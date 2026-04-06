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
        $query = Product::with(['category', 'media']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereJsonContains('tags', strtolower($term));
            });
        }
        if ($request->filled('badge')) {
            $query->where('badge', strtoupper($request->badge));
        }
        if ($request->boolean('in_stock')) {
            $query->where('in_stock', true);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sort = $request->input('sort', 'id');
        $dir  = $request->input('dir', 'asc');
        if (in_array($sort, ['price', 'name', 'rating', 'reviews', 'id'])) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->paginate($request->integer('per_page', 20));

        $products->getCollection()->transform(function ($p) {
            $arr = $p->toArray();
            $arr['image'] = $p->getFirstMediaUrl('images', 'medium') ?: null;
            $arr['thumb'] = $p->getFirstMediaUrl('images', 'thumb') ?: null;
            return $arr;
        });

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        // Accept both plain ID and SEO slug format "name_id"
        $id = (int) (str_contains($slug, '_') ? substr(strrchr($slug, '_'), 1) : $slug);
        $product = Product::with(['category', 'media'])->findOrFail($id);

        $data = $product->toArray();
        $data['images'] = $product->getMedia('images')->map(fn($m) => [
            'id'     => $m->id,
            'url'    => $m->getUrl(),
            'thumb'  => $m->getUrl('thumb'),
            'medium' => $m->getUrl('medium'),
        ])->values()->toArray();
        $data['image']  = $product->getFirstMediaUrl('images', 'medium') ?: null;
        $data['thumb']  = $product->getFirstMediaUrl('images', 'thumb') ?: null;

        return response()->json(['product' => $data]);
    }
}
