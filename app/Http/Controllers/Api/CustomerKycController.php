<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Customer-facing KYC (marketplace users). Signup is free; KYC after first post.
 */
class CustomerKycController extends Controller
{
    public function postCount(Request $request): JsonResponse
    {
        /** @var Customer $user */
        $user = auth()->user();
        $count = (int) ($user->posts_count ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'post_count' => $count,
                'kyc_required' => $count >= 1,
            ],
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        /** @var Customer $user */
        $user = auth()->user();
        $status = Schema::hasColumn('customer', 'kyc_status')
            ? ($user->kyc_status ?? 'not_verified')
            : 'disabled';

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'kyc_status' => $status,
                'verified_at' => $user->kyc_verified_at ?? null,
                'rejection_reason' => $user->kyc_rejection_reason ?? null,
                'post_count' => (int) ($user->posts_count ?? 0),
                'kyc_required' => ((int) ($user->posts_count ?? 0)) >= 1
                    && ! in_array($status, ['verified', 'disabled'], true),
            ],
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        if (! Schema::hasColumn('customer', 'kyc_status')) {
            return response()->json([
                'success' => false,
                'message' => 'KYC is not enabled on this environment yet.',
            ], 503);
        }

        $validated = $request->validate([
            'verification_type' => 'nullable|string|max:64',
            'id_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'proof_of_address' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'selfie' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        /** @var Customer $user */
        $user = auth()->user();
        $docs = is_array($user->kyc_documents) ? $user->kyc_documents : [];
        $docs['verification_type'] = $validated['verification_type'] ?? ($docs['verification_type'] ?? null);

        foreach (['id_front', 'id_back', 'passport', 'proof_of_address', 'selfie'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->storeAs(
                    'kyc_documents/'.$user->customer_id,
                    Str::uuid().'.'.$request->file($field)->getClientOriginalExtension(),
                    'public'
                );
                $docs[$field] = $path;
            }
        }

        $user->kyc_documents = $docs;
        $user->kyc_status = 'pending';
        $user->kyc_rejection_reason = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted for review.',
            'data' => [
                'status' => 'pending',
                'verification_type' => $docs['verification_type'] ?? null,
            ],
        ]);
    }
}
