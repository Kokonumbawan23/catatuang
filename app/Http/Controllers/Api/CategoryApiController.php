<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Category::query();

        if (request()->has('type') && in_array(request('type'), ['expense', 'income'])) {
            $query->where('type', request('type'));
        }

        $categories = $query->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
