<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        // Filter by subcategory
        if ($request->filled('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereJsonContains('tags', strtolower($term));
            });
        }

        // Filter by badge
        if ($request->filled('badge')) {
            $query->where('badge', strtoupper($request->badge));
        }

        // Filter by in_stock
        if ($request->boolean('in_stock')) {
            $query->where('in_stock', true);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $dir  = $request->get('dir', 'asc');
        $allowedSorts = ['price', 'name', 'rating', 'reviews', 'id'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json(['product' => $product]);
    }
}
