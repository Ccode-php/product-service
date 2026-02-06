<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        return ProductVariant::create($data);
    }

    public function show(ProductVariant $variant)
    {
        return $variant->load('product');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'variant' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $variant->update($data);

        return $variant->fresh();
    }



    public function destroy(ProductVariant $productVariant)
    {
        $productVariant->delete();
        return response()->noContent();
    }

    public function decreaseStock(Request $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $data = $request->validate(['quantity' => 'required|integer|min:1']);

        if ($variant->stock < $data['quantity']) {
            return response()->json(['error' => 'Not enough stock'], 400);
        }

        $variant->decrement('stock', $data['quantity']);

        return ['success' => true];
    }
}
