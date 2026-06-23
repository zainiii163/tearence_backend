<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EventAdminController extends Controller
{
    /**
     * Display events dashboard with analytics
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_events' => Event::count(),
                'active_events' => Event::where('is_active', true)->count(),
                'pending_events' => Event::where('is_active', false)->count(),
                'upcoming_events' => Event::where('date_time', '>=', now())->count(),
                'past_events' => Event::where('date_time', '<', now())->count(),
                'featured_events' => Event::whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])->count(),
                'revenue_from_promotions' => $this->calculatePromotionRevenue(),
                'events_this_month' => Event::whereMonth('created_at', now()->month)->count(),
                'events_today' => Event::whereDate('created_at', today())->count(),
            ];

            $recentEvents = Event::with(['user', 'venue'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $popularCategories = Event::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_events' => $recentEvents,
                    'popular_categories' => $popularCategories,
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
     * Display a listing of events
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Event::with(['user', 'venue', 'venueServices']);

            // Filters
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('promotion_tier')) {
                $query->where('promotion_tier', $request->promotion_tier);
            }

            if ($request->has('date_from')) {
                $query->whereDate('date_time', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('date_time', '<=', $request->date_to);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('venue_name', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $events = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request): JsonResponse
    {
        try {
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
                'video_link' => 'nullable|url',
                'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,spotlight',
                'venue_id' => 'nullable|exists:venues,id',
                'is_active' => 'boolean'
            ]);

            $event = Event::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event->load(['user', 'venue'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified event
     */
    public function show($id): JsonResponse
    {
        try {
            $event = Event::with(['user', 'venue', 'venueServices'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);

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
                'is_active' => 'sometimes|boolean'
            ]);

            $event->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event->load(['user', 'venue'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified event
     */
    public function destroy($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve an event
     */
    public function approve($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Event approved successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an event
     */
    public function reject($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Event rejected successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle event active status
     */
    public function toggleActive($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->update(['is_active' => !$event->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Event status updated successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle event status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upgrade event promotion tier
     */
    public function upgradeTier(Request $request, $id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'promotion_tier' => 'required|in:promoted,featured,sponsored,spotlight'
            ]);

            $event->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event promotion tier upgraded successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade promotion tier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set event as featured
     */
    public function setFeatured($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->update(['promotion_tier' => 'featured']);

            return response()->json([
                'success' => true,
                'message' => 'Event set as featured successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set event as featured: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set event as sponsored
     */
    public function setSponsored($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->update(['promotion_tier' => 'sponsored']);

            return response()->json([
                'success' => true,
                'message' => 'Event set as sponsored successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set event as sponsored: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve events
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_ids' => 'required|array',
                'event_ids.*' => 'exists:events,id'
            ]);

            Event::whereIn('id', $validated['event_ids'])
                ->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Events approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject events
     */
    public function bulkReject(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_ids' => 'required|array',
                'event_ids.*' => 'exists:events,id'
            ]);

            Event::whereIn('id', $validated['event_ids'])
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Events rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update events
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_ids' => 'required|array',
                'event_ids.*' => 'exists:events,id',
                'updates' => 'required|array'
            ]);

            Event::whereIn('id', $validated['event_ids'])
                ->update($validated['updates']);

            return response()->json([
                'success' => true,
                'message' => 'Events updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk update events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete events
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_ids' => 'required|array',
                'event_ids.*' => 'exists:events,id'
            ]);

            Event::whereIn('id', $validated['event_ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Events deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export events data
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $events = Event::with(['user', 'venue'])
                ->get()
                ->map(function ($event) {
                    return [
                        'ID' => $event->id,
                        'Title' => $event->title,
                        'Category' => $event->category,
                        'Date' => $event->date_time,
                        'Venue' => $event->venue_name,
                        'City' => $event->city,
                        'Country' => $event->country,
                        'Price Type' => $event->price_type,
                        'Ticket Price' => $event->ticket_price,
                        'Promotion Tier' => $event->promotion_tier,
                        'Status' => $event->is_active ? 'Active' : 'Inactive',
                        'Created By' => $event->user ? $event->user->name : 'N/A',
                        'Created At' => $event->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reports and analytics
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30'); // days
            
            $startDate = Carbon::now()->subDays($period);
            
            $report = [
                'events_created' => Event::where('created_at', '>=', $startDate)->count(),
                'events_by_category' => Event::select('category', DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('category')
                    ->get(),
                'events_by_promotion_tier' => Event::select('promotion_tier', DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('promotion_tier')
                    ->get(),
                'revenue_by_promotions' => $this->calculatePromotionRevenue($startDate),
                'top_organizers' => User::select('users.name', 'users.email', DB::raw('count(events.id) as events_count'))
                    ->join('events', 'users.id', '=', 'events.user_id')
                    ->where('events.created_at', '>=', $startDate)
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('events_count', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get promotion report
     */
    public function promotionReport(Request $request): JsonResponse
    {
        try {
            $report = [
                'total_promoted_events' => Event::whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])->count(),
                'promotion_tiers_breakdown' => Event::select('promotion_tier', DB::raw('count(*) as count'))
                    ->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])
                    ->groupBy('promotion_tier')
                    ->get(),
                'promotion_revenue' => $this->calculatePromotionRevenue(),
                'upcoming_promoted_events' => Event::whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])
                    ->where('date_time', '>=', now())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate promotion report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics overview
     */
    public function analyticsOverview(Request $request): JsonResponse
    {
        try {
            $analytics = [
                'growth_metrics' => $this->getGrowthMetrics(),
                'engagement_metrics' => $this->getEngagementMetrics(),
                'revenue_metrics' => $this->getRevenueMetrics(),
                'performance_metrics' => $this->getPerformanceMetrics(),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics overview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular events
     */
    public function popularEvents(Request $request): JsonResponse
    {
        try {
            $events = Event::with(['user', 'venue'])
                ->where('is_active', true)
                ->orderBy('views', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popular events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event trends
     */
    public function eventTrends(Request $request): JsonResponse
    {
        try {
            $trends = [
                'category_trends' => Event::select('category', DB::raw('count(*) as count'))
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get(),
                'monthly_trends' => Event::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', Carbon::now()->subYear())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $trends
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get event trends: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance analytics
     */
    public function attendanceAnalytics(Request $request): JsonResponse
    {
        try {
            $analytics = [
                'total_expected_attendance' => Event::sum('expected_attendance'),
                'attendance_by_category' => Event::select('category', DB::raw('sum(expected_attendance) as total'))
                    ->groupBy('category')
                    ->get(),
                'upcoming_events_attendance' => Event::where('date_time', '>=', now())
                    ->sum('expected_attendance'),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue analytics
     */
    public function revenueAnalytics(Request $request): JsonResponse
    {
        try {
            $revenue = [
                'total_revenue' => $this->calculatePromotionRevenue(),
                'revenue_by_tier' => $this->getRevenueByTier(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
            ];

            return response()->json([
                'success' => true,
                'data' => $revenue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get revenue analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            return $this->analyticsOverview($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    private function calculatePromotionRevenue($startDate = null): float
    {
        $query = Event::whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight']);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $events = $query->get();
        
        $revenue = 0;
        foreach ($events as $event) {
            switch ($event->promotion_tier) {
                case 'promoted':
                    $revenue += 19.99;
                    break;
                case 'featured':
                    $revenue += 49.99;
                    break;
                case 'sponsored':
                    $revenue += 99.99;
                    break;
                case 'spotlight':
                    $revenue += 199.99;
                    break;
            }
        }

        return $revenue;
    }

    private function getGrowthMetrics(): array
    {
        $currentMonth = Event::whereMonth('created_at', now()->month)->count();
        $lastMonth = Event::whereMonth('created_at', now()->subMonth()->month)->count();
        
        return [
            'current_month_events' => $currentMonth,
            'last_month_events' => $lastMonth,
            'growth_rate' => $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0,
        ];
    }

    private function getEngagementMetrics(): array
    {
        return [
            'total_views' => Event::sum('views'),
            'average_views_per_event' => Event::avg('views'),
            'events_with_images' => Event::whereNotNull('images')->where('images', '!=', '[]')->count(),
        ];
    }

    private function getRevenueMetrics(): array
    {
        return [
            'total_revenue' => $this->calculatePromotionRevenue(),
            'revenue_this_month' => $this->calculatePromotionRevenue(Carbon::now()->startOfMonth()),
            'average_revenue_per_event' => Event::whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])->count() > 0 
                ? $this->calculatePromotionRevenue() / Event::whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])->count() 
                : 0,
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'approval_rate' => Event::count() > 0 ? (Event::where('is_active', true)->count() / Event::count()) * 100 : 0,
            'average_events_per_user' => User::has('events')->count() > 0 ? Event::count() / User::has('events')->count() : 0,
        ];
    }

    private function getRevenueByTier(): array
    {
        return [
            'promoted' => Event::where('promotion_tier', 'promoted')->count() * 19.99,
            'featured' => Event::where('promotion_tier', 'featured')->count() * 49.99,
            'sponsored' => Event::where('promotion_tier', 'sponsored')->count() * 99.99,
            'spotlight' => Event::where('promotion_tier', 'spotlight')->count() * 199.99,
        ];
    }

    private function getMonthlyRevenue(): array
    {
        return Event::select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), 'promotion_tier', DB::raw('count(*) as count'))
            ->whereIn('promotion_tier', ['promoted', 'featured', 'sponsored', 'spotlight'])
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month', 'year', 'promotion_tier')
            ->get()
            ->groupBy('month')
            ->map(function ($monthData) {
                $monthlyRevenue = 0;
                foreach ($monthData as $data) {
                    $monthlyRevenue += $data->count * $this->getTierPrice($data->promotion_tier);
                }
                return $monthlyRevenue;
            })
            ->toArray();
    }

    private function getTierPrice($tier): float
    {
        switch ($tier) {
            case 'promoted': return 19.99;
            case 'featured': return 49.99;
            case 'sponsored': return 99.99;
            case 'spotlight': return 199.99;
            default: return 0;
        }
    }
}
