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
            'image' => 'nullable|image|max:2048', // rasmni qabul qilamiz
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = config('app.url') . '/storage/' . $path;
        }

        return Product::create($data);
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
            // eski rasmni o'chirish
            if ($product->image) {
                $oldPath = str_replace(config('app.url') . '/storage/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('products', 'public');
            $data['image'] = config('app.url') . '/storage/' . $path;
        }

        $product->update($data);

        return $product->load('category', 'variants');
    }


    public function destroy(Product $product)
    {
        // agar image bo'lsa, diskdan o'chirish
        if ($product->image) {
            $oldPath = str_replace(config('app.url') . '/storage/', '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
