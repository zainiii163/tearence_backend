<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VideoAdvertAgentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideoAdvertAgentController extends Controller
{
    public function __construct(protected VideoAdvertAgentService $agent)
    {
    }

    /**
     * Generate a video advert script.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'target_audience' => 'nullable|string|max:255',
            'key_benefits' => 'nullable|array',
            'key_benefits.*' => 'string|max:255',
            'tone' => 'nullable|string|in:professional,casual,energetic,emotional,authoritative,friendly',
            'duration_seconds' => 'nullable|integer|min:15|max:120',
            'platform' => 'nullable|string|in:social_media,youtube,tiktok,instagram,facebook,linkedin,website',
            'variations' => 'nullable|boolean',
            'variation_count' => 'nullable|integer|min:1|max:5',
        ]);

        $validated['tone'] = $validated['tone'] ?? 'professional';
        $validated['duration_seconds'] = $validated['duration_seconds'] ?? 30;
        $validated['platform'] = $validated['platform'] ?? 'social_media';

        if ($validated['variations'] ?? false) {
            $count = min($validated['variation_count'] ?? 3, 5);
            $scripts = $this->agent->generateVariations($validated, $count);
            return response()->json([
                'success' => true,
                'data' => ['scripts' => $scripts],
            ]);
        }

        $script = $this->agent->generateScript($validated);

        return response()->json([
            'success' => true,
            'data' => ['script' => $script],
        ]);
    }

    /**
     * Trigger learning from existing platform content.
     */
    public function learn(Request $request): JsonResponse
    {
        $learnings = $this->agent->learnFromContent();

        return response()->json([
            'success' => true,
            'data' => [
                'patterns_learned' => count($learnings),
                'patterns' => $learnings,
            ],
        ]);
    }

    /**
     * Get generation history for analytics.
     */
    public function history(Request $request): JsonResponse
    {
        $history = Cache::get('video_ad_agent.generation_history', []);
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_generations' => count($history),
                'history' => array_slice($history, -20), // Last 20
            ],
        ]);
    }
}