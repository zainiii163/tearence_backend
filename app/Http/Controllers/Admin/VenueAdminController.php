<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VenueAdminController extends Controller
{
    /**
     * Display venues dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_venues' => Venue::count(),
                'active_venues' => Venue::where('is_active', true)->count(),
                'pending_venues' => Venue::where('is_active', false)->count(),
                'featured_venues' => Venue::where('is_featured', true)->count(),
                'venues_this_month' => Venue::whereMonth('created_at', now()->month)->count(),
                'venues_today' => Venue::whereDate('created_at', today())->count(),
            ];

            $recentVenues = Venue::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_venues' => $recentVenues,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of venues
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Venue::with(['user']);

            // Filters
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->has('venue_type')) {
                $query->where('venue_type', $request->venue_type);
            }

            if ($request->has('city')) {
                $query->where('city', $request->city);
            }

            if ($request->has('country')) {
                $query->where('country', $request->country);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $venues = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $venues
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch venues: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created venue
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'venue_type' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'capacity' => 'nullable|integer|min:1',
                'contact_email' => 'required|email',
                'contact_phone' => 'nullable|string|max:20',
                'website' => 'nullable|url',
                'social_links' => 'nullable|array',
                'social_links.*' => 'url',
                'images' => 'nullable|array',
                'images.*' => 'string',
                'facilities' => 'nullable|array',
                'facilities.*' => 'string',
                'opening_hours' => 'nullable|array',
                'pricing_info' => 'nullable|array',
                'is_active' => 'boolean',
                'is_featured' => 'boolean'
            ]);

            $venue = Venue::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Venue created successfully',
                'data' => $venue->load(['user'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create venue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified venue
     */
    public function show($id): JsonResponse
    {
        try {
            $venue = Venue::with(['user', 'venueServices'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $venue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified venue
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $venue = Venue::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'venue_type' => 'sometimes|string|max:100',
                'address' => 'sometimes|string|max:255',
                'city' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'capacity' => 'sometimes|nullable|integer|min:1',
                'contact_email' => 'sometimes|email',
                'contact_phone' => 'sometimes|nullable|string|max:20',
                'website' => 'sometimes|nullable|url',
                'social_links' => 'sometimes|nullable|array',
                'social_links.*' => 'url',
                'images' => 'sometimes|nullable|array',
                'images.*' => 'string',
                'facilities' => 'sometimes|nullable|array',
                'facilities.*' => 'string',
                'opening_hours' => 'sometimes|nullable|array',
                'pricing_info' => 'sometimes|nullable|array',
                'is_active' => 'sometimes|boolean',
                'is_featured' => 'sometimes|boolean'
            ]);

            $venue->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Venue updated successfully',
                'data' => $venue->load(['user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update venue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified venue
     */
    public function destroy($id): JsonResponse
    {
        try {
            $venue = Venue::findOrFail($id);
            $venue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Venue deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete venue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a venue
     */
    public function approve($id): JsonResponse
    {
        try {
            $venue = Venue::findOrFail($id);
            $venue->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Venue approved successfully',
                'data' => $venue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve venue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a venue
     */
    public function reject($id): JsonResponse
    {
        try {
            $venue = Venue::findOrFail($id);
            $venue->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Venue rejected successfully',
                'data' => $venue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject venue: ' . $e->getMessage()
            ], 500);
        }
    }
}
