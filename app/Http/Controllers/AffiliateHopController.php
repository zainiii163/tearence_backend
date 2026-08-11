<?php

namespace App\Http\Controllers;

use App\Models\AffiliateApplication;
use App\Models\AffiliateHopClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Public hop redirect — ClickBank-style unique promoter links.
 * GET /go/aff/{code} → log click → set cookie → redirect to merchant offer URL.
 */
class AffiliateHopController extends Controller
{
    public function go(Request $request, string $code)
    {
        $application = AffiliateApplication::query()
            ->with('businessAffiliateOffer')
            ->where('tracking_code', $code)
            ->where('status', 'approved')
            ->first();

        if (!$application || !$application->businessAffiliateOffer) {
            abort(404, 'Affiliate link not found');
        }

        $offer = $application->businessAffiliateOffer;
        $destination = $offer->tracking_link ?: $offer->website_url;

        if (!$destination || !$this->isSafeHttpUrl($destination)) {
            abort(404, 'Offer destination missing or invalid');
        }

        AffiliateHopClick::create([
            'affiliate_application_id' => $application->id,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'referer' => substr((string) $request->headers->get('referer'), 0, 512),
        ]);
        $application->increment('clicks_count');
        $offer->incrementClicks();

        $days = max(1, (int) ($offer->cookie_duration ?: 30));
        $secure = $request->isSecure() || app()->environment('production');
        Cookie::queue(cookie(
            'wwa_aff',
            $application->tracking_code,
            $days * 24 * 60,
            '/',
            null,
            $secure,
            true, // httpOnly
            false,
            'Lax'
        ));

        $separator = str_contains($destination, '?') ? '&' : '?';
        $target = $destination . $separator . http_build_query([
            'aff' => $application->tracking_code,
            'wwa_offer' => $offer->id,
        ]);

        if (!$this->isSafeHttpUrl($target)) {
            abort(404, 'Offer destination missing or invalid');
        }

        return redirect()->away($target);
    }

    /**
     * Only allow http(s) absolute URLs — blocks javascript:, data:, etc.
     */
    private function isSafeHttpUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 2048) {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        return in_array($scheme, ['http', 'https'], true);
    }
}
