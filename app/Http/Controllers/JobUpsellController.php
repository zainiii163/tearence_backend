<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\JobUpsell;
use App\Models\Listing;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class JobUpsellController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all job upsells for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            $query = JobUpsell::with(['listing'])
                ->whereHas('listing', function($q) use ($customer_id) {
                    $q->where('customer_id', $customer_id);
                });

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by upsell type if provided
            if ($request->has('upsell_type')) {
                $query->where('upsell_type', $request->upsell_type);
            }

            $upsells = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($upsells, 'Job upsells retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a job upsell (featured or suggested).
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;

        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|integer|exists:listing,listing_id',
            'upsell_type' => 'required|in:featured,suggested',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Verify listing ownership
            $listing = Listing::where('listing_id', $request->listing_id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            // Check if active upsell already exists
            $existingUpsell = JobUpsell::where('listing_id', $request->listing_id)
                ->where('upsell_type', $request->upsell_type)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($existingUpsell) {
                return $this->errorResponse('An active upsell of this type already exists for this listing', Response::HTTP_BAD_REQUEST);
            }

            // Calculate price based on upsell type and duration
            $price = $this->calculatePrice($request->upsell_type, $request->duration_days);

            $upsell = new JobUpsell();
            $upsell->listing_id = $request->listing_id;
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

            $upsell = JobUpsell::findOrFail($id);
            
            // Verify ownership
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;
            $listing = Listing::where('listing_id', $upsell->listing_id)
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

            // Update listing flags
            if ($upsell->upsell_type === 'featured') {
                $listing->is_featured = true;
                $listing->featured_expires_at = $upsell->expires_at;
            } elseif ($upsell->upsell_type === 'suggested') {
                $listing->is_suggested = true;
                $listing->suggested_expires_at = $upsell->expires_at;
            }
            $listing->save();

            // Track revenue
            $revenue = new RevenueTracking();
            $revenue->revenue_type = 'job_upsell';
            $revenue->related_id = $upsell->job_upsell_id;
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

            return $this->successResponse($upsell->load('listing'), 'Payment completed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get upsells for a listing.
     */
    public function getByListing($listingId)
    {
        try {
            $upsells = JobUpsell::where('listing_id', $listingId)
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
            'featured' => 5.00,  // $5 per day for featured
            'suggested' => 3.00, // $3 per day for suggested
        ];

        $basePrice = $basePrices[$type] ?? 5.00;
        
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
    private function createPayPalPayment(JobUpsell $upsell): ?string
    {
        try {
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('api.v1.job-upsell.payment.success', ['id' => $upsell->job_upsell_id]),
                    "cancel_url" => route('api.v1.job-upsell.payment.cancel', ['id' => $upsell->job_upsell_id]),
                ],
                "purchase_units" => [
                    0 => [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => (string) $upsell->price
                        ],
                        "description" => ucfirst($upsell->upsell_type) . " job upsell for {$upsell->duration_days} days"
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

