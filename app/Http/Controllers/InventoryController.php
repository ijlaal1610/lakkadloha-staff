<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('creator')->withCount('sales');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->stock_status === 'low') {
            $query->lowStock();
        } elseif ($request->stock_status === 'out') {
            $query->where('current_stock', 0);
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Product::active()->count(),
            'low_stock' => Product::active()->lowStock()->count(),
            'out_of_stock' => Product::active()->where('current_stock', 0)->count(),
            'total_stock_value' => Product::active()->count(),
        ];

        return view('inventory.index', compact('products', 'stats'));
    }

    public function create()
    {
        $this->authorize('manage', Product::class);
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'current_stock' => $validated['current_stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'notes' => $validated['notes'],
            'photo' => $photoPath,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        // Record initial stock movement if stock > 0
        if ($product->current_stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'add',
                'quantity' => $product->current_stock,
                'stock_before' => 0,
                'stock_after' => $product->current_stock,
                'notes' => 'Initial stock on product creation',
            ]);
        }

        AuditLog::record('created', 'inventory', "Created product: {$product->name}", $product, null, $product->toArray());

        // Check low stock notification
        if ($product->isLowStock()) {
            $this->notifyLowStock($product);
        }

        return redirect()->route('inventory.index')
            ->with('success', "Product '{$product->name}' created successfully.");
    }

    public function show(Product $product)
    {
        $product->load('creator', 'stockMovements.user', 'sales.staff');
        $stockMovements = $product->stockMovements()->with('user')->latest()->paginate(20);
        $salesHistory = $product->sales()->with('staff')->latest()->paginate(10);
        return view('inventory.show', compact('product', 'stockMovements', 'salesHistory'));
    }

    public function edit(Product $product)
    {
        $this->authorize('manage', Product::class);
        return view('inventory.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('manage', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'low_stock_threshold' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $oldValues = $product->toArray();

        if ($request->hasFile('photo')) {
            if ($product->photo) Storage::disk('public')->delete($product->photo);
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($validated);
        AuditLog::record('updated', 'inventory', "Updated product: {$product->name}", $product, $oldValues, $product->fresh()->toArray());

        return redirect()->route('inventory.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('admin', Product::class);
        AuditLog::record('deleted', 'inventory', "Deleted product: {$product->name}", $product);
        $product->delete();
        return redirect()->route('inventory.index')->with('success', 'Product deleted.');
    }

    public function addStock(Request $request, Product $product)
    {
        $this->authorize('manage', Product::class);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $before = $product->current_stock;
        $movement = $product->addStock($validated['quantity'], Auth::id(), $validated['notes']);

        AuditLog::record('stock_added', 'inventory',
            "Added {$validated['quantity']} units to {$product->name}. Stock: {$before} → {$product->current_stock}",
            $product);

        return redirect()->back()->with('success', "Added {$validated['quantity']} units to stock.");
    }

    public function adjustStock(Request $request, Product $product)
    {
        $this->authorize('manage', Product::class);

        $validated = $request->validate([
            'new_stock' => 'required|integer|min:0',
            'notes' => 'required|string|max:500',
        ]);

        $before = $product->current_stock;
        $diff = $validated['new_stock'] - $before;

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => 'adjustment',
            'quantity' => abs($diff),
            'stock_before' => $before,
            'stock_after' => $validated['new_stock'],
            'notes' => $validated['notes'],
        ]);

        $product->update(['current_stock' => $validated['new_stock']]);

        AuditLog::record('stock_adjusted', 'inventory',
            "Adjusted stock for {$product->name}: {$before} → {$validated['new_stock']}",
            $product);

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }

    private function notifyLowStock(Product $product): void
    {
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'manager'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\LowStockNotification($product));
        }
    }
}
