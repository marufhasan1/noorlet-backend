<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = $request->user()
            ->wishlists()
            ->with('product.category', 'product.media')
            ->get()
            ->pluck('product')
            ->filter()
            ->map(function ($product) {
                $data = $product->toArray();
                $data['image'] = $product->getFirstMediaUrl('images', 'medium') ?: null;
                $data['thumb'] = $product->getFirstMediaUrl('images', 'thumb') ?: null;
                return $data;
            })
            ->values();

        return response()->json(['products' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => ['required', 'exists:products,id']]);

        $item = $request->user()->wishlists()->firstOrCreate([
            'product_id' => $request->product_id,
        ]);

        return response()->json(['wishlist_item' => $item], 201);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $request->user()
            ->wishlists()
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }

    public function ids(Request $request): JsonResponse
    {
        $ids = $request->user()->wishlists()->pluck('product_id');
        return response()->json(['ids' => $ids]);
    }
}
