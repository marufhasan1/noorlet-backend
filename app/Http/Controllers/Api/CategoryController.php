<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products')->orderBy('name');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return response()->json(['categories' => $query->get()]);
    }
}
