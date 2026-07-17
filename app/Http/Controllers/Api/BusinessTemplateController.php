<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BusinessTemplateController extends Controller
{
    /**
     * Public list — filter by vertical + category_slug.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BusinessTemplate::query()->active();

        if ($request->filled('vertical')) {
            $query->where('vertical', $request->vertical);
        }

        if ($request->filled('category_slug')) {
            $slug = $request->category_slug;
            // Prefer exact category packs; include default only when no exact rows exist
            $exact = (clone $query)->where('category_slug', $slug);
            if ($exact->exists()) {
                $query->where('category_slug', $slug);
            } else {
                $query->where('category_slug', 'default');
            }
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
    }

    /**
     * Category-page strip: returns section meta + up to 3 template packs.
     */
    public function browse(Request $request): JsonResponse
    {
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

        $vertical = $request->vertical;
        $categorySlug = $request->category_slug ?: 'default';

        $base = BusinessTemplate::query()->active()->where('vertical', $vertical);

        $exact = (clone $base)->where('category_slug', $categorySlug)->orderBy('sort_order')->limit(6)->get();
        $items = $exact->isNotEmpty()
            ? $exact
            : (clone $base)->where('category_slug', 'default')->orderBy('sort_order')->limit(6)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'headline' => null,
                    'description' => null,
                    'items' => [],
                ],
            ]);
        }

        $first = $items->first();

        return response()->json([
            'success' => true,
            'data' => [
                'headline' => $first->headline,
                'description' => $first->section_description,
                'category_slug' => $items->first()->category_slug,
                'vertical' => $vertical,
                'items' => $items->take(3)->map(fn (BusinessTemplate $t) => [
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
}
