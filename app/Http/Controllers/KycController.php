<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get current user's KYC status
     */
    public function status()
    {
        $user = Auth::user();
        
        return response()->json([
            'status' => 'success',
            'message' => 'KYC status retrieved successfully',
            'data' => [
                'kyc_status' => $user->kyc_status,
                'kyc_documents' => $user->kyc_documents,
                'kyc_verified_at' => $user->kyc_verified_at,
                'kyc_rejection_reason' => $user->kyc_rejection_reason,
                'is_verified' => $user->isKycVerified(),
                'can_access_website' => $user->canAccessWebsite(),
            ]
        ]);
    }

    /**
     * Submit KYC documents for verification
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        // Check if user can submit KYC (not already verified or submitted)
        if (in_array($user->kyc_status, ['verified', 'submitted'])) {
            return response()->json([
                'message' => 'KYC documents have already been submitted or verified'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'document_types' => 'required|array|min:1',
            'document_types.*' => 'required|string|in:id_card,passport,utility_bill,bank_statement,selfie',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            $documents = [];
            
            foreach ($request->file('documents') as $key => $file) {
                $documentType = $request->input('document_types')[$key] ?? 'id_card';
                $filename = time() . '_' . $documentType . '_' . $file->getClientOriginalName();
                
                // Store file
                $path = $file->storeAs('kyc_documents', $filename, 'public');
                
                $documents[] = [
                    'type' => $documentType,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toISOString(),
                ];
            }

            // Submit KYC
            $user->submitKyc($documents);

            return response()->json([
                'status' => 'success',
                'message' => 'KYC documents submitted successfully. Please wait for verification.',
                'data' => [
                    'kyc_status' => 'submitted',
                    'documents' => $documents,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit KYC documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all pending KYC submissions
     */
    public function pending(Request $request)
    {
        // Check if user has permission to manage users
        if (!Auth::user()->canManageUsers()) {
            return response()->json([
                'message' => 'You do not have permission to manage KYC verifications'
            ], 403);
        }

        $pendingUsers = User::where('kyc_status', 'pending')
            ->orWhere('kyc_status', 'submitted')
            ->with(['customer'])
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Pending KYC submissions retrieved successfully',
            'data' => $pendingUsers
        ]);
    }

    /**
     * Admin: Approve KYC verification
     */
    public function approve(Request $request, $userId)
    {
        // Check if user has permission to manage users
        if (!Auth::user()->canManageUsers()) {
            return response()->json([
                'message' => 'You do not have permission to approve KYC verifications'
            ], 403);
        }

        $user = User::findOrFail($userId);
        
        if ($user->kyc_status === 'verified') {
            return response()->json([
                'message' => 'User KYC is already verified'
            ], 400);
        }

        $user->approveKyc();

        return response()->json([
            'status' => 'success',
            'message' => 'KYC verification approved successfully',
            'data' => [
                'user_id' => $user->user_id,
                'kyc_status' => 'verified',
                'kyc_verified_at' => $user->kyc_verified_at,
            ]
        ]);
    }

    /**
     * Admin: Reject KYC verification
     */
    public function reject(Request $request, $userId)
    {
        // Check if user has permission to manage users
        if (!Auth::user()->canManageUsers()) {
            return response()->json([
                'message' => 'You do not have permission to reject KYC verifications'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($userId);
        
        if ($user->kyc_status === 'verified') {
            return response()->json([
                'message' => 'Cannot reject already verified KYC'
            ], 400);
        }

        $user->rejectKyc($request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'KYC verification rejected successfully',
            'data' => [
                'user_id' => $user->user_id,
                'kyc_status' => 'rejected',
                'kyc_rejection_reason' => $user->kyc_rejection_reason,
            ]
        ]);
    }

    /**
     * Get KYC statistics (admin only)
     */
    public function statistics()
    {
        // Check if user has permission to view analytics
        if (!Auth::user()->canViewAnalytics()) {
            return response()->json([
                'message' => 'You do not have permission to view statistics'
            ], 403);
        }

        $stats = [
            'pending' => User::where('kyc_status', 'pending')->orWhere('kyc_status', 'submitted')->count(),
            'verified' => User::where('kyc_status', 'verified')->count(),
            'rejected' => User::where('kyc_status', 'rejected')->count(),
            'total_users' => User::count(),
            'verification_rate' => User::where('kyc_status', 'verified')->count() / max(User::count(), 1) * 100,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'KYC statistics retrieved successfully',
            'data' => $stats
        ]);
    }
}
