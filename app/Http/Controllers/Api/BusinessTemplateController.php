<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlatformFeeHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessTemplate;
use App\Models\TemplatePurchase;
use App\Models\TemplateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BusinessTemplateController extends Controller
{
    protected function hasPremiumColumns(): bool
    {
        return Schema::hasTable('business_templates')
            && Schema::hasColumn('business_templates', 'is_premium')
            && Schema::hasColumn('business_templates', 'premium_until');
    }

    protected function applyPremiumSort($query)
    {
        if ($this->hasPremiumColumns()) {
            $query->orderByRaw(
                'CASE WHEN is_premium = 1 AND (premium_until IS NULL OR premium_until > ?) THEN 0 ELSE 1 END',
                [now()]
            );
        }

        return $query;
    }

    /**
     * Public list — filter by vertical + category_slug.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if (!Schema::hasTable('business_templates')) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [],
                        'total' => 0,
                    ],
                    'message' => 'Run BusinessTemplateSeeder after migrate.',
                ]);
            }

            $query = BusinessTemplate::query()->active();

            if ($request->filled('vertical')) {
                $query->where('vertical', $request->vertical);
            }

            if ($request->filled('category_slug')) {
                $slug = $request->category_slug;
                $hasExact = BusinessTemplate::query()
                    ->active()
                    ->where('vertical', $request->vertical)
                    ->where('category_slug', $slug)
                    ->exists();

                $query->where('category_slug', $hasExact ? $slug : 'default');
            }

            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('blurb', 'like', "%{$term}%")
                        ->orWhere('template_type', 'like', "%{$term}%");
                });
            }

            if ($request->filled('template_type')) {
                $query->where('template_type', $request->template_type);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            if ($request->filled('is_premium') && $request->boolean('is_premium') && $this->hasPremiumColumns()) {
                $query->premiumActive();
            }

            $this->applyPremiumSort($query)
                ->orderBy('sort_order')
                ->orderByDesc('created_at');

            $perPage = min((int) ($request->per_page ?? 12), 50);
            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('BusinessTemplate index failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Soft-fail so the shop still falls back to static catalog packs
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'per_page' => (int) ($request->per_page ?? 12),
                ],
                'warning' => config('app.debug') ? $e->getMessage() : 'Catalog unavailable — using static packs.',
            ]);
        }
    }

    /**
     * Category-page strip: returns section meta + up to 3 template packs.
     */
    public function browse(Request $request): JsonResponse
    {
        $empty = [
            'headline' => null,
            'description' => null,
            'items' => [],
        ];

        try {
            $validator = Validator::make($request->all(), [
                'vertical' => 'required|string|max:50',
                'category_slug' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            if (!Schema::hasTable('business_templates')) {
                return response()->json([
                    'success' => true,
                    'data' => $empty,
                    'message' => 'Templates table missing — run migrate and BusinessTemplateSeeder.',
                ]);
            }

            $vertical = $request->vertical;
            $categorySlug = $request->category_slug ?: 'default';

            $exactQuery = BusinessTemplate::query()
                ->active()
                ->where('vertical', $vertical)
                ->where('category_slug', $categorySlug);
            $this->applyPremiumSort($exactQuery);
            $exact = $exactQuery->orderBy('sort_order')->limit(12)->get();

            if ($exact->isNotEmpty()) {
                $items = $exact;
            } else {
                $fallbackQuery = BusinessTemplate::query()
                    ->active()
                    ->where('vertical', $vertical)
                    ->where('category_slug', 'default');
                $this->applyPremiumSort($fallbackQuery);
                $items = $fallbackQuery->orderBy('sort_order')->limit(12)->get();
            }

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => $empty,
                ]);
            }

            $first = $items->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'headline' => $first->headline,
                    'description' => $first->section_description,
                    'category_slug' => $first->category_slug,
                    'vertical' => $vertical,
                    'items' => $items->take(8)->map(fn (BusinessTemplate $t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'slug' => $t->slug,
                        'blurb' => $t->blurb,
                        'price' => $t->display_price,
                        'price_amount' => (float) $t->price,
                        'currency' => $t->currency,
                        'template_type' => $t->template_type,
                        'preview_image' => $t->preview_image,
                        'file_url' => $t->file_url,
                        'is_premium' => (bool) ($t->is_premium_active ?? false),
                        'premium_until' => $t->premium_until ?? null,
                    ])->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('BusinessTemplate browse failed: '.$e->getMessage());

            // Soft-fail so frontend static packs still show
            return response()->json([
                'success' => true,
                'data' => $empty,
                'warning' => config('app.debug') ? $e->getMessage() : 'Catalog unavailable',
            ]);
        }
    }

    public function show(string $slug): JsonResponse
    {
        $template = BusinessTemplate::where('slug', $slug)->active()->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found.',
            ], 404);
        }

        $template->increment('views');

        return response()->json([
            'success' => true,
            'data' => $template->fresh(),
        ]);
    }

    public function myTemplates(Request $request): JsonResponse
    {
        $items = BusinessTemplate::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        if (Schema::hasTable('template_purchases') && $items->count() > 0) {
            $ids = $items->getCollection()->pluck('id')->all();
            $stats = TemplatePurchase::query()
                ->whereIn('business_template_id', $ids)
                ->where('payment_status', 'completed')
                ->selectRaw('business_template_id, COUNT(*) as sales_count, COALESCE(SUM(seller_amount),0) as sales_revenue')
                ->groupBy('business_template_id')
                ->get()
                ->keyBy('business_template_id');

            $items->getCollection()->transform(function ($row) use ($stats) {
                $s = $stats->get($row->id);
                $row->setAttribute('sales_count', (int) ($s->sales_count ?? 0));
                $row->setAttribute('sales_revenue', (float) ($s->sales_revenue ?? 0));
                return $row;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Seller sales of their template products (Clive — dashboard).
     */
    public function mySales(Request $request): JsonResponse
    {
        if (!Schema::hasTable('template_purchases')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $userId = Auth::id();
        $templateIds = BusinessTemplate::where('user_id', $userId)->pluck('id');

        $items = TemplatePurchase::query()
            ->whereIn('business_template_id', $templateIds)
            ->where('payment_status', 'completed')
            ->with(['template:id,title,slug,vertical,price'])
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $summary = [
            'orders' => TemplatePurchase::whereIn('business_template_id', $templateIds)
                ->where('payment_status', 'completed')
                ->count(),
            'revenue' => (float) TemplatePurchase::whereIn('business_template_id', $templateIds)
                ->where('payment_status', 'completed')
                ->sum('seller_amount'),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $items,
        ]);
    }

    public function myPurchases(Request $request): JsonResponse
    {
        if (!Schema::hasTable('template_purchases')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $items = TemplatePurchase::where('customer_id', Auth::id())
            ->where('payment_status', 'completed')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'blurb' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'vertical' => 'required|string|max:50',
            'category_slug' => 'nullable|string|max:100',
            'headline' => 'nullable|string|max:255',
            'section_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0|max:99999.99',
            'price_label' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'template_type' => 'nullable|string|max:50',
            'preview_image' => 'nullable|string|max:500',
            'file_url' => 'nullable|string|max:500',
            'status' => 'nullable|in:draft,active,paused,sold',
            'make_premium' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $makePremium = (bool) ($data['make_premium'] ?? false);
        unset($data['make_premium'], $data['payment_method']);

        $data['user_id'] = Auth::id();
        $data['category_slug'] = $data['category_slug'] ?? 'default';
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['status'] = $data['status'] ?? 'active';
        $data['is_catalog'] = false;
        $data['slug'] = BusinessTemplate::makeSlug(
            $data['title'],
            $data['vertical'],
            $data['category_slug']
        );

        if ($makePremium) {
            $data['is_premium'] = true;
            $data['premium_until'] = now()->addDays(TemplateSetting::premiumDurationDays());
            $data['premium_fee_paid'] = TemplateSetting::premiumMonthlyFee();
        }

        $template = BusinessTemplate::create($data);

        return response()->json([
            'success' => true,
            'message' => $makePremium
                ? 'Template listed as premium for '.TemplateSetting::premiumDurationDays().' days.'
                : 'Template listed successfully.',
            'data' => $template,
            'premium' => [
                'requested' => $makePremium,
                'fee' => $makePremium ? TemplateSetting::premiumMonthlyFee() : 0,
                'until' => $template->premium_until,
            ],
        ], 201);
    }

    /**
     * Public settings used by sell form (premium fee is admin-editable).
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TemplateSetting::publicSettings(),
        ]);
    }

    /**
     * Promote an existing listing to premium for one billing period.
     */
    public function promote(Request $request, int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        if ((int) $template->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $fee = TemplateSetting::premiumMonthlyFee();
        $template->applyPremium(TemplateSetting::premiumDurationDays(), $fee);

        return response()->json([
            'success' => true,
            'message' => "Premium activated for {$fee} USD / month.",
            'data' => $template->fresh(),
            'premium' => [
                'fee' => $fee,
                'until' => $template->fresh()->premium_until,
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        if ((int) $template->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|min:3|max:255',
            'blurb' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'vertical' => 'sometimes|string|max:50',
            'category_slug' => 'nullable|string|max:100',
            'headline' => 'nullable|string|max:255',
            'section_description' => 'nullable|string|max:500',
            'price' => 'sometimes|numeric|min:0|max:99999.99',
            'price_label' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'template_type' => 'nullable|string|max:50',
            'preview_image' => 'nullable|string|max:500',
            'file_url' => 'nullable|string|max:500',
            'status' => 'nullable|in:draft,active,paused,sold',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $template->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Template updated.',
            'data' => $template->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        if ($template->is_catalog || (int) $template->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted.',
        ]);
    }

    /**
     * Buy a template (catalog or seller listing). Platform fee applied.
     * Payment gateway hook: mark completed after Stripe/PayPal success — demo completes immediately.
     */
    public function purchase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'nullable|integer',
            'slug' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!Schema::hasTable('template_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations: template_purchases table missing.',
            ], 503);
        }

        $customerId = Auth::id();
        $template = null;

        if ($request->filled('template_id')) {
            $template = BusinessTemplate::active()->find($request->template_id);
        } elseif ($request->filled('slug')) {
            $template = BusinessTemplate::active()->where('slug', $request->slug)->first();
        }

        if (!$template && $request->filled('slug')) {
            // Allow purchasing static catalog files by slug/path until seeded
            $file = $request->input('file_url') ?: ('/templates/'.$request->slug.'.html');
            $title = $request->input('title') ?: Str::title(str_replace('-', ' ', $request->slug));
            $price = (float) ($request->input('price') ?? 19);
            $fee = PlatformFeeHelper::split($price);

            $purchase = TemplatePurchase::create([
                'customer_id' => $customerId,
                'business_template_id' => null,
                'template_slug' => $request->slug,
                'title' => $title,
                'file_url' => $file,
                'price_paid' => $price,
                'fee_percent' => $fee['fee_percent'],
                'platform_fee' => $fee['platform_fee'],
                'seller_amount' => $fee['seller_amount'],
                'payment_method' => $request->payment_method ?: 'platform',
                'payment_status' => 'pending',
            ]);
            $purchase->markCompleted($request->payment_method ?: 'platform');

            return response()->json([
                'success' => true,
                'message' => 'Template purchased successfully.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'download_token' => $purchase->download_token,
                    'download_url' => url('/api/v1/business-templates/download/'.$purchase->download_token),
                    'expires_at' => $purchase->download_token_expires_at,
                    'platform_fee' => $purchase->platform_fee,
                    'fee_percent' => $purchase->fee_percent,
                ],
            ], 201);
        }

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $existing = TemplatePurchase::where('customer_id', $customerId)
            ->where('business_template_id', $template->id)
            ->where('payment_status', 'completed')
            ->where(function ($q) {
                $q->whereNull('download_token_expires_at')
                    ->orWhere('download_token_expires_at', '>', now());
            })
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already purchased.',
                'data' => [
                    'purchase_id' => $existing->id,
                    'download_token' => $existing->download_token,
                    'download_url' => url('/api/v1/business-templates/download/'.$existing->download_token),
                    'expires_at' => $existing->download_token_expires_at,
                ],
            ]);
        }

        $price = (float) $template->price;
        $fee = PlatformFeeHelper::split($price);

        $purchase = TemplatePurchase::create([
            'customer_id' => $customerId,
            'business_template_id' => $template->id,
            'template_slug' => $template->slug,
            'title' => $template->title,
            'file_url' => $template->file_url,
            'price_paid' => $price,
            'fee_percent' => $fee['fee_percent'],
            'platform_fee' => $fee['platform_fee'],
            'seller_amount' => $fee['seller_amount'],
            'payment_method' => $request->payment_method ?: 'platform',
            'payment_status' => 'pending',
        ]);

        // Hook Stripe/PayPal here — for now complete so buyers can download immediately.
        $purchase->markCompleted($request->payment_method ?: 'platform');

        return response()->json([
            'success' => true,
            'message' => 'Template purchased successfully.',
            'data' => [
                'purchase_id' => $purchase->id,
                'download_token' => $purchase->download_token,
                'download_url' => url('/api/v1/business-templates/download/'.$purchase->download_token),
                'expires_at' => $purchase->download_token_expires_at,
                'platform_fee' => $purchase->platform_fee,
                'fee_percent' => $purchase->fee_percent,
            ],
        ], 201);
    }

    public function download(string $token): BinaryFileResponse|JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (!Schema::hasTable('template_purchases')) {
            return response()->json(['message' => 'Not available'], 404);
        }

        $purchase = TemplatePurchase::where('download_token', $token)->first();
        if (!$purchase || !$purchase->isDownloadValid()) {
            return response()->json(['message' => 'Invalid or expired download token'], 401);
        }

        $path = $purchase->file_url ?: optional($purchase->template)->file_url;
        if (!$path) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Absolute URL → redirect
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        $relative = ltrim(str_replace(['/templates/', 'templates/'], '', parse_url($path, PHP_URL_PATH) ?: $path), '/');
        $candidates = [
            public_path('templates/'.$relative),
            public_path(ltrim($path, '/')),
            storage_path('app/public/'.ltrim($path, '/')),
        ];

        foreach ($candidates as $file) {
            if (is_file($file)) {
                return response()->download($file, basename($file));
            }
        }

        // Fallback: open under /templates/
        return redirect()->away(url('/templates/'.basename($relative)));
    }

    /**
     * User requests a professional fill-in quote → business admins (Clive).
     */
    public function requestQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:60',
            'company' => 'nullable|string|max:190',
            'message' => 'required|string|max:5000',
            'template_title' => 'nullable|string|max:255',
            'template_slug' => 'nullable|string|max:255',
            'template_id' => 'nullable|integer',
            'file_url' => 'nullable|string|max:500',
            'vertical' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Schema::hasTable('template_quote_requests')) {
            return response()->json([
                'success' => false,
                'message' => 'Quote requests are not available yet. Please contact support.',
            ], 503);
        }

        $user = Auth::user();
        $data = $validator->validated();

        $quote = \App\Models\TemplateQuoteRequest::create([
            'user_id' => $user?->user_id ?? $user?->id ?? null,
            'template_title' => $data['template_title'] ?? null,
            'template_slug' => $data['template_slug'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'file_url' => $data['file_url'] ?? null,
            'vertical' => $data['vertical'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'],
            'status' => 'new',
        ]);

        try {
            $title = $quote->template_title ?: 'a template';
            \App\Models\AdminNotification::notifyAllAdmins(
                'template_quote',
                "New template fill-in quote: {$quote->name} — {$title}",
                [
                    'quote_id' => $quote->id,
                    'email' => $quote->email,
                    'vertical' => $quote->vertical,
                    'template_title' => $quote->template_title,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to notify admins of template quote', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Quote request sent to our business team.',
            'data' => ['id' => $quote->id],
        ], 201);
    }
}
