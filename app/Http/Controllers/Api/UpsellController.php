<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Venue;
use App\Models\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpsellController extends Controller
{
    public function getPromotionTiers()
    {
        $tiers = [
            'standard' => [
                'name' => 'Standard',
                'price' => 0,
                'duration' => '30 days',
                'features' => [
                    'Basic listing',
                    'Appears in search results',
                    'Standard visibility',
                ],
                'badge' => null,
                'visibility_multiplier' => 1,
            ],
            'promoted' => [
                'name' => 'Promoted',
                'price' => 29.99,
                'duration' => '30 days',
                'features' => [
                    'Highlighted listing',
                    'Appears above standard posts',
                    'Promoted badge',
                    '2x more visibility',
                ],
                'badge' => 'Promoted',
                'visibility_multiplier' => 2,
            ],
            'featured' => [
                'name' => 'Featured',
                'price' => 79.99,
                'duration' => '30 days',
                'features' => [
                    'Top of category pages',
                    'Larger card display',
                    'Priority in search results',
                    'Featured badge',
                    'Included in weekly email newsletter',
                    '4x more visibility',
                ],
                'badge' => 'Featured',
                'visibility_multiplier' => 4,
                'popular' => true,
            ],
            'sponsored' => [
                'name' => 'Sponsored',
                'price' => 149.99,
                'duration' => '30 days',
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Homepage slider inclusion',
                    'Social media promotion',
                    'Sponsored badge',
                    '6x more visibility',
                ],
                'badge' => 'Sponsored',
                'visibility_multiplier' => 6,
            ],
            'spotlight' => [
                'name' => 'Spotlight',
                'price' => 299.99,
                'duration' => '30 days',
                'features' => [
                    'Permanently pinned at top of category',
                    'Exclusive Spotlight badge',
                    'Newsletter inclusion',
                    'Top Picks of the Month feature',
                    'Social media promotion',
                    'Homepage priority placement',
                    '10x more visibility',
                ],
                'badge' => 'Spotlight',
                'visibility_multiplier' => 10,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }

    public function upgradeEvent(Request $request, $eventId)
    {
        $request->validate([
            'promotion_tier' => 'required|in:promoted,featured,sponsored,spotlight',
            'payment_method' => 'required|string',
        ]);

        $event = Event::where('user_id', Auth::id())->findOrFail($eventId);
        
        $tiers = $this->getPromotionTiers()->getData()->data;
        $selectedTier = $tiers[$request->promotion_tier];

        // In a real implementation, you would process payment here
        // For now, we'll just update the promotion tier
        $event->update([
            'promotion_tier' => $request->promotion_tier,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Event upgraded to {$selectedTier['name']} successfully",
            'data' => [
                'event' => $event,
                'promotion_tier' => $selectedTier,
            ],
        ]);
    }

    public function upgradeVenue(Request $request, $venueId)
    {
        $request->validate([
            'promotion_tier' => 'required|in:promoted,featured,sponsored,spotlight',
            'payment_method' => 'required|string',
        ]);

        $venue = Venue::where('user_id', Auth::id())->findOrFail($venueId);
        
        $tiers = $this->getPromotionTiers()->getData()->data;
        $selectedTier = $tiers[$request->promotion_tier];

        // In a real implementation, you would process payment here
        // For now, we'll just update the promotion tier
        $venue->update([
            'promotion_tier' => $request->promotion_tier,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Venue upgraded to {$selectedTier['name']} successfully",
            'data' => [
                'venue' => $venue,
                'promotion_tier' => $selectedTier,
            ],
        ]);
    }

    public function upgradeVenueService(Request $request, $serviceId)
    {
        $request->validate([
            'promotion_tier' => 'required|in:promoted,featured,sponsored,spotlight',
            'payment_method' => 'required|string',
        ]);

        $service = VenueService::where('user_id', Auth::id())->findOrFail($serviceId);
        
        $tiers = $this->getPromotionTiers()->getData()->data;
        $selectedTier = $tiers[$request->promotion_tier];

        // In a real implementation, you would process payment here
        // For now, we'll just update the promotion tier
        $service->update([
            'promotion_tier' => $request->promotion_tier,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Venue service upgraded to {$selectedTier['name']} successfully",
            'data' => [
                'service' => $service,
                'promotion_tier' => $selectedTier,
            ],
        ]);
    }

    public function getPromotionStats($type, $id)
    {
        $user = Auth::user();
        
        switch ($type) {
            case 'event':
                $item = Event::where('user_id', $user->id)->findOrFail($id);
                break;
            case 'venue':
                $item = Venue::where('user_id', $user->id)->findOrFail($id);
                break;
            case 'venue_service':
                $item = VenueService::where('user_id', $user->id)->findOrFail($id);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type',
                ], 400);
        }

        $tiers = $this->getPromotionTiers()->getData()->data;
        $currentTier = $tiers[$item->promotion_tier] ?? $tiers['standard'];

        // Mock stats - in a real implementation, you would track actual views, clicks, etc.
        $stats = [
            'current_tier' => $currentTier,
            'views_this_month' => $currentTier['visibility_multiplier'] * 150,
            'clicks_this_month' => $currentTier['visibility_multiplier'] * 25,
            'enquiries_this_month' => $currentTier['visibility_multiplier'] * 8,
            'potential_reach' => $currentTier['visibility_multiplier'] * 1000,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getNetworkWideBoost()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'Network-Wide Boost',
                'price' => 499.99,
                'duration' => '30 days',
                'features' => [
                    'Appears across multiple pages',
                    'Included in all newsletters',
                    'Social media promotion on all platforms',
                    'Priority placement in search results',
                    'Featured in "Top Picks" sections',
                    '15x more visibility',
                ],
                'description' => 'Maximum exposure across the entire Worldwide Adverts network',
            ],
        ]);
    }

    public function purchaseNetworkWideBoost(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:event,venue,venue_service',
            'item_id' => 'required|integer',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        
        switch ($request->item_type) {
            case 'event':
                $item = Event::where('user_id', $user->id)->findOrFail($request->item_id);
                break;
            case 'venue':
                $item = Venue::where('user_id', $user->id)->findOrFail($request->item_id);
                break;
            case 'venue_service':
                $item = VenueService::where('user_id', $user->id)->findOrFail($request->item_id);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid item type',
                ], 400);
        }

        // In a real implementation, you would process payment and set up the boost
        // For now, we'll just return a success message
        
        return response()->json([
            'success' => true,
            'message' => 'Network-Wide Boost purchased successfully',
            'data' => [
                'item' => $item,
                'boost_active' => true,
                'expires_at' => now()->addDays(30),
            ],
        ]);
    }
}
