<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('variants', 'category')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 's3');
            $data['image'] = Storage::disk('s3')->url($path);
        }

        $product = Product::create($data);

        return $product->load('category', 'variants');
    }

    public function show(Product $product)
    {
        return $product->load('category', 'variants');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {

            // eski rasmni o‘chiramiz
            if ($product->image) {
                $oldPath = str_replace(env('AWS_URL') . '/', '', $product->image);
                Storage::disk('s3')->delete($oldPath);
            }

            $path = $request->file('image')->store('products', 's3');
            $data['image'] = Storage::disk('s3')->url($path);
        }

        $product->update($data);

        return $product->load('category', 'variants');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            $oldPath = str_replace(env('AWS_URL') . '/', '', $product->image);
            Storage::disk('s3')->delete($oldPath);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
