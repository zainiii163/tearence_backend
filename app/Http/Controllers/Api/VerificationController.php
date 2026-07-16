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
            $message = !empty($result['mail_delivered'])
                ? 'Verification code sent to your email.'
                : 'Verification code ready. Check the on-screen code if email delivery is unavailable.';
            return $this->successResponse($result, $message);
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

        return response()->json([
            'success' => true,
            'status' => 'Success',
            'message' => 'Email verified successfully.',
            'data' => ['verified' => true],
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
