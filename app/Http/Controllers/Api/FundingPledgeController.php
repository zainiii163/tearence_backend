<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Models\FundingProject;
use App\Models\FundingPledge;
use App\Models\FundingReward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FundingPledgeController extends Controller
{
    use VerifiesClientPayments;

    public function store(Request $request, $projectId)
    {
        $project = FundingProject::findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . ($project->minimum_contribution ?? 1),
            'funding_reward_id' => 'nullable|exists:funding_rewards,id',
            'notes' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$project->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Project is not currently accepting pledges'
            ], 403);
        }

        $data = $validator->validated();
        $data['customer_id'] = Auth::id();
        $data['funding_project_id'] = $projectId;
        $data['currency'] = $project->currency ?: 'USD';
        $data['status'] = 'pending';
        $data['is_anonymous'] = $data['is_anonymous'] ?? false;

        if (!empty($data['funding_reward_id'])) {
            $reward = FundingReward::findOrFail($data['funding_reward_id']);

            if ((int) $reward->funding_project_id !== (int) $projectId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward does not belong to this project'
                ], 422);
            }

            if ($reward->isLimitReached()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reward is out of stock'
                ], 403);
            }

            if ($data['amount'] < $reward->minimum_contribution) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pledge amount is below the minimum for this reward'
                ], 422);
            }
        }

        $pledge = FundingPledge::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pledge created successfully. Complete payment to confirm your backing.',
            'data' => $pledge->load('reward')
        ], 201);
    }

    /**
     * Backer confirms PayPal (or other) payment — marks pledge completed and updates project totals.
     */
    public function confirmPayment(Request $request, $pledgeId)
    {
        $pledge = FundingPledge::with(['fundingProject', 'reward'])->findOrFail($pledgeId);

        if ((int) $pledge->customer_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($pledge->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Pledge already completed',
                'data' => $pledge
            ]);
        }

        if ($pledge->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending pledges can be confirmed'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $payload = $validator->validated();

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $pledge->amount,
            'funding_pledge',
            $pledge->id
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }
        $payload['payment_id'] = $verified['payment_id'];

        DB::transaction(function () use ($pledge, $payload) {
            $pledge->update([
                'status' => 'completed',
                'transaction_id' => $payload['payment_id'],
                'payment_method' => $payload['payment_method'] ?? 'paypal',
                'completed_at' => now(),
            ]);

            if ($pledge->funding_reward_id && $pledge->reward) {
                $pledge->reward->increment('claimed_count');
            }

            $project = $pledge->fundingProject;
            $project->increment('current_funded', $pledge->amount);
            $project->increment('backers_count');

            if ($project->fresh()->isFunded() && $project->status === 'active') {
                $project->update(['status' => 'funded']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Thank you for backing this project!',
            'data' => $pledge->fresh()->load(['reward', 'fundingProject'])
        ]);
    }

    public function show($pledgeId)
    {
        $pledge = FundingPledge::with(['fundingProject', 'reward', 'customer'])->findOrFail($pledgeId);

        $isOwner = (int) $pledge->customer_id === (int) Auth::id();
        $isAdmin = Auth::user() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin();

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $pledge
        ]);
    }

    public function myPledges(Request $request)
    {
        $pledges = FundingPledge::where('customer_id', Auth::id())
            ->with(['fundingProject', 'reward'])
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $pledges
        ]);
    }

    public function updateStatus(Request $request, $pledgeId)
    {
        $pledge = FundingPledge::with('fundingProject')->findOrFail($pledgeId);
        $project = $pledge->fundingProject;

        $isProjectOwner = $project && (int) $project->customer_id === (int) Auth::id();
        $isAdmin = Auth::user() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin();

        if (!$isProjectOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if ($data['status'] === 'completed' && $pledge->status !== 'completed') {
            $data['completed_at'] = now();

            if ($pledge->funding_reward_id && $pledge->reward) {
                $pledge->reward->increment('claimed_count');
            }

            $project->increment('current_funded', $pledge->amount);
            $project->increment('backers_count');
        }

        $pledge->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pledge status updated successfully',
            'data' => $pledge
        ]);
    }

    public function destroy($pledgeId)
    {
        $pledge = FundingPledge::findOrFail($pledgeId);

        if ($pledge->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending pledges can be deleted'
            ], 403);
        }

        if ((int) $pledge->customer_id !== (int) Auth::id()) {
            $isAdmin = Auth::user() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin();
            if (!$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        $pledge->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pledge deleted successfully'
        ]);
    }

    public function projectPledges(Request $request, $projectId)
    {
        $project = FundingProject::findOrFail($projectId);

        $pledges = $project->pledges()
            ->where('status', 'completed')
            ->when(!$request->boolean('include_anonymous'), function ($query) {
                $query->where('is_anonymous', false);
            })
            ->with('customer:customer_id,first_name,last_name')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $pledges
        ]);
    }
}
