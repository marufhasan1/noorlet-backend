<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = OrderItem::where('seller_id', $request->user()->id)
            ->with(['order', 'product:id,name,color1,color2,icon_class,icon_color'])
            ->latest()
            ->paginate(20);

        return response()->json($items);
    }

    public function updateStatus(Request $request, OrderItem $orderItem): JsonResponse
    {
        abort_if($orderItem->seller_id !== $request->user()->id, 403);

        $data = $request->validate([
            'seller_status' => ['required', 'in:pending,processing,shipped,delivered'],
        ]);

        $orderItem->update(['seller_status' => $data['seller_status']]);

        return response()->json(['order_item' => $orderItem]);
    }
}
