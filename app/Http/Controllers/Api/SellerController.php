<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    // Register existing user as seller OR register new seller account
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSeller()) {
            return response()->json(['message' => 'Already registered as a seller.'], 422);
        }

        $data = $request->validate([
            'store_name'  => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'email'       => ['nullable', 'email'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
        ]);

        $slug = Str::slug($data['store_name']);
        $originalSlug = $slug;
        $i = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $store = Store::create([
            'user_id'     => $user->id,
            'name'        => $data['store_name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'address'     => $data['address'] ?? null,
            'city'        => $data['city'] ?? null,
            'country'     => $data['country'] ?? null,
            'status'      => 'approved', // auto-approve for now
        ]);

        $user->update(['role' => 'seller']);

        return response()->json(['store' => $store, 'user' => $user->fresh()], 201);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = $user->store;

        $totalProducts = $user->products()->count();
        $totalOrders = \App\Models\OrderItem::where('seller_id', $user->id)->distinct('order_id')->count('order_id');
        $totalRevenue = \App\Models\OrderItem::where('seller_id', $user->id)->sum(\Illuminate\Support\Facades\DB::raw('unit_price * quantity'));
        $pendingOrders = \App\Models\OrderItem::where('seller_id', $user->id)->where('seller_status', 'pending')->count();

        return response()->json([
            'store'          => $store,
            'stats' => [
                'total_products' => $totalProducts,
                'total_orders'   => $totalOrders,
                'total_revenue'  => round($totalRevenue, 2),
                'pending_orders' => $pendingOrders,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['store' => $request->user()->store]);
    }

    public function updateStore(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'slug'        => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'email'       => ['nullable', 'email'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
        ]);

        if (isset($data['slug'])) {
            $slug = Str::slug($data['slug']);
            if (Store::where('slug', $slug)->where('id', '!=', $store->id)->exists()) {
                return response()->json(['message' => 'This store ID is already taken.'], 422);
            }
            $data['slug'] = $slug;
        }

        $store->update($data);

        return response()->json(['store' => $store->fresh()]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate(['logo' => ['required', 'image', 'max:2048']]);
        $store = $request->user()->store;

        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }

        $path = $request->file('logo')->store("stores/{$store->id}", 'public');
        $store->update(['logo' => $path]);

        return response()->json(['store' => $store->fresh(), 'url' => Storage::disk('public')->url($path)]);
    }

    public function uploadBanner(Request $request): JsonResponse
    {
        $request->validate(['banner' => ['required', 'image', 'max:5120']]);
        $store = $request->user()->store;

        if ($store->banner) {
            Storage::disk('public')->delete($store->banner);
        }

        $path = $request->file('banner')->store("stores/{$store->id}", 'public');
        $store->update(['banner' => $path]);

        return response()->json(['store' => $store->fresh(), 'url' => Storage::disk('public')->url($path)]);
    }

    public function deleteImage(Request $request, string $type): JsonResponse
    {
        $store = $request->user()->store;

        if (!in_array($type, ['logo', 'banner'])) {
            return response()->json(['message' => 'Invalid image type.'], 422);
        }

        if ($store->{$type}) {
            Storage::disk('public')->delete($store->{$type});
            $store->update([$type => null]);
        }

        return response()->json(['store' => $store->fresh()]);
    }
}
