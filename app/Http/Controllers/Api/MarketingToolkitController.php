<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingToolkit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingToolkitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MarketingToolkit::active()->ordered();

        if ($request->has('category')) {
            $query->forCategory($request->category);
        }

        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        $toolkits = $query->get()->groupBy('category_slug');

        return response()->json(['toolkits' => $toolkits]);
    }

    public function byCategory(string $category): JsonResponse
    {
        $toolkits = MarketingToolkit::active()
            ->forCategory($category)
            ->ordered()
            ->get()
            ->groupBy('tool_type');

        return response()->json([
            'category' => $category,
            'toolkits' => $toolkits,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $toolkit = MarketingToolkit::active()->findOrFail($id);

        return response()->json(['toolkit' => $toolkit]);
    }

    public function categories(): JsonResponse
    {
        $categories = MarketingToolkit::active()
            ->select('category_slug')
            ->distinct()
            ->pluck('category_slug');

        return response()->json(['categories' => $categories]);
    }
}
