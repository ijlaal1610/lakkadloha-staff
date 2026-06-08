<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['product', 'staff']);

        if ($request->user()->isStaff()) {
            $query->where('staff_id', $request->user()->id);
        }

        if ($request->date_from) $query->whereDate('sold_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('sold_at', '<=', $request->date_to);
        if ($request->status) $query->where('status', $request->status);

        $sales = $query->latest('sold_at')->paginate($request->per_page ?? 20);
        return response()->json($sales);
    }

    public function show(Sale $sale)
    {
        if ($request->user()->isStaff() && $sale->staff_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $sale->load(['product', 'staff', 'refunds.processor']);
        return response()->json(['sale' => $sale]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->current_stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock.',
                'available_stock' => $product->current_stock,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'sale_number' => Sale::generateSaleNumber(),
                'product_id' => $validated['product_id'],
                'staff_id' => $request->user()->id,
                'quantity' => $validated['quantity'],
                'selling_price' => $validated['selling_price'],
                'total_amount' => $validated['quantity'] * $validated['selling_price'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'notes' => $validated['notes'],
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $product->deductStock($validated['quantity'], $request->user()->id, 'sale', "Sale #{$sale->sale_number}");
            AuditLog::record('created', 'sales', "API: Created sale #{$sale->sale_number}", $sale);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create sale.'], 500);
        }

        return response()->json(['sale' => $sale->load(['product', 'staff'])], 201);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdminOrManager();

        $salesQuery = Sale::completed();
        if (!$isAdmin) $salesQuery->where('staff_id', $user->id);

        return response()->json([
            'today' => [
                'revenue' => (clone $salesQuery)->today()->sum('total_amount'),
                'count' => (clone $salesQuery)->today()->count(),
            ],
            'this_month' => [
                'revenue' => (clone $salesQuery)->thisMonth()->sum('total_amount'),
                'count' => (clone $salesQuery)->thisMonth()->count(),
            ],
            'low_stock_count' => $isAdmin ? Product::active()->lowStock()->count() : 0,
            'today_attendance' => $isAdmin ? null : \App\Models\Attendance::where('user_id', $user->id)->today()->first(),
        ]);
    }
}
