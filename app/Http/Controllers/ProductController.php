<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $r)
    {
        $query = Product::with('variants', 'category');

        if ($r->filled('search')) {
            $query->where('name', 'like', '%' . $r->search . '%');
        }

        if ($r->filled('category_id')) {
            $query->where('category_id', $r->category_id);
        }

        if ($r->filled('min_price')) {
            $query->whereHas('variants', fn($q) => $q->where('price', '>=', $r->min_price));
        }

        if ($r->filled('max_price')) {
            $query->whereHas('variants', fn($q) => $q->where('price', '<=', $r->max_price));
        }

        return $query->get();
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
            $data['image'] = Storage::disk('s3')->url($path); // ✅ URL saqlaymiz
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

            // eski rasmni o‘chirish (agar URL bo‘lsa, S3 path bo‘lishi kerak)
            if ($product->image) {
                $oldPath = str_replace(Storage::disk('s3')->url('/'), '', $product->image);
                if (Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }
        
            $path = $request->file('image')->store('products', 's3');
            $data['image'] = Storage::disk('s3')->url($path);
        }
        

        $product->update($data);

        return $product->load('category', 'variants');
    }



    public function destroy(Product $product)
    {
        // rasmni o‘chiramiz
        if ($product->image) {
            $oldPath = str_replace(Storage::disk('s3')->url('/'), '', $product->image);
            if (Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
