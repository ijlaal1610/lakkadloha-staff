<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['product', 'staff']);

        // Staff can only see their own sales
        if (Auth::user()->isStaff()) {
            $query->where('staff_id', Auth::id());
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"));
            });
        }

        if ($request->status) $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('sold_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('sold_at', '<=', $request->date_to);
        if ($request->staff_id && Auth::user()->isAdminOrManager()) $query->where('staff_id', $request->staff_id);

        $sales = $query->latest('sold_at')->paginate(15)->withQueryString();

        $stats = [
            'today' => Sale::completed()->today()->sum('total_amount'),
            'today_count' => Sale::completed()->today()->count(),
            'this_month' => Sale::completed()->thisMonth()->sum('total_amount'),
            'this_month_count' => Sale::completed()->thisMonth()->count(),
        ];

        $staffList = Auth::user()->isAdminOrManager()
            ? \App\Models\User::whereIn('role', ['staff', 'manager'])->where('is_active', true)->get()
            : collect();

        return view('sales.index', compact('sales', 'stats', 'staffList'));
    }

    public function create()
    {
        $products = Product::active()->where('current_stock', '>', 0)->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->current_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock. Available: {$product->current_stock}"])->withInput();
        }

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'sale_number' => Sale::generateSaleNumber(),
                'product_id' => $validated['product_id'],
                'staff_id' => Auth::id(),
                'quantity' => $validated['quantity'],
                'selling_price' => $validated['selling_price'],
                'total_amount' => $validated['quantity'] * $validated['selling_price'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'notes' => $validated['notes'],
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $product->deductStock($validated['quantity'], Auth::id(), 'sale', "Sale #{$sale->sale_number}");

            AuditLog::record('created', 'sales', "Created sale #{$sale->sale_number} for {$product->name}", $sale);

            // Low stock check after sale
            if ($product->fresh()->isLowStock()) {
                $this->notifyLowStock($product);
            }

            DB::commit();

            if ($request->print) {
                return redirect()->route('sales.receipt', $sale)->with('success', 'Sale created successfully.');
            }

            return redirect()->route('sales.index')->with('success', "Sale #{$sale->sale_number} created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create sale. ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $this->authorizeSaleAccess($sale);
        $sale->load(['product', 'staff', 'refunds.processor']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->authorize('update', $sale);
        if ($sale->status !== 'completed') {
            return back()->with('error', 'Cannot edit a cancelled or refunded sale.');
        }
        $products = Product::active()->orderBy('name')->get();
        return view('sales.edit', compact('sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Cannot edit this sale.');
        }

        $validated = $request->validate([
            'selling_price' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $old = $sale->toArray();
        $sale->update(array_merge($validated, [
            'total_amount' => $sale->quantity * $validated['selling_price'],
        ]));

        AuditLog::record('updated', 'sales', "Updated sale #{$sale->sale_number}", $sale, $old, $sale->fresh()->toArray());

        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function cancel(Request $request, Sale $sale)
    {
        $this->authorize('manage', $sale);

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Sale cannot be cancelled.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        DB::beginTransaction();
        try {
            $sale->update(['status' => 'cancelled', 'notes' => ($sale->notes ? $sale->notes . ' | ' : '') . 'Cancelled: ' . $request->reason]);
            $sale->product->addStock($sale->quantity, Auth::id(), "Sale #{$sale->sale_number} cancelled - stock restored");
            AuditLog::record('cancelled', 'sales', "Cancelled sale #{$sale->sale_number}", $sale);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel sale.');
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale cancelled and stock restored.');
    }

    public function refund(Request $request, Sale $sale)
    {
        $this->authorize('manage', $sale);

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Only completed sales can be refunded.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $sale->quantity,
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $refundAmount = $validated['quantity'] * $sale->selling_price;

            SaleRefund::create([
                'sale_id' => $sale->id,
                'processed_by' => Auth::id(),
                'quantity' => $validated['quantity'],
                'refund_amount' => $refundAmount,
                'reason' => $validated['reason'],
                'refunded_at' => now(),
            ]);

            $sale->update(['status' => 'refunded']);
            $sale->product->addStock($validated['quantity'], Auth::id(), "Refund for sale #{$sale->sale_number}");
            AuditLog::record('refunded', 'sales', "Refunded sale #{$sale->sale_number}", $sale);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process refund.');
        }

        return redirect()->route('sales.show', $sale)->with('success', "Refund of ₹{$refundAmount} processed.");
    }

    public function receipt(Sale $sale)
    {
        $this->authorizeSaleAccess($sale);
        $sale->load(['product', 'staff']);
        return view('sales.receipt', compact('sale'));
    }

    private function authorizeSaleAccess(Sale $sale): void
    {
        if (Auth::user()->isStaff() && $sale->staff_id !== Auth::id()) {
            abort(403);
        }
    }

    private function notifyLowStock(Product $product): void
    {
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\LowStockNotification($product));
        }
    }
}
