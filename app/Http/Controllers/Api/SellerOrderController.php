<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sellerId = $request->user()->id;

        $orders = Order::whereHas('items', fn($q) => $q->where('seller_id', $sellerId))
            ->with([
                'user:id,name,email',
                'items' => fn($q) => $q->where('seller_id', $sellerId)
                    ->with('product:id,name,color1,color2,icon_class,icon_color'),
            ])
            ->latest()
            ->paginate(50);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        // Ensure seller has at least one item in this order
        abort_unless(
            $order->items()->where('seller_id', $request->user()->id)->exists(),
            403
        );

        $data = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json(['order' => $order]);
    }
}
