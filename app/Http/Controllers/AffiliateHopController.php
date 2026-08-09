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

        if (!$destination) {
            abort(404, 'Offer destination missing');
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
        Cookie::queue(cookie(
            'wwa_aff',
            $application->tracking_code,
            $days * 24 * 60,
            '/',
            null,
            false,
            false
        ));

        $separator = str_contains($destination, '?') ? '&' : '?';
        $target = $destination . $separator . http_build_query([
            'aff' => $application->tracking_code,
            'wwa_offer' => $offer->id,
        ]);

        return redirect()->away($target);
    }
}
