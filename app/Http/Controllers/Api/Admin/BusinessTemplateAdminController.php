<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessTemplate;
use App\Models\TemplatePurchase;
use App\Models\TemplateSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class BusinessTemplateAdminController extends Controller
{
    public function stats(): JsonResponse
    {
        if (!Schema::hasTable('business_templates')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => 0,
                    'active' => 0,
                    'premium' => 0,
                    'catalog' => 0,
                    'seller_listings' => 0,
                    'purchases' => 0,
                    'revenue' => 0,
                    'premium_monthly_fee' => TemplateSetting::premiumMonthlyFee(),
                    'premium_duration_days' => TemplateSetting::premiumDurationDays(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => BusinessTemplate::count(),
                'active' => BusinessTemplate::where('status', 'active')->count(),
                'premium' => BusinessTemplate::query()->premiumActive()->count(),
                'catalog' => BusinessTemplate::where('is_catalog', true)->count(),
                'seller_listings' => BusinessTemplate::where('is_catalog', false)->count(),
                'purchases' => Schema::hasTable('template_purchases')
                    ? TemplatePurchase::where('payment_status', 'completed')->count()
                    : 0,
                'revenue' => Schema::hasTable('template_purchases')
                    ? (float) TemplatePurchase::where('payment_status', 'completed')->sum('platform_fee')
                    : 0,
                'premium_monthly_fee' => TemplateSetting::premiumMonthlyFee(),
                'premium_duration_days' => TemplateSetting::premiumDurationDays(),
            ],
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        if (!Schema::hasTable('template_purchases')) {
            return response()->json([
                'success' => true,
                'data' => ['data' => [], 'total' => 0],
            ]);
        }

        $query = TemplatePurchase::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('template_slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $items = $query->paginate(min((int) ($request->per_page ?? 20), 100));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable('business_templates')) {
            return response()->json([
                'success' => true,
                'data' => ['data' => [], 'total' => 0],
            ]);
        }

        $query = BusinessTemplate::query()->with('user:user_id,name,email,first_name,last_name');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('blurb', 'like', "%{$term}%");
            });
        }

        if ($request->filled('vertical')) {
            $query->where('vertical', $request->vertical);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_premium')) {
            if ($request->boolean('is_premium')) {
                $query->premiumActive();
            } else {
                $query->where(function ($q) {
                    $q->where('is_premium', false)
                        ->orWhereNull('premium_until')
                        ->orWhere('premium_until', '<', now());
                });
            }
        }

        if ($request->filled('is_catalog')) {
            $query->where('is_catalog', $request->boolean('is_catalog'));
        }

        $query->orderByDesc('is_premium')->orderByDesc('created_at');

        $items = $query->paginate(min((int) ($request->per_page ?? 20), 100));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $template = BusinessTemplate::with('user:user_id,name,email,first_name,last_name')->find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
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
            'sort_order' => 'nullable|integer|min:0',
            'is_catalog' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'premium_until' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (array_key_exists('is_premium', $data) && $data['is_premium'] && empty($data['premium_until'])) {
            if (!$template->premium_until || $template->premium_until->isPast()) {
                $data['premium_until'] = now()->addDays(TemplateSetting::premiumDurationDays());
            }
        }

        if (array_key_exists('is_premium', $data) && !$data['is_premium']) {
            $data['premium_until'] = null;
        }

        $template->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Template updated.',
            'data' => $template->fresh()->load('user:user_id,name,email,first_name,last_name'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted.',
        ]);
    }

    public function setPremium(Request $request, int $id): JsonResponse
    {
        $template = BusinessTemplate::find($id);

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $enable = $request->boolean('is_premium', true);
        $days = (int) ($request->input('days') ?: TemplateSetting::premiumDurationDays());

        if ($enable) {
            $template->applyPremium($days, TemplateSetting::premiumMonthlyFee());
        } else {
            $template->clearPremium();
        }

        return response()->json([
            'success' => true,
            'message' => $enable ? 'Template marked premium.' : 'Premium removed.',
            'data' => $template->fresh(),
        ]);
    }

    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TemplateSetting::publicSettings(),
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'premium_monthly_fee' => 'required|numeric|min:0|max:9999.99',
            'premium_duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        TemplateSetting::setValue(
            'premium_monthly_fee',
            number_format((float) $request->premium_monthly_fee, 2, '.', ''),
            'Premium listing fee (USD / month)'
        );

        if ($request->filled('premium_duration_days')) {
            TemplateSetting::setValue(
                'premium_duration_days',
                (string) (int) $request->premium_duration_days,
                'Premium listing duration (days)'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Template settings saved.',
            'data' => TemplateSetting::publicSettings(),
        ]);
    }

    /** List professional fill-in quote requests (business admins). */
    public function quotes(Request $request): JsonResponse
    {
        if (!Schema::hasTable('template_quote_requests')) {
            return response()->json(['success' => true, 'data' => ['data' => []]]);
        }

        $query = \App\Models\TemplateQuoteRequest::query()->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function updateQuote(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable('template_quote_requests')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 404);
        }

        $quote = \App\Models\TemplateQuoteRequest::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:new,contacted,quoted,closed',
            'admin_notes' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $quote->fill($validator->validated());
        $quote->save();

        return response()->json(['success' => true, 'data' => $quote]);
    }
}
