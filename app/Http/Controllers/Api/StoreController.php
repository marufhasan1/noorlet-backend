<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stores = Store::where('status', 'approved')
            ->with('user:id,name')
            ->withCount('products')
            ->when($request->filled('search'), fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
            )
            ->latest()
            ->paginate(20);

        return response()->json($stores);
    }

    public function show(string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->withCount('products')
            ->firstOrFail();

        return response()->json(['store' => $store]);
    }

    public function products(Request $request, string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('status', 'approved')->firstOrFail();

        $query = $store->products()->with(['category', 'media']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        }
        if ($request->boolean('in_stock')) {
            $query->where('in_stock', true);
        }
        if ($request->filled('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->filled('max_price')) $query->where('price', '<=', $request->max_price);

        $sort = in_array($request->input('sort'), ['price', 'name', 'rating', 'id']) ? $request->input('sort') : 'id';
        $query->orderBy($sort, $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc');

        $products = $query->paginate(20);
        $products->getCollection()->transform(function ($p) {
            $arr = $p->toArray();
            $arr['image'] = $p->getFirstMediaUrl('images', 'medium') ?: null;
            $arr['thumb'] = $p->getFirstMediaUrl('images', 'thumb') ?: null;
            return $arr;
        });

        return response()->json($products);
    }
}
