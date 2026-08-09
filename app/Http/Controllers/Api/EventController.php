<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Venue;
use App\Models\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['user', 'venue', 'venueServices'])
            ->active();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->byCategory($request->input('category'));
        }

        // Location filter
        if ($request->has('country')) {
            $query->byLocation($request->input('country'), $request->input('city'));
        }

        // Date filter
        if ($request->has('date_from')) {
            $query->where('date_time', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('date_time', '<=', $request->input('date_to'));
        }

        // Price filter
        if ($request->has('min_price')) {
            $query->where('ticket_price', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('ticket_price', '<=', $request->input('max_price'));
        }

        // Price type filter
        if ($request->has('price_type')) {
            $query->where('price_type', $request->input('price_type'));
        }

        // Promotion tier filter
        if ($request->has('promotion_tier')) {
            $query->byPromotionTier($request->input('promotion_tier'));
        }

        // Sort
        $sort = $request->input('sort', 'date_time');
        $order = $request->input('order', 'asc');
        
        switch ($sort) {
            case 'date':
                $query->orderBy('date_time', $order);
                break;
            case 'title':
                $query->orderBy('title', $order);
                break;
            case 'price':
                $query->orderBy('ticket_price', $order);
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            default:
                $query->orderBy('date_time', 'asc');
        }

        $events = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function show($slug)
    {
        $event = Event::with(['user', 'venue', 'venueServices'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
    }

    public function store(Request $request)
    {
        // Normalize Social Hub composer aliases → Event schema
        if (!$request->filled('category') && $request->filled('category_id')) {
            $request->merge(['category' => $request->input('category_id')]);
        }
        if (!$request->filled('date_time') && $request->filled('start_date')) {
            $request->merge(['date_time' => $request->input('start_date')]);
        }
        if (!$request->filled('venue_name') && $request->filled('location')) {
            $request->merge(['venue_name' => $request->input('location')]);
        }
        if (!$request->filled('expected_attendance') && $request->filled('max_attendees')) {
            $request->merge(['expected_attendance' => $request->input('max_attendees')]);
        }
        if (!$request->filled('video_link') && $request->filled('meeting_link')) {
            $request->merge(['video_link' => $request->input('meeting_link')]);
        }
        if (!$request->filled('price_type')) {
            $request->merge(['price_type' => 'free']);
        }
        if (!$request->filled('country')) {
            $user = Auth::user();
            $request->merge(['country' => $user->country ?? 'Worldwide']);
        }
        if (!$request->filled('city')) {
            $user = Auth::user();
            $city = $user->city ?? null;
            if (!$city && $request->filled('location')) {
                $city = $request->input('location');
            }
            $request->merge(['city' => $city ?: 'Online']);
        }
        if (!$request->filled('contact_email')) {
            $user = Auth::user();
            $request->merge([
                'contact_email' => $user->email
                    ?? $user->user_email
                    ?? 'events@worldwideadverts.info',
            ]);
        }
        if ($request->boolean('is_virtual') && !$request->filled('venue_name')) {
            $request->merge(['venue_name' => 'Virtual event']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:concert,workshop,party,festival,conference,sports,cultural,food_drink,charity,other',
            'date_time' => 'required|date|after:now',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'venue_name' => 'nullable|string|max:255',
            'ticket_price' => 'nullable|numeric|min:0',
            'price_type' => 'required|in:free,paid,donation',
            'description' => 'required|string',
            'schedule' => 'nullable|string',
            'age_restrictions' => 'nullable|string|max:100',
            'dress_code' => 'nullable|string|max:100',
            'expected_attendance' => 'nullable|integer|min:1',
            'ticket_link' => 'nullable|url',
            'contact_email' => 'required|email',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'cover_image' => 'nullable',
            'video_link' => 'nullable|url',
            'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,spotlight',
            'venue_id' => 'nullable|exists:venues,id',
            'venue_services' => 'nullable|array',
            'venue_services.*' => 'exists:venue_services,id',
            'tags' => 'nullable',
            'end_date' => 'nullable|date|after_or_equal:date_time',
            'is_virtual' => 'nullable|boolean',
            'meeting_link' => 'nullable|url',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['promotion_tier'] = $validated['promotion_tier'] ?? 'standard';

        $images = $validated['images'] ?? [];
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('events', 'public');
            $images[] = Storage::url($path);
        } elseif (is_string($request->input('cover_image')) && $request->input('cover_image') !== '') {
            $images[] = $request->input('cover_image');
        }
        $validated['images'] = $images ?: null;

        // Persist only Event fillable fields
        $payload = collect($validated)->only([
            'title', 'category', 'date_time', 'country', 'city', 'venue_name',
            'ticket_price', 'price_type', 'description', 'schedule',
            'age_restrictions', 'dress_code', 'expected_attendance',
            'ticket_link', 'contact_email', 'social_links', 'images',
            'video_link', 'promotion_tier', 'user_id', 'venue_id',
        ])->all();

        $event = Event::create($payload);

        // Attach venue services if provided
        if (!empty($validated['venue_services'])) {
            $event->venueServices()->attach($validated['venue_services']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event->load(['user', 'venue', 'venueServices']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|in:concert,workshop,party,festival,conference,sports,cultural,food_drink,charity,other',
            'date_time' => 'sometimes|date|after:now',
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'venue_name' => 'sometimes|nullable|string|max:255',
            'ticket_price' => 'sometimes|nullable|numeric|min:0',
            'price_type' => 'sometimes|in:free,paid,donation',
            'description' => 'sometimes|string',
            'schedule' => 'sometimes|nullable|string',
            'age_restrictions' => 'sometimes|nullable|string|max:100',
            'dress_code' => 'sometimes|nullable|string|max:100',
            'expected_attendance' => 'sometimes|nullable|integer|min:1',
            'ticket_link' => 'sometimes|nullable|url',
            'contact_email' => 'sometimes|email',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'url',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string',
            'video_link' => 'sometimes|nullable|url',
            'promotion_tier' => 'sometimes|in:standard,promoted,featured,sponsored,spotlight',
            'venue_id' => 'sometimes|nullable|exists:venues,id',
            'venue_services' => 'sometimes|nullable|array',
            'venue_services.*' => 'exists:venue_services,id',
        ]);

        $event->update($validated);

        // Sync venue services if provided
        if (isset($validated['venue_services'])) {
            $event->venueServices()->sync($validated['venue_services']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event->load(['user', 'venue', 'venueServices']),
        ]);
    }

    public function destroy($id)
    {
        $event = Event::where('user_id', Auth::id())->findOrFail($id);
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }

    public function myEvents(Request $request)
    {
        $events = Event::with(['venue', 'venueServices'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function featuredEvents()
    {
        $events = Event::with(['user', 'venue'])
            ->active()
            ->whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])
            ->upcoming()
            ->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured')")
            ->orderBy('date_time', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function categories()
    {
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

        // Array shape for Social Hub CreateEventForm + map for legacy clients
        $list = collect($categories)->map(function ($label, $key) {
            return [
                'id' => $key,
                'category_id' => $key,
                'name' => $label,
                'label' => $label,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $list,
            'map' => $categories,
        ]);
    }

    public function uploadImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('events', 'public');
            $uploadedImages[] = Storage::url($path);
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedImages,
        ]);
    }
}
