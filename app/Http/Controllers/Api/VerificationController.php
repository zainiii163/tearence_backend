<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\APIController;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class VerificationController extends APIController
{
    public function __construct(protected VerificationService $verification)
    {
    }

    public function sendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->verification->sendEmailOtp($request->email);
            return $this->successResponse($result, 'Verification code sent to your email.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to send verification email.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'code' => 'required|string|min:4|max:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $verified = $this->verification->verifyEmailOtp($request->email, $request->code);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'status' => 'Error',
                'message' => 'Invalid or expired verification code.',
                'data' => null,
            ], Response::HTTP_BAD_REQUEST);
        }

        // Update customer email_verified_at and business status
        $customer = Customer::where('email', strtolower(trim($request->email)))->first();
        if ($customer) {
            $customer->email_verified_at = now();
            $customer->save();

            // Activate associated business pages
            CustomerBusiness::where('customer_id', $customer->customer_id)
                ->where('status', 'pending')
                ->update(['status' => 'active']);
        }

        return response()->json([
            'success' => true,
            'status' => 'Success',
            'message' => 'Email verified successfully. Your business pages are now live.',
            'data' => ['verified' => true],
        ]);
    }

    /**
     * Check verification status for current user.
     * Returns whether email/phone are verified and business status.
     */
    public function verificationStatus(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        }

        $verification = app(VerificationService::class);
        $email = $user->email;
        $phone = $user->phone ?? null;

        $emailVerified = $verification->isEmailVerified($email) || ! empty($user->email_verified_at);
        $phoneVerified = $phone ? $verification->isPhoneVerified($phone) : false;

        $businesses = CustomerBusiness::where('customer_id', $user->customer_id)->get(['id', 'business_name', 'slug', 'status']);

        return $this->successResponse([
            'email_verified' => (bool) $emailVerified,
            'phone_verified' => (bool) $phoneVerified,
            'businesses' => $businesses->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->business_name,
                'slug' => $b->slug,
                'status' => $b->status,
                'is_live' => $b->status === 'active',
            ]),
            'can_post' => (bool) $emailVerified,
        ]);
    }

    public function sendPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:8|max:30',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->verification->sendPhoneOtp($request->phone, $request->country ?? '');
            return $this->successResponse($result, 'Verification code sent to your phone.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to send verification SMS.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:8|max:30',
            'code' => 'required|string|min:4|max:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $verified = $this->verification->verifyPhoneOtp($request->phone, $request->code);

        if (!$verified) {
            return response()->json([
                'success' => false,
                'status' => 'Error',
                'message' => 'Invalid or expired verification code.',
                'data' => null,
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'success' => true,
            'status' => 'Success',
            'message' => 'Phone verified successfully.',
            'data' => ['verified' => true],
        ]);
    }

    public function checkCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_registration_number' => 'required|string|min:2|max:50',
            'vat_number' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $result = $this->verification->checkCompany(
            $request->company_registration_number,
            $request->vat_number,
            $request->country ?? ''
        );

        return $this->successResponse($result, $result['message'] ?? 'Company check complete.');
    }
}
