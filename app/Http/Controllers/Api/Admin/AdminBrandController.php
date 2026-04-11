<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBrandController extends Controller
{
    private function withLogoUrl(Brand $brand): array
    {
        $data = $brand->toArray();
        $data['logo_url'] = $brand->logo ? Storage::disk('public')->url($brand->logo) : null;
        return $data;
    }

    private function allWithLogoUrl(): array
    {
        return Brand::orderBy('sort_order')->get()
            ->map(fn($b) => $this->withLogoUrl($b))
            ->values()
            ->toArray();
    }

    public function index(): JsonResponse
    {
        return response()->json(['brands' => $this->allWithLogoUrl()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $brand = Brand::create([
            'name'        => $data['name'],
            'website_url' => $data['website_url'] ?? null,
            'sort_order'  => $data['sort_order'] ?? Brand::max('sort_order') + 1,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        return response()->json(['brand' => $this->withLogoUrl($brand)], 201);
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:100'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $brand->update($data);

        return response()->json(['brand' => $this->withLogoUrl($brand->fresh())]);
    }

    public function uploadLogo(Request $request, Brand $brand): JsonResponse
    {
        $request->validate(['logo' => ['required', 'image', 'max:2048']]);

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $path = $request->file('logo')->store("brands/{$brand->id}", 'public');
        $brand->update(['logo' => $path]);

        return response()->json(['brand' => $this->withLogoUrl($brand->fresh())]);
    }

    public function deleteLogo(Brand $brand): JsonResponse
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
            $brand->update(['logo' => null]);
        }

        return response()->json(['brand' => $this->withLogoUrl($brand->fresh())]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();
        return response()->json(['message' => 'Brand deleted']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($data['order'] as $position => $id) {
            Brand::where('id', $id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['brands' => $this->allWithLogoUrl()]);
    }
}
