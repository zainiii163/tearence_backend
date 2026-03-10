<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ServiceComparisonController extends Controller
{
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'service_ids' => 'required|array|min:2|max:4',
            'service_ids.*' => 'exists:services,id',
        ]);

        $services = Service::with([
            'user:id,name,email,country',
            'category:id,name',
            'packages',
            'reviews' => function($query) {
                $query->latest()->limit(5);
            },
            'upsells' => function($query) {
                $query->active();
            }
        ])->whereIn('id', $request->service_ids)
           ->active()
           ->get();

        if ($services->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'At least 2 active services are required for comparison',
            ], 400);
        }

        $comparison = [
            'services' => $services->map(function($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'description' => substr($service->description, 0, 200) . '...',
                    'provider' => [
                        'name' => $service->user->name ?? 'Unknown',
                        'country' => $service->user->country ?? 'Unknown',
                        'verified' => $service->verified,
                    ],
                    'category' => $service->category->name ?? 'Uncategorized',
                    'pricing' => [
                        'base_price' => $service->base_price,
                        'pricing_model' => $service->pricing_model,
                        'packages' => $service->packages->map(function($package) {
                            return [
                                'name' => $package->name,
                                'price' => $package->price,
                                'delivery_time' => $package->delivery_time,
                                'revisions' => $package->revisions,
                                'features' => $package->features,
                                'is_popular' => $package->is_popular,
                            ];
                        }),
                    ],
                    'delivery' => [
                        'delivery_time' => $service->delivery_time,
                        'revisions_included' => $service->revisions_included,
                        'extra_fast_delivery' => $service->extra_fast_delivery,
                    ],
                    'quality' => [
                        'rating' => $service->rating,
                        'reviews_count' => $service->reviews_count,
                        'orders_count' => $service->orders_count,
                        'skill_level' => $service->skill_level,
                    ],
                    'features' => [
                        'featured' => $service->featured,
                        'verified' => $service->verified,
                        'upsells' => $service->upsells->map(function($upsell) {
                            return [
                                'type' => $upsell->upsell_type,
                                'benefits' => $upsell->benefits,
                                'expires_at' => $upsell->expires_at,
                            ];
                        }),
                    ],
                    'engagement' => [
                        'views_count' => $service->views_count,
                        'recent_reviews' => $service->reviews->take(3)->map(function($review) {
                            return [
                                'rating' => $review->rating,
                                'comment' => substr($review->comment, 0, 100) . '...',
                                'created_at' => $review->created_at->diffForHumans(),
                            ];
                        }),
                    ],
                ];
            }),
            'comparison_matrix' => $this->buildComparisonMatrix($services),
            'recommendations' => $this->generateRecommendations($services),
        ];

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    public function saveComparison(Request $request): JsonResponse
    {
        $request->validate([
            'service_ids' => 'required|array|min:2|max:4',
            'service_ids.*' => 'exists:services,id',
            'name' => 'required|string|max:255',
        ]);

        // Save comparison to user's saved comparisons (could implement this later)
        $comparison = [
            'user_id' => Auth::id(),
            'name' => $request->name,
            'service_ids' => $request->service_ids,
            'created_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Comparison saved successfully',
            'data' => $comparison,
        ]);
    }

    private function buildComparisonMatrix($services): array
    {
        $matrix = [];
        $criteria = [
            'price' => 'Base Price',
            'rating' => 'Rating',
            'delivery_time' => 'Delivery Speed',
            'revisions' => 'Revisions Included',
            'experience' => 'Experience Level',
            'orders' => 'Total Orders',
            'reviews' => 'Total Reviews',
        ];

        foreach ($criteria as $key => $label) {
            $matrix[$key] = [
                'label' => $label,
                'values' => $services->map(function($service) use ($key) {
                    switch ($key) {
                        case 'price':
                            return $service->base_price;
                        case 'rating':
                            return $service->rating;
                        case 'delivery_time':
                            return $this->getDeliveryScore($service->delivery_time);
                        case 'revisions':
                            return $service->revisions_included;
                        case 'experience':
                            return $this->getExperienceScore($service->skill_level);
                        case 'orders':
                            return $service->orders_count;
                        case 'reviews':
                            return $service->reviews_count;
                        default:
                            return 0;
                    }
                })->toArray(),
                'best_value' => $this->getBestValueIndex($key, $services),
            ];
        }

        return $matrix;
    }

    private function generateRecommendations($services): array
    {
        $recommendations = [];

        // Best overall value
        $bestValue = $services->sortByDesc(function($service) {
            return ($service->rating * 0.4) + 
                   (($service->orders_count > 0 ? log($service->orders_count) : 0) * 0.3) +
                   ((1 / max($service->base_price, 1)) * 0.3);
        })->first();

        $recommendations[] = [
            'type' => 'best_value',
            'service_id' => $bestValue->id,
            'title' => 'Best Overall Value',
            'description' => 'Offers the best combination of price, quality, and experience',
        ];

        // Highest rated
        $highestRated = $services->sortByDesc('rating')->first();
        if ($highestRated->rating >= 4.5) {
            $recommendations[] = [
                'type' => 'highest_rated',
                'service_id' => $highestRated->id,
                'title' => 'Highest Rated',
                'description' => 'Top-rated provider with excellent customer satisfaction',
            ];
        }

        // Fastest delivery
        $fastest = $services->sortBy(function($service) {
            return $this->getDeliveryScore($service->delivery_time);
        })->first();

        $recommendations[] = [
            'type' => 'fastest_delivery',
            'service_id' => $fastest->id,
            'title' => 'Fastest Delivery',
            'description' => 'Quickest turnaround time for urgent projects',
        ];

        // Most experienced
        $mostExperienced = $services->sortByDesc('orders_count')->first();
        if ($mostExperienced->orders_count > 10) {
            $recommendations[] = [
                'type' => 'most_experienced',
                'service_id' => $mostExperienced->id,
                'title' => 'Most Experienced',
                'description' => 'Extensive track record with many completed orders',
            ];
        }

        return $recommendations;
    }

    private function getDeliveryScore($deliveryTime): int
    {
        $scores = [
            '1_day' => 5,
            '3_days' => 4,
            '1_week' => 3,
            '2_weeks' => 2,
            '1_month' => 1,
            'custom' => 2,
        ];

        return $scores[$deliveryTime] ?? 2;
    }

    private function getExperienceScore($skillLevel): int
    {
        $scores = [
            'beginner' => 1,
            'intermediate' => 2,
            'expert' => 3,
            'professional' => 4,
        ];

        return $scores[$skillLevel] ?? 1;
    }

    private function getBestValueIndex($criteria, $services): int
    {
        $values = $services->map(function($service) use ($criteria) {
            switch ($criteria) {
                case 'price':
                    return $service->base_price;
                default:
                    return $this->getComparisonValue($criteria, $service);
            }
        })->toArray();

        if ($criteria === 'price') {
            return array_search(min($values), $values);
        } else {
            return array_search(max($values), $values);
        }
    }

    private function getComparisonValue($criteria, $service)
    {
        switch ($criteria) {
            case 'rating':
                return $service->rating;
            case 'delivery_time':
                return $this->getDeliveryScore($service->delivery_time);
            case 'revisions':
                return $service->revisions_included;
            case 'experience':
                return $this->getExperienceScore($service->skill_level);
            case 'orders':
                return $service->orders_count;
            case 'reviews':
                return $service->reviews_count;
            default:
                return 0;
        }
    }
}
