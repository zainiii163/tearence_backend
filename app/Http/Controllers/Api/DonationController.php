<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Models\Donation;
use App\Models\DonationContribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    use VerifiesClientPayments;

    private function authUserId(): ?int
    {
        return auth('api')->id();
    }

    public function index(Request $request)
    {
        $query = Donation::query()->where('is_active', true)->where('status', 'active');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $sort = $request->sort ?? 'latest';
        switch ($sort) {
            case 'urgent':
                $query->where('is_urgent', true)->latest('published_at');
                break;
            case 'featured':
                $query->where('is_featured', true)->latest('published_at');
                break;
            case 'goal_low':
                $query->orderBy('goal_amount', 'asc');
                break;
            case 'goal_high':
                $query->orderBy('goal_amount', 'desc');
                break;
            default:
                $query->latest('published_at');
        }

        $donations = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    public function featured()
    {
        $donations = Donation::active()
            ->featured()
            ->latest('published_at')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    public function urgent()
    {
        $donations = Donation::active()
            ->urgent()
            ->latest('published_at')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    public function show($id)
    {
        $donation = Donation::findOrFail($id);
        
        $donation->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $donation
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'story' => 'nullable|string',
            'category' => 'required|string|max:255',
            'organizer_name' => 'required|string|max:255',
            'organizer_email' => 'required|email|max:255',
            'organizer_phone' => 'nullable|string|max:50',
            'goal_amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
            'deadline' => 'nullable|date|after:now',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'cover_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url',
            'beneficiaries' => 'nullable|array',
            'use_of_funds' => 'nullable|string',
            'milestones' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $this->authUserId();
        if (!$data['user_id']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data['is_active'] = true;
        $data['is_verified'] = false;
        $data['status'] = 'active';
        $data['published_at'] = now();

        // Generate slug from title
        $data['slug'] = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']));
        $data['slug'] = preg_replace('/-+/', '-', $data['slug']);
        $data['slug'] = trim($data['slug'], '-');

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('donations/covers', 'public');
        }

        // Handle additional images
        $additionalImagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $additionalImagesPaths[] = $image->store('donations/images', 'public');
            }
        }
        if (!empty($additionalImagesPaths)) {
            $data['images'] = $additionalImagesPaths;
        }

        // JSON/array fields — model casts handle serialization
        if (isset($data['beneficiaries']) && is_array($data['beneficiaries'])) {
            $data['beneficiaries'] = array_values($data['beneficiaries']);
        }
        if (isset($data['milestones']) && is_array($data['milestones'])) {
            $data['milestones'] = array_values($data['milestones']);
        }

        $donation = Donation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Donation campaign created successfully',
            'data' => $donation
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $donation = Donation::findOrFail($id);

        if ($donation->user_id != $this->authUserId()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|min:50',
            'story' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_email' => 'nullable|email|max:255',
            'organizer_phone' => 'nullable|string|max:50',
            'goal_amount' => 'nullable|numeric|min:1',
            'currency' => 'nullable|string|max:3',
            'deadline' => 'nullable|date|after:now',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'cover_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_url' => 'nullable|url',
            'beneficiaries' => 'nullable|array',
            'use_of_funds' => 'nullable|string',
            'milestones' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($donation->cover_image) {
                Storage::disk('public')->delete($donation->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('donations/covers', 'public');
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            if ($donation->images) {
                foreach ($donation->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            $additionalImagesPaths = [];
            foreach ($request->file('images') as $image) {
                $additionalImagesPaths[] = $image->store('donations/images', 'public');
            }
            $data['images'] = $additionalImagesPaths;
        }

        if (isset($data['beneficiaries']) && is_array($data['beneficiaries'])) {
            $data['beneficiaries'] = array_values($data['beneficiaries']);
        }
        if (isset($data['milestones']) && is_array($data['milestones'])) {
            $data['milestones'] = array_values($data['milestones']);
        }

        $donation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Donation campaign updated successfully',
            'data' => $donation
        ]);
    }

    public function destroy($id)
    {
        $donation = Donation::findOrFail($id);

        if ($donation->user_id != $this->authUserId()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete images
        if ($donation->cover_image) {
            Storage::disk('public')->delete($donation->cover_image);
        }
        if ($donation->images) {
            foreach ($donation->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $donation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Donation campaign deleted successfully'
        ]);
    }

    public function myDonations(Request $request)
    {
        $userId = $this->authUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $donations = Donation::where('user_id', $userId)
            ->latest('published_at')
            ->latest('created_at')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function statistics()
    {
        $totalDonations = Donation::active()->count();
        $totalRaised = Donation::active()->sum('current_amount');
        $totalGoal = Donation::active()->sum('goal_amount');
        $totalDonors = Donation::active()->sum('donor_count');

        return response()->json([
            'success' => true,
            'data' => [
                'total_donations' => $totalDonations,
                'total_raised' => $totalRaised,
                'total_goal' => $totalGoal,
                'total_donors' => $totalDonors,
                'average_progress' => $totalGoal > 0 ? round(($totalRaised / $totalGoal) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Start a donation pledge (PayPal confirms next).
     */
    public function startDonate(Request $request, $id)
    {
        if (!Schema::hasTable('donation_contributions')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations: donation_contributions missing.',
            ], 503);
        }

        $donation = Donation::active()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:999999',
            'donor_name' => 'nullable|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = $this->authUserId();
        $contribution = DonationContribution::create([
            'donation_id' => $donation->id,
            'customer_id' => $userId,
            'donor_name' => $request->boolean('is_anonymous')
                ? 'Anonymous'
                : ($request->input('donor_name') ?: 'Supporter'),
            'donor_email' => $request->input('donor_email'),
            'amount' => $request->input('amount'),
            'currency' => $donation->currency ?: 'USD',
            'is_anonymous' => $request->boolean('is_anonymous'),
            'message' => $request->input('message'),
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donation started. Complete PayPal payment to confirm.',
            'data' => [
                'contribution_id' => $contribution->id,
                'donation_id' => $donation->id,
                'amount' => (float) $contribution->amount,
                'currency' => $contribution->currency,
                'payment_status' => 'pending',
                'title' => $donation->title,
            ],
        ], 201);
    }

    /**
     * Confirm PayPal payment and credit the campaign.
     */
    public function confirmDonate(Request $request, $contributionId)
    {
        if (!Schema::hasTable('donation_contributions')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 503);
        }

        $contribution = DonationContribution::findOrFail($contributionId);
        $userId = $this->authUserId();

        if ($contribution->customer_id && $userId && (int) $contribution->customer_id !== (int) $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_id' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($contribution->payment_status === 'completed') {
            $donation = Donation::find($contribution->donation_id);
            return response()->json([
                'success' => true,
                'message' => 'Already confirmed.',
                'data' => [
                    'contribution_id' => $contribution->id,
                    'payment_status' => 'completed',
                    'donation' => $donation,
                ],
            ]);
        }

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $contribution->amount,
            'donation',
            $contribution->id
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        DB::transaction(function () use ($contribution, $request, $verified) {
            $contribution->payment_status = 'completed';
            $contribution->payment_method = $request->input('payment_method', 'paypal');
            $contribution->payment_id = $verified['payment_id'];
            $contribution->save();

            $donation = Donation::lockForUpdate()->findOrFail($contribution->donation_id);
            $donation->current_amount = (float) $donation->current_amount + (float) $contribution->amount;
            $donation->donor_count = (int) $donation->donor_count + 1;
            $donation->save();
        });

        $donation = Donation::find($contribution->donation_id);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your donation is confirmed.',
            'data' => [
                'contribution_id' => $contribution->id,
                'payment_status' => 'completed',
                'amount' => (float) $contribution->amount,
                'currency' => $contribution->currency,
                'donation' => $donation,
            ],
        ]);
    }
}
