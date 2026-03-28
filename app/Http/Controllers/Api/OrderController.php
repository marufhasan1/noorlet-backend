<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        $order->load('items.product');
        return response()->json(['order' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.size'     => ['nullable', 'string'],
            'items.*.color'    => ['nullable', 'string'],
            'shipping_address' => ['required', 'string'],
        ]);

        $subtotal = 0;
        $orderItems = [];

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;
            $orderItems[] = [
                'product_id'   => $product->id,
                'seller_id'    => $product->seller_id,
                'product_name' => $product->name,
                'size'         => $item['size'] ?? null,
                'color'        => $item['color'] ?? null,
                'quantity'     => $item['quantity'],
                'unit_price'   => $product->price,
            ];
        }

        $shipping = $subtotal >= 150 ? 0 : 9.99;
        $total = $subtotal + $shipping;

        $order = $request->user()->orders()->create([
            'order_number'     => 'LX-' . strtoupper(Str::random(6)),
            'status'           => 'processing',
            'subtotal'         => $subtotal,
            'shipping'         => $shipping,
            'total'            => $total,
            'shipping_address' => $data['shipping_address'],
        ]);

        $order->items()->createMany($orderItems);
        $order->load('items.product');

        return response()->json(['order' => $order], 201);
    }
}
