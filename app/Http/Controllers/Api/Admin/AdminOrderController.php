<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('user')->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('search')) {
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $orders = $query->paginate(20);

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('user', 'items.product');
        return response()->json(['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:processing,confirmed,shipped,delivered,cancelled'],
        ]);

        $order->update($data);

        return response()->json(['order' => $order->fresh()]);
    }
}
