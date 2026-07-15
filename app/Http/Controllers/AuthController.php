<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use App\Http\Controllers\APIController;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Helpers\StringHelper;
use App\Helpers\OtpHelper;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\Category;
use App\Services\VerificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends APIController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', [
        //     'except' => [
        //         'register',
        //         'login',
        //         'forgot-password',
        //         'generate-otp',
        //         'validate-otp',
        //     ]
        // ]);
    }

    /**
     * @OA\Post(
     * path="/v1/auth/login",
     *   tags={"Auth"},
     *   summary="Login user",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"email": "string", "password": "string"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="access_token", type="string", format="string"),
     *                  @OA\Property(property="token_type", type="string", format="string"),
     *                  @OA\Property(property="expires_in", type="integer", format="integer"),
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   )
     *)
     **/
    public function login()
    {
        $credentials = request(['email', 'password']);
        $email = $credentials['email'];
        $password = $credentials['password'];
        // valid credential
        $validator = FacadesValidator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // send failed response if request is not valid
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // check user is exists or not by email
        $check_user = Customer::where('email', request()->email);
        if (!$check_user->exists()) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // check user password needsRehash or not
        $user = $check_user->first();
        if (Hash::needsRehash($user->password_hash)) {
            $old_password = sha1($password);
            if ($user->password_hash == $old_password) {
                // update password with new password using bcrypt
                $user->password_hash = bcrypt($password);
                $user->save();
            }
        }

        // get token from user email & password
        if (!$token = auth('api')->attempt(['email' => $email, 'password' => $password])) {
            return $this->errorResponse('There was a problem logging in. Check your email and password or create an account.', Response::HTTP_UNAUTHORIZED);
        }

        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];

        return $this->successResponse($response, 'Login success', Response::HTTP_OK);
    }

    /**
     * JWT-based login for frontend applications
     */
    public function webLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            // Attempt JWT authentication using the configured api guard
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials do not match our records.'
                ], 401);
            }

            $user = auth('api')->user();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => [
                        'id' => $user->customer_id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'user_type' => $user->user_type ?? 'basic',
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * JWT-based logout for frontend applications
     */
    public function webLogout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Check JWT authentication status for frontend
     */
    public function webCheck(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => true,
                    'authenticated' => false
                ]);
            }

            return response()->json([
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => $user->user_id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'needs_kyc' => ($user->kyc_status ?? 'pending') !== 'verified',
                    'kyc_verified' => ($user->kyc_status ?? '') === 'verified',
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Token expired'
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Token invalid'
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Token not provided'
            ]);
        } catch (\Throwable $e) {
            Log::error('webCheck failed: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'authenticated' => false,
                'message' => 'Auth check failed',
            ]);
        }
    }

    /**
     * @OA\Post(
     * path="/v1/auth/login-admin",
     *   tags={"Auth"},
     *   summary="Login admin",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"email": "string", "password": "string"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="access_token", type="string", format="string"),
     *                  @OA\Property(property="token_type", type="string", format="string"),
     *                  @OA\Property(property="expires_in", type="integer", format="integer"),
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Not Found",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   )
     *)
     **/
    public function loginAdmin()
    {
        $credentials = request(['email', 'password']);
        $password = $credentials['password'];
        // valid credential
        $validator = FacadesValidator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // send failed response if request is not valid
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), RESPONSE::HTTP_UNPROCESSABLE_ENTITY);
        }

        // check user is exists or not by email
        $check_user = User::where('email', request()->email);
        if (!$check_user->exists()) {
            return $this->errorResponse('Data not found.', RESPONSE::HTTP_NOT_FOUND);
        }

        // check user password needsRehash or not
        $user = $check_user->first();
        if (Hash::needsRehash($user->password)) {
            $old_password = sha1($password);
            if ($user->password == $old_password) {
                // update password with new password using bcrypt
                $user->password = bcrypt($password);
                $user->save();
            }
        }

        // get token from user email & password
        if (!$token = auth('admin')->attempt($validator->validated())) {
            return $this->errorResponse('There was a problem logging in. Check your email and password or create an account.', RESPONSE::HTTP_UNAUTHORIZED);
        }

        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];

        return $this->successResponse($response, 'Login success', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/auth/logout",
     *      tags={"Auth"},
     *      summary="Logout user",
     *      description="Logout user",
     *      security={
     *         {"bearerAuth": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function logout()
    {
        auth('api')->logout();
        return $this->successResponse('', 'User successfully signed out', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/auth/refresh",
     *      tags={"Auth"},
     *      summary="Refresh token user",
     *      description="Refresh token user",
     *      security={
     *         {"bearerAuth": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="access_token", type="string", format="string"),
     *                  @OA\Property(property="token_type", type="string", format="string"),
     *                  @OA\Property(property="expires_in", type="integer", format="integer"),
     *                  @OA\Property(property="user", type="object", ref="#/components/schemas/UserResource"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function refresh()
    {
        try {
            // Parse token from request - this works with expired tokens within refresh window
            $newToken = JWTAuth::parseToken()->refresh();
            
            // Get the authenticated user with the new token
            JWTAuth::setToken($newToken);
            $user = JWTAuth::toUser($newToken);
            
            $response = [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $user
            ];
            
            return $this->successResponse($response, 'Token refreshed successfully', Response::HTTP_OK);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->errorResponse('Token has expired and can no longer be refreshed', Response::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->errorResponse('Token is invalid', Response::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->errorResponse('Token error: ' . $e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->errorResponse('Token refresh failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/auth/user-profile",
     *      tags={"Auth"},
     *      summary="Get details of user",
     *      description="Returns details of user",
     *      security={
     *         {"bearerAuth": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function userProfile()
    {
        try {
            // Check if user is authenticated
            if (!auth('api')->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth('api')->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            $customer_id = $user->customer_id;
            $customer = Customer::where('customer_id', $customer_id)->first();

            if (!$customer) {
                return $this->errorResponse('Customer not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse($customer, '', Response::HTTP_OK);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->errorResponse('Token has expired', Response::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->errorResponse('Token is invalid', Response::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->errorResponse('Token error: ' . $e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving user profile: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ];

        return $this->successResponse($response, '', Response::HTTP_OK);
    }

    /**
     * Debug endpoint to test authentication status
     */
    public function debugAuth(Request $request)
    {
        $headers = $request->headers->all();
        $authHeader = $request->header('Authorization');
        
        return response()->json([
            'success' => true,
            'message' => 'Debug information',
            'headers' => $headers,
            'auth_header' => $authHeader,
            'has_token' => !empty($authHeader),
            'token_preview' => $authHeader ? substr($authHeader, 0, 50) . '...' : null,
            'jwt_config' => [
                'secret_set' => !empty(config('jwt.secret')),
                'algo' => config('jwt.algo'),
                'ttl' => config('jwt.ttl'),
                'refresh_ttl' => config('jwt.refresh_ttl'),
            ]
        ]);
    }

    /**
     * @OA\Post(
     * path="/v1/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 ),
     *                 example={"first_name": "Will", "last_name": "Smith", "email": "will.smith@mail.com", "password": "Password.123", "password_confirmation": "Password.123"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="role",
     *                     type="string"
     *                 ),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *     )
     * )
     */
    public function register()
    {
        $userType = request()->input('user_type', 'basic');
        $isBusiness = $userType === 'business';

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:customer',
            'password' => 'required|min:8|same:password_confirmation',
            'password_confirmation' => 'required',
            'referral_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'user_type' => 'nullable|in:basic,business',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ];

        if ($isBusiness) {
            $rules['business_name'] = 'required|string|max:255';
            $rules['business_category'] = 'required|string|max:100';
            $rules['company_registration_number'] = 'required|string|max:50';
            $rules['phone'] = 'required|string|max:30';
            $rules['city'] = 'required|string|max:100';
            $rules['country'] = 'required|string|max:100';
            $rules['vat_number'] = 'nullable|string|max:50';
        }

        $validator = FacadesValidator::make(request()->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $verification = app(VerificationService::class);
        $email = strtolower(trim(request()->email));
        $phone = request()->phone;

        if ($phone || $isBusiness) {
            if (!$verification->isEmailVerified($email)) {
                return $this->errorResponse('Please verify your email address before registering.', Response::HTTP_BAD_REQUEST);
            }
            if ($phone && !$verification->isPhoneVerified($phone)) {
                return $this->errorResponse('Please verify your phone number before registering.', Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            DB::beginTransaction();
            $ip = request()->ip();
            $currentUserInfo = Location::get($ip);
            $regionName = "";
            $cityName = "";
            $countryName = "";
            $zipCode = "";
            $latitude = "";
            $longitude = "";
            if ($currentUserInfo) {
                $regionName = $currentUserInfo->regionName;
                $cityName = $currentUserInfo->cityName;
                $countryName = $currentUserInfo->countryName;
                $zipCode = $currentUserInfo->zipCode;
                $latitude = $currentUserInfo->latitude;
                $longitude = $currentUserInfo->longitude;
            }

            $customer = new Customer();
            $customer->customer_uid = Str::random(10);
            $customer->first_name = request()->first_name;
            $customer->last_name = request()->last_name;
            $customer->affiliate_id = "";
            $customer->affiliated_members = 0;
            $customer->email = $email;
            $customer->password_hash = bcrypt(request()->password);
            $customer->ip_address = $ip;
            $customer->ip_location = $regionName . ', ' . $cityName . ', ' . $countryName . '-' . $zipCode;
            $customer->ip_latlng = $latitude . ',' . $longitude;

            if (Schema::hasColumn('customer', 'user_type')) {
                $customer->user_type = $isBusiness ? 'business' : 'basic';
            }
            if ($phone && Schema::hasColumn('customer', 'phone')) {
                $customer->phone = $phone;
            }
            if (request()->country && Schema::hasColumn('customer', 'country')) {
                $customer->country = request()->country;
            }
            if (request()->city && Schema::hasColumn('customer', 'city')) {
                $customer->city = request()->city;
            }
            if ($isBusiness && Schema::hasColumn('customer', 'business_category')) {
                $customer->business_category = request()->business_category;
            }

            if ($verification->isEmailVerified($email)) {
                $customer->email_verified_at = now();
            }
            if ($phone && $verification->isPhoneVerified($phone) && Schema::hasColumn('customer', 'phone_verified_at')) {
                $customer->phone_verified_at = now();
            }

            $customer->save();

            if ($isBusiness) {
                $businessName = request()->business_name;
                $slug = Str::slug($businessName);
                $baseSlug = $slug;
                $counter = 1;
                while (CustomerBusiness::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $categoryId = null;
                $categorySlug = request()->business_category;
                if ($categorySlug) {
                    $category = Category::where('slug', $categorySlug)->first();
                    $categoryId = $category?->category_id;
                }

                $address = trim(request()->city . ', ' . request()->country);

                CustomerBusiness::create([
                    'customer_id' => $customer->customer_id,
                    'slug' => $slug,
                    'business_name' => $businessName,
                    'business_phone_number' => $phone ?? '',
                    'business_address' => $address,
                    'business_email' => $email,
                    'business_owner' => request()->first_name . ' ' . request()->last_name,
                    'business_company_registration' => request()->company_registration_number,
                    'business_company_no' => request()->company_registration_number,
                    'business_company_name' => $businessName,
                    'personal_email' => $email,
                    'personal_phone_number' => $phone,
                    'category_id' => $categoryId,
                    'business_category_slug' => $categorySlug,
                    'vat_number' => request()->vat_number,
                    'status' => 'active',
                ]);
            }

            $userReferral = ReferralService::processRegistrationReferral($customer, request()->referral_code);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $responseData = [
            'customer' => $customer,
            'user_type' => $isBusiness ? 'business' : 'basic',
        ];

        if ($isBusiness) {
            $responseData['business'] = CustomerBusiness::where('customer_id', $customer->customer_id)->first();
        }

        if ($userReferral) {
            $responseData['referral'] = [
                'welcome_discount' => $userReferral->getReferredDiscountInfo(),
                'referrer_name' => $userReferral->referrerUser->name ?? 'A friend',
            ];
        }

        return $this->successResponse($responseData, 'Register success', Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     * path="/v1/auth/forgot-password",
     *   tags={"Auth"},
     *   summary="Forgot password user",
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 example={"email": "string"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   )
     *)
     **/
    public function forgotPassword(Request $request)
    {
        $input = $request->only('email');
        $validator = FacadesValidator::make($input, [
            'email' => "required|email"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), RESPONSE::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Customer::where('email', $input['email'])->first();

        if ($user == null) {
            Log::warning("User not found. email: " . $input['email']);
            return $this->errorResponse('Data not found.', RESPONSE::HTTP_NOT_FOUND);
        }

        $helper = new StringHelper;
        $gen_pass = $helper->generateRandomString(10);
        $user->update(['password_hash' => bcrypt($gen_pass)]);

        // send email to the user
        MailHelper::sendForgotPasswordEmail($user, $gen_pass);

        return $this->successResponse($user, 'Reset password mail send successfully', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/auth/reset-password",
     *   tags={"Auth"},
     *   summary="Reset password user",
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 ),
     *                 example={"email": "string", "password": "string", "password_confirmation": "string"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   )
     *)
     **/
    public function resetPassword(Request $request)
    {
        $input = $request->only('email', 'password', 'password_confirmation');
        $validator = FacadesValidator::make($input, [
            'email' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // validate email
        $user = Customer::where('email', '=', $input['email'])->first();
        if ($user != NULL) {
            // change password
            $user->password_hash = bcrypt($request->password);
            $user->updated_at = Carbon::now()->timestamp;
            $user->save();
        } else {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($user, 'Change password success', Response::HTTP_OK);
    }
}
