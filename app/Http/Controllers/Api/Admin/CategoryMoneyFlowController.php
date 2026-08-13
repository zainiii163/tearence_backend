<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryMoneyFlow;
use App\Services\CategoryMoneyFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CategoryMoneyFlowController extends Controller
{
    public function summary(Request $request, CategoryMoneyFlowService $service): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');

        return response()->json([
            'success' => true,
            'message' => 'Category money flow summary',
            'data' => $service->summarize($from, $to),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('category_money_flows')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $q = CategoryMoneyFlow::query()->orderByDesc('occurred_at');

        if ($request->filled('category_key')) {
            $q->where('category_key', $request->query('category_key'));
        }
        if ($request->filled('bucket')) {
            $q->where('bucket', $request->query('bucket'));
        }

        $perPage = min(100, max(10, (int) $request->query('per_page', 50)));

        return response()->json([
            'success' => true,
            'data' => $q->paginate($perPage),
        ]);
    }
}
