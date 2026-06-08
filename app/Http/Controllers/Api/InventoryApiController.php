<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Attendance;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active();
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        if ($request->low_stock) $query->lowStock();
        $products = $query->latest()->paginate($request->per_page ?? 20);
        return response()->json($products);
    }

    public function show(Product $product)
    {
        return response()->json([
            'product' => $product,
            'stock_movements' => $product->stockMovements()->with('user')->latest()->take(20)->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdminOrManager()) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::create(array_merge($validated, ['created_by' => $request->user()->id]));
        AuditLog::record('created', 'inventory', "API: Created product {$product->name}", $product);
        return response()->json(['product' => $product], 201);
    }

    public function update(Request $request, Product $product)
    {
        if (!$request->user()->isAdminOrManager()) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'low_stock_threshold' => 'sometimes|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product->update($validated);
        return response()->json(['product' => $product->fresh()]);
    }

    public function addStock(Request $request, Product $product)
    {
        if (!$request->user()->isAdminOrManager()) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product->addStock($validated['quantity'], $request->user()->id, $validated['notes'] ?? null);
        return response()->json(['product' => $product->fresh(), 'message' => 'Stock added.']);
    }
}
