<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->isSeller()) {
            return response()->json(['message' => 'Seller access required.'], 403);
        }
        return $next($request);
    }
}
