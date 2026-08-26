<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoAdvertAgentService
{
    protected string $provider;
    protected array $config;

    public function __construct()
    {
        $this->provider = config('video_ad_agent.provider', 'openai');
        $this->config = config('video_ad_agent', []);
    }

    /**
     * Generate a video advert script/storyboard based on business and product info.
     */
    public function generateScript(array $input): array
    {
        $prompt = $this->buildPrompt($input);
        
        $response = $this->callProvider($prompt);
        
        $script = $this->parseResponse($response, $input);
        
        // Learn from this generation for future improvements
        $this->learnFromGeneration($input, $script);
        
        return $script;
    }

    /**
     * Generate multiple variations for A/B testing.
     */
    public function generateVariations(array $input, int $count = 3): array
    {
        $variations = [];
        for ($i = 0; $i < $count; $i++) {
            $variationInput = array_merge($input, ['variation_seed' => $i]);
            $variations[] = $this->generateScript($variationInput);
        }
        return $variations;
    }

    /**
     * Learn from existing content on the platform.
     */
    public function learnFromContent(): array
    {
        // Fetch high-performing adverts
        $topAdverts = $this->getTopPerformingAdverts();
        
        $learnings = [];
        foreach ($topAdverts as $advert) {
            $learnings[] = $this->extractPatterns($advert);
        }
        
        // Store learned patterns for future generations
        Cache::put('video_ad_agent.learned_patterns', $learnings, now()->addDays(30));
        
        return $learnings;
    }

    /**
     * Get top performing adverts from the platform.
     */
    protected function getTopPerformingAdverts(int $limit = 50): array
    {
        // This would query the database for adverts with high engagement
        // For now, return empty array - implement based on your analytics
        return [];
    }

    /**
     * Extract patterns from successful adverts.
     */
    protected function extractPatterns($advert): array
    {
        return [
            'category' => $advert->category->slug ?? 'unknown',
            'title_structure' => $this->analyzeTitleStructure($advert->title),
            'description_length' => strlen($advert->description ?? ''),
            'has_video' => (bool) ($advert->video_url ?? false),
            'engagement_rate' => $this->calculateEngagementRate($advert),
        ];
    }

    /**
     * Build the prompt for the AI provider.
     */
    protected function buildPrompt(array $input): string
    {
        $businessName = $input['business_name'] ?? 'Your Business';
        $productName = $input['product_name'] ?? 'Your Product';
        $category = $input['category'] ?? 'General';
        $targetAudience = $input['target_audience'] ?? 'General consumers';
        $keyBenefits = $input['key_benefits'] ?? [];
        $tone = $input['tone'] ?? 'professional';
        $duration = $input['duration_seconds'] ?? 30;
        $platform = $input['platform'] ?? 'social_media';
        $learnedPatterns = Cache::get('video_ad_agent.learned_patterns', []);

        $patternsText = '';
        if (!empty($learnedPatterns)) {
            $patternsText = "\n\nLearned patterns from successful adverts:\n";
            foreach (array_slice($learnedPatterns, 0, 5) as $pattern) {
                $patternsText .= "- Category: {$pattern['category']}, Title: {$pattern['title_structure']}, Duration: {$pattern['description_length']} chars\n";
            }
        }

        return <<<PROMPT
You are an expert video advert creator for Worldwide Adverts marketplace.
Create a compelling {$duration}-second video advert script for:

Business: {$businessName}
Product/Service: {$productName}
Category: {$category}
Target Audience: {$targetAudience}
Key Benefits: {@implode(', ', $keyBenefits)}
Tone: {$tone}
Platform: {$platform}
{$patternsText}

Output format (JSON):
{
  "title": "Catchy title for the video",
  "hook": "First 3 seconds - grab attention",
  "problem": "Identify the pain point",
  "solution": "How the product/service solves it",
  "benefits": ["Benefit 1", "Benefit 2", "Benefit 3"],
  "social_proof": "Testimonial or trust signal",
  "call_to_action": "Clear CTA with urgency",
  "scenes": [
    {"time": "0-3s", "visual": "Description", "audio": "Voiceover/music"},
    {"time": "3-10s", "visual": "Description", "audio": "Voiceover/music"},
    {"time": "10-20s", "visual": "Description", "audio": "Voiceover/music"},
    {"time": "20-{$duration}s", "visual": "Description", "audio": "Voiceover/music"}
  ],
  "music_suggestion": "Upbeat/calm/energetic",
  "text_overlays": ["Key text to show on screen"],
  "hashtags": ["#hashtag1", "#hashtag2"]
}
PROMPT;
    }

    /**
     * Call the AI provider (OpenAI, Anthropic, etc.)
     */
    protected function callProvider(string $prompt): string
    {
        switch ($this->provider) {
            case 'openai':
                return $this->callOpenAI($prompt);
            case 'anthropic':
                return $this->callAnthropic($prompt);
            default:
                return $this->mockResponse();
        }
    }

    protected function callOpenAI(string $prompt): string
    {
        $apiKey = $this->config['openai_api_key'] ?? env('OPENAI_API_KEY');
        
        if (! $apiKey) {
            Log::warning('OpenAI API key not configured, returning mock');
            return $this->mockResponse();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->config['openai_model'] ?? 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert video advert creator. Output only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::warning('OpenAI API error: ' . $response->body());
            return $this->mockResponse();
        } catch (\Throwable $e) {
            Log::warning('OpenAI call failed: ' . $e->getMessage());
            return $this->mockResponse();
        }
    }

    protected function callAnthropic(string $prompt): string
    {
        $apiKey = $this->config['anthropic_api_key'] ?? env('ANTHROPIC_API_KEY');
        
        if (! $apiKey) {
            return $this->mockResponse();
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->config['anthropic_model'] ?? 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            return $this->mockResponse();
        } catch (\Throwable $e) {
            Log::warning('Anthropic call failed: ' . $e->getMessage());
            return $this->mockResponse();
        }
    }

    protected function mockResponse(): string
    {
        return json_encode([
            'title' => 'Transform Your Business Today',
            'hook' => 'Stop wasting time on manual tasks!',
            'problem' => 'Business owners spend 20+ hours/week on admin work.',
            'solution' => 'Our AI automation handles it all in minutes.',
            'benefits' => ['Save 15+ hours/week', 'Reduce errors by 95%', 'Scale without hiring'],
            'social_proof' => 'Join 500+ businesses already saving time',
            'call_to_action' => 'Start free trial now - no credit card required!',
            'scenes' => [
                ['time' => '0-3s', 'visual' => 'Stressed business owner at desk', 'audio' => 'Upbeat music starts'],
                ['time' => '3-10s', 'visual' => 'Software dashboard automating tasks', 'audio' => 'Meet Sarah, she saved 15 hours this week'],
                ['time' => '10-20s', 'visual' => 'Happy team collaborating', 'audio' => 'Focus on growth, not paperwork'],
                ['time' => '20-30s', 'visual' => 'Logo + "Start Free Trial" button', 'audio' => 'Click below, start in 60 seconds'],
            ],
            'music_suggestion' => 'Upbeat, energetic',
            'text_overlays' => ['Save 15+ hrs/week', 'AI-Powered', 'Free Trial'],
            'hashtags' => ['#BusinessAutomation', '#Productivity', '#AI'],
        ]);
    }

    protected function parseResponse(string $response, array $input): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to parse AI response: ' . $e->getMessage());
        }

        return $this->parseResponse($this->mockResponse(), $input);
    }

    protected function analyzeTitleStructure(string $title): string
    {
        // Simple analysis: count words, detect patterns
        $words = str_word_count($title);
        $hasNumbers = (bool) preg_match('/\d/', $title);
        $hasQuestion = str_ends_with(trim($title), '?');
        
        return "{$words} words" . ($hasNumbers ? ' + numbers' : '') . ($hasQuestion ? ' + question' : '');
    }

    protected function calculateEngagementRate($advert): float
    {
        $views = (float) ($advert->views_count ?? 0);
        $interactions = (float) (($advert->likes_count ?? 0) + ($advert->comments_count ?? 0) + ($advert->shares_count ?? 0));
        return $views > 0 ? round($interactions / $views * 100, 2) : 0.0;
    }

    protected function learnFromGeneration(array $input, array $script): void
    {
        $history = Cache::get('video_ad_agent.generation_history', []);
        $history[] = [
            'timestamp' => now()->toISOString(),
            'input' => $input,
            'output' => $script,
        ];
        
        // Keep last 100 generations
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }
        
        Cache::put('video_ad_agent.generation_history', $history, now()->addDays(90));
    }
}