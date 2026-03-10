<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VenueServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = VenueService::with(['user', 'events'])
            ->active();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Service category filter
        if ($request->has('service_category')) {
            $query->byCategory($request->input('service_category'));
        }

        // Location filter
        if ($request->has('country')) {
            $query->byLocation($request->input('country'), $request->input('city'));
        }

        // Price filter
        if ($request->has('min_price') || $request->has('max_price')) {
            $query->byPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );
        }

        // Promotion tier filter
        if ($request->has('promotion_tier')) {
            $query->byPromotionTier($request->input('promotion_tier'));
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        switch ($sort) {
            case 'name':
                $query->orderBy('name', $order);
                break;
            case 'price':
                $query->orderBy('min_price', $order);
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $services = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show($slug)
    {
        $service = VenueService::with(['user', 'events' => function ($query) {
            $query->active()->upcoming()->orderBy('date_time', 'asc')->limit(5);
        }])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_category' => 'required|in:catering,dj,decor,photography,security,event_planner,av_equipment,transportation,other',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'packages' => 'nullable|array',
            'packages.*.name' => 'required|string',
            'packages.*.description' => 'required|string',
            'packages.*.price' => 'required|numeric|min:0',
            'availability' => 'nullable|array',
            'website' => 'nullable|url',
            'contact_email' => 'required|email',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'portfolio_images' => 'nullable|array',
            'portfolio_images.*' => 'string',
            'video_link' => 'nullable|url',
            'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,spotlight',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['promotion_tier'] = $validated['promotion_tier'] ?? 'standard';

        $service = VenueService::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Venue service created successfully',
            'data' => $service->load(['user', 'events']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $service = VenueService::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'service_category' => 'sometimes|in:catering,dj,decor,photography,security,event_planner,av_equipment,transportation,other',
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'min_price' => 'sometimes|nullable|numeric|min:0',
            'max_price' => 'sometimes|nullable|numeric|min:0',
            'description' => 'sometimes|string',
            'packages' => 'sometimes|nullable|array',
            'packages.*.name' => 'required|string',
            'packages.*.description' => 'required|string',
            'packages.*.price' => 'required|numeric|min:0',
            'availability' => 'sometimes|nullable|array',
            'website' => 'sometimes|nullable|url',
            'contact_email' => 'sometimes|email',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'url',
            'portfolio_images' => 'sometimes|nullable|array',
            'portfolio_images.*' => 'string',
            'video_link' => 'sometimes|nullable|url',
            'promotion_tier' => 'sometimes|in:standard,promoted,featured,sponsored,spotlight',
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Venue service updated successfully',
            'data' => $service->load(['user', 'events']),
        ]);
    }

    public function destroy($id)
    {
        $service = VenueService::where('user_id', Auth::id())->findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Venue service deleted successfully',
        ]);
    }

    public function myServices(Request $request)
    {
        $services = VenueService::with(['events'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function featuredServices()
    {
        $services = VenueService::with(['user'])
            ->active()
            ->whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])
            ->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured')")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function serviceCategories()
    {
        $categories = [
            'catering' => 'Catering',
            'dj' => 'DJ Services',
            'decor' => 'Decor & Styling',
            'photography' => 'Photography',
            'security' => 'Security Services',
            'event_planner' => 'Event Planning',
            'av_equipment' => 'AV Equipment',
            'transportation' => 'Transportation',
            'other' => 'Other',
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function uploadPortfolioImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('venue-services', 'public');
            $uploadedImages[] = Storage::url($path);
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedImages,
        ]);
    }

    public function addToEvent(Request $request, $eventId)
    {
        $request->validate([
            'venue_service_id' => 'required|exists:venue_services,id',
            'notes' => 'nullable|string',
        ]);

        $event = \App\Models\Event::where('user_id', Auth::id())->findOrFail($eventId);
        $venueService = VenueService::findOrFail($request->venue_service_id);

        // Check if already attached
        if ($event->venueServices()->where('venue_service_id', $venueService->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Venue service already added to this event',
            ], 422);
        }

        $event->venueServices()->attach($venueService->id, [
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Venue service added to event successfully',
            'data' => $event->load('venueServices'),
        ]);
    }

    public function removeFromEvent($eventId, $serviceId)
    {
        $event = \App\Models\Event::where('user_id', Auth::id())->findOrFail($eventId);
        
        $event->venueServices()->detach($serviceId);

        return response()->json([
            'success' => true,
            'message' => 'Venue service removed from event successfully',
        ]);
    }

    public function updateEventServiceStatus(Request $request, $eventId, $serviceId)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $event = \App\Models\Event::where('user_id', Auth::id())->findOrFail($eventId);
        
        $event->venueServices()->updateExistingPivot($serviceId, [
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event venue service status updated successfully',
            'data' => $event->load('venueServices'),
        ]);
    }
}
