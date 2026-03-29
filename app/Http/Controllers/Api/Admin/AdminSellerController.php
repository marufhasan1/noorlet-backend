<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSellerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'seller')->with('store');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('store', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $sellers = $query->latest()->paginate(20);

        return response()->json($sellers);
    }

    public function show(User $user): JsonResponse
    {
        abort_if(!$user->isSeller(), 404);
        $user->load('store', 'products');
        return response()->json(['seller' => $user]);
    }

    public function updateStoreStatus(Request $request, Store $store): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,suspended'],
        ]);

        $store->update($data);

        return response()->json(['store' => $store->fresh()]);
    }

    public function revoke(User $user): JsonResponse
    {
        abort_if(!$user->isSeller(), 404);
        $user->update(['role' => 'customer']);

        return response()->json(['message' => 'Seller access revoked.', 'user' => $user->fresh()]);
    }
}
