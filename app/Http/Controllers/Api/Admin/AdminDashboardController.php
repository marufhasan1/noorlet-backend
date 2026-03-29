<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalUsers    = User::where('role', 'customer')->orWhereNull('role')->count();
        $totalSellers  = User::where('role', 'seller')->count();
        $totalProducts = Product::count();
        $totalOrders   = Order::count();
        $totalRevenue  = Order::sum('total');

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'status', 'total', 'created_at', 'user_id']);

        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $revenueByMonth = Order::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'stats' => [
                'total_users'    => $totalUsers,
                'total_sellers'  => $totalSellers,
                'total_products' => $totalProducts,
                'total_orders'   => $totalOrders,
                'total_revenue'  => round($totalRevenue, 2),
            ],
            'recent_orders'    => $recentOrders,
            'orders_by_status' => $ordersByStatus,
            'revenue_by_month' => $revenueByMonth,
        ]);
    }
}
