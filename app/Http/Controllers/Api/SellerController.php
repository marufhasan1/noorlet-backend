<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'description' => ['nullable', 'string', 'max:1000'],
            'email'       => ['nullable', 'email'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'country'     => ['nullable', 'string', 'max:100'],
        ]);

        $store->update($data);

        return response()->json(['store' => $store->fresh()]);
    }
}
