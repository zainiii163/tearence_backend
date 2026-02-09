<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\CandidateProfile;
use App\Models\CandidateUpsell;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class CandidateUpsellController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get candidate upsells for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, we need to find the corresponding customer
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user->user_id;
            
            // Get candidate profiles for this customer
            $candidateProfiles = CandidateProfile::where('customer_id', $customer_id)->pluck('candidate_profile_id');
            
            // Get upsells for these profiles
            $upsells = CandidateUpsell::whereIn('candidate_profile_id', $candidateProfiles)
                ->with('candidateProfile')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($upsells, 'Candidate upsells retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a candidate upsell (featured profile or job alerts boost).
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        // For User model, we need to find the corresponding customer
        $customer = \App\Models\Customer::where('email', $user->email)->first();
        $customer_id = $customer ? $customer->customer_id : $user->user_id;

        $validator = Validator::make($request->all(), [
            'candidate_profile_id' => 'required|integer|exists:candidate_profiles,candidate_profile_id',
            'upsell_type' => 'required|in:featured_profile,job_alerts_boost',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Verify profile ownership
            $profile = CandidateProfile::where('candidate_profile_id', $request->candidate_profile_id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            // Check if active upsell already exists
            $existingUpsell = CandidateUpsell::where('candidate_profile_id', $request->candidate_profile_id)
                ->where('upsell_type', $request->upsell_type)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($existingUpsell) {
                return $this->errorResponse('An active upsell of this type already exists for this profile', Response::HTTP_BAD_REQUEST);
            }

            // Calculate price based on upsell type and duration
            $price = $this->calculatePrice($request->upsell_type, $request->duration_days);

            $upsell = new CandidateUpsell();
            $upsell->candidate_profile_id = $request->candidate_profile_id;
            $upsell->upsell_type = $request->upsell_type;
            $upsell->price = $price;
            $upsell->duration_days = $request->duration_days;
            $upsell->status = 'pending';
            $upsell->payment_status = 'pending';
            $upsell->save();

            DB::commit();

            // Return payment URL for PayPal
            $paymentUrl = $this->createPayPalPayment($upsell);

            return $this->successResponse([
                'upsell' => $upsell,
                'payment_url' => $paymentUrl,
            ], 'Upsell created successfully. Please complete payment.', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete payment for an upsell.
     */
    public function completePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_transaction_id' => 'required|string',
            'payment_method' => 'required|in:paypal,stripe',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $upsell = CandidateUpsell::findOrFail($id);
            
            // Verify ownership
            $user = auth('api')->user();
            // For User model, we need to find the corresponding customer
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user->user_id;
            
            $profile = CandidateProfile::where('candidate_profile_id', $upsell->candidate_profile_id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            // Update upsell
            $upsell->payment_transaction_id = $request->payment_transaction_id;
            $upsell->payment_status = 'completed';
            $upsell->payment_details = [
                'payment_method' => $request->payment_method,
                'paid_at' => now()->toDateTimeString(),
            ];
            $upsell->activate();
            $upsell->save();

            // Update profile flags
            if ($upsell->upsell_type === 'featured_profile') {
                $profile->is_featured = true;
                $profile->featured_expires_at = $upsell->expires_at;
            } elseif ($upsell->upsell_type === 'job_alerts_boost') {
                $profile->has_job_alerts_boost = true;
                $profile->job_alerts_boost_expires_at = $upsell->expires_at;
            }
            $profile->save();

            // Track revenue
            $revenue = new RevenueTracking();
            $revenue->revenue_type = 'candidate_upsell';
            $revenue->related_id = $upsell->candidate_upsell_id;
            $revenue->customer_id = $customer_id;
            $revenue->upsell_type = $upsell->upsell_type;
            $revenue->amount = $upsell->price;
            $revenue->currency = 'USD';
            $revenue->payment_method = $request->payment_method;
            $revenue->payment_transaction_id = $request->payment_transaction_id;
            $revenue->payment_status = 'completed';
            $revenue->payment_date = now();
            $revenue->save();

            DB::commit();

            return $this->successResponse($upsell->load('candidateProfile'), 'Payment completed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get upsells for a candidate profile.
     */
    public function getByProfile($profileId)
    {
        try {
            $upsells = CandidateUpsell::where('candidate_profile_id', $profileId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($upsells, 'Upsells retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Calculate price based on upsell type and duration.
     */
    private function calculatePrice(string $type, int $days): float
    {
        // Base prices per day
        $basePrices = [
            'featured_profile' => 4.00,      // $4 per day for featured profile
            'job_alerts_boost' => 2.00,      // $2 per day for job alerts boost
        ];

        $basePrice = $basePrices[$type] ?? 4.00;
        
        // Apply discount for longer durations
        $discount = 0;
        if ($days >= 90) {
            $discount = 0.20; // 20% discount for 90+ days
        } elseif ($days >= 30) {
            $discount = 0.10; // 10% discount for 30+ days
        }

        $totalPrice = $basePrice * $days;
        $discountedPrice = $totalPrice * (1 - $discount);

        return round($discountedPrice, 2);
    }

    /**
     * Create PayPal payment.
     */
    private function createPayPalPayment(CandidateUpsell $upsell): ?string
    {
        try {
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('api.v1.candidate-upsell.payment.success', ['id' => $upsell->candidate_upsell_id]),
                    "cancel_url" => route('api.v1.candidate-upsell.payment.cancel', ['id' => $upsell->candidate_upsell_id]),
                ],
                "purchase_units" => [
                    0 => [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => (string) $upsell->price
                        ],
                        "description" => ucfirst(str_replace('_', ' ', $upsell->upsell_type)) . " for {$upsell->duration_days} days"
                    ]
                ]
            ]);

            if (isset($response['id']) && $response['id'] != null) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] == 'approve') {
                        return $link['href'];
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

