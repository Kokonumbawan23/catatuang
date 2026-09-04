<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::cached();

        $type = TransactionType::tryFrom((string) request('type'));
        if ($type !== null) {
            $categories = $categories->where('type', $type)->values();
        }

        return response()->json([
            'data' => $categories,
        ]);
    }
}
