<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvertReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdvertReportController extends Controller
{
    /**
     * POST /api/v1/reports/submit
     * Generic report endpoint for any advert type on Worldwide Adverts.
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'nullable|string|max:120',
            'advert_slug' => 'nullable|string|max:255',
            'advert_type' => 'nullable|string|max:80',
            'advert_title' => 'nullable|string|max:255',
            'advert_code' => 'nullable|string|max:120',
            'reason' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'reporter_email' => 'nullable|email|max:190',
            'severity' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$request->filled('advert_id') && !$request->filled('advert_slug')) {
            return response()->json([
                'success' => false,
                'message' => 'advert_id or advert_slug is required',
            ], 422);
        }

        $userId = Auth::id();

        $report = AdvertReport::create([
            'advert_id' => $request->input('advert_id'),
            'advert_slug' => $request->input('advert_slug'),
            'advert_type' => $request->input('advert_type'),
            'advert_title' => $request->input('advert_title'),
            'advert_code' => $request->input('advert_code'),
            'reporter_id' => $userId,
            'reporter_email' => $request->input('reporter_email') ?: (Auth::user()->email ?? null),
            'reason' => $request->input('reason'),
            'severity' => $request->input('severity'),
            'description' => $request->input('description'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully. We will review it shortly.',
            'data' => [
                'report_id' => $report->id,
            ],
        ], 201);
    }
}
