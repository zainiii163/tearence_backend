<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlatformFeeHelper;
use App\Http\Controllers\Controller;
use App\Models\BusinessTemplate;
use App\Models\TemplatePurchase;
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

            $query->orderBy('sort_order')->orderByDesc('created_at');

            $perPage = min((int) ($request->per_page ?? 12), 50);
            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('BusinessTemplate index failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load templates.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
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

            $exact = BusinessTemplate::query()
                ->active()
                ->where('vertical', $vertical)
                ->where('category_slug', $categorySlug)
                ->orderBy('sort_order')
                ->limit(12)
                ->get();

            $items = $exact->isNotEmpty()
                ? $exact
                : BusinessTemplate::query()
                    ->active()
                    ->where('vertical', $vertical)
                    ->where('category_slug', 'default')
                    ->orderBy('sort_order')
                    ->limit(12)
                    ->get();

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

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
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

        $template = BusinessTemplate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Template listed successfully.',
            'data' => $template,
        ], 201);
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
}
