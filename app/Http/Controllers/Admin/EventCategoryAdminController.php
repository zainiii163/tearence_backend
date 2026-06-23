<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventCategoryAdminController extends Controller
{
    /**
     * Display a listing of event categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = [
                'concert' => 'Concerts & Music',
                'workshop' => 'Workshops',
                'party' => 'Parties & Nightlife',
                'festival' => 'Festivals',
                'conference' => 'Business Conferences',
                'sports' => 'Sports Events',
                'cultural' => 'Cultural Events',
                'food_drink' => 'Food & Drink',
                'charity' => 'Charity Events',
                'other' => 'Other',
            ];

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new category (placeholder for future expansion)
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Category management is currently fixed. Custom categories not supported yet.'
        ], 400);
    }

    /**
     * Display the specified category
     */
    public function show($id): JsonResponse
    {
        try {
            $categories = [
                'concert' => 'Concerts & Music',
                'workshop' => 'Workshops',
                'party' => 'Parties & Nightlife',
                'festival' => 'Festivals',
                'conference' => 'Business Conferences',
                'sports' => 'Sports Events',
                'cultural' => 'Cultural Events',
                'food_drink' => 'Food & Drink',
                'charity' => 'Charity Events',
                'other' => 'Other',
            ];

            if (!isset($categories[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $id,
                    'name' => $categories[$id]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category (placeholder)
     */
    public function update(Request $request, $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Category management is currently fixed. Custom categories not supported yet.'
        ], 400);
    }

    /**
     * Remove the specified category (placeholder)
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Category management is currently fixed. Custom categories not supported yet.'
        ], 400);
    }
}
