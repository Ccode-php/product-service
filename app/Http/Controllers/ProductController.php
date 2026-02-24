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
            $path = $request->file('image')->store('', 's3');
            $data['image'] = $path; // faqat filename
        }

        $product = Product::create($data);

        return $product->load('category', 'variants')
               ->append('image');

    }

    public function show(Product $product)
    {
        return $product->load('category', 'variants')
               ->append('image');

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
                Storage::disk('s3')->delete($product->image);
            }
            
            $path = $request->file('image')->store('', 's3');
            $data['image'] = $path;
        }

        $product->update($data);

        return $product->load('category', 'variants')
               ->append('image');

    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('s3')->delete($product->image);
        }
        

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
