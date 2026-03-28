<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SellerProductController extends Controller
{
    private function productWithImages(Product $product): array
    {
        $data = $product->toArray();
        $data['images'] = $product->getMedia('images')->map(fn($m) => [
            'id'     => $m->id,
            'url'    => $m->getUrl(),
            'thumb'  => $m->getUrl('thumb'),
            'medium' => $m->getUrl('medium'),
        ])->values()->toArray();
        $data['image']  = $product->getFirstMediaUrl('images', 'medium') ?: null;
        $data['thumb']  = $product->getFirstMediaUrl('images', 'thumb') ?: null;
        return $data;
    }

    public function index(Request $request): JsonResponse
    {
        $products = Product::where('seller_id', $request->user()->id)
            ->with(['category', 'media'])
            ->latest()
            ->paginate(20);

        $products->getCollection()->transform(fn($p) => $this->productWithImages($p));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category_id'    => ['required', 'exists:categories,id'],
            'subcategory'    => ['nullable', 'string', 'max:100'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
            'badge'          => ['nullable', 'string', 'max:50'],
            'details'        => ['nullable', 'array'],
            'care'           => ['nullable', 'string'],
            'fit'            => ['nullable', 'string'],
            'colors'         => ['nullable', 'array'],
            'sizes'          => ['nullable', 'array'],
            'tags'           => ['nullable', 'array'],
            'color1'         => ['nullable', 'string'],
            'color2'         => ['nullable', 'string'],
            'icon_class'     => ['nullable', 'string'],
            'icon_color'     => ['nullable', 'string'],
            'in_stock'       => ['boolean'],
            'images'         => ['nullable', 'array', 'max:8'],
            'images.*'       => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $slug = Str::slug($data['name']);
        $original = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        $product = Product::create(array_merge(
            collect($data)->except('images')->toArray(),
            [
                'seller_id' => $request->user()->id,
                'slug'      => $slug,
                'sku'       => 'SKU-' . strtoupper(Str::random(8)),
                'rating'    => 0,
                'reviews'   => 0,
            ]
        ));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $product->addMedia($file)->toMediaCollection('images');
            }
        }

        $product->load('category');

        return response()->json(['product' => $this->productWithImages($product)], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        abort_if($product->seller_id !== $request->user()->id, 403);
        $product->load(['category', 'media']);
        return response()->json(['product' => $this->productWithImages($product)]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        abort_if($product->seller_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'category_id'    => ['sometimes', 'required', 'exists:categories,id'],
            'subcategory'    => ['nullable', 'string', 'max:100'],
            'price'          => ['sometimes', 'required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'description'    => ['nullable', 'string'],
            'badge'          => ['nullable', 'string', 'max:50'],
            'details'        => ['nullable', 'array'],
            'care'           => ['nullable', 'string'],
            'fit'            => ['nullable', 'string'],
            'colors'         => ['nullable', 'array'],
            'sizes'          => ['nullable', 'array'],
            'tags'           => ['nullable', 'array'],
            'color1'         => ['nullable', 'string'],
            'color2'         => ['nullable', 'string'],
            'icon_class'     => ['nullable', 'string'],
            'icon_color'     => ['nullable', 'string'],
            'in_stock'       => ['boolean'],
        ]);

        $product->update($data);
        $product->load(['category', 'media']);

        return response()->json(['product' => $this->productWithImages($product)]);
    }

    public function storeImages(Request $request, Product $product): JsonResponse
    {
        abort_if($product->seller_id !== $request->user()->id, 403);

        $request->validate([
            'images'   => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        foreach ($request->file('images') as $file) {
            $product->addMedia($file)->toMediaCollection('images');
        }

        $product->load('media');

        return response()->json([
            'images' => $product->getMedia('images')->map(fn($m) => [
                'id'     => $m->id,
                'url'    => $m->getUrl(),
                'thumb'  => $m->getUrl('thumb'),
                'medium' => $m->getUrl('medium'),
            ])->values(),
        ]);
    }

    public function destroyImage(Request $request, Product $product, Media $media): JsonResponse
    {
        abort_if($product->seller_id !== $request->user()->id, 403);
        abort_if((int) $media->model_id !== $product->id, 403);
        $media->delete();
        return response()->json(['message' => 'Image deleted.']);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        abort_if($product->seller_id !== $request->user()->id, 403);
        $product->delete();
        return response()->json(['message' => 'Product deleted.']);
    }
}
