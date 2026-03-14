<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingProject;
use App\Models\FundingPledge;
use App\Models\FundingReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FundingPledgeController extends Controller
{
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
        $data['user_id'] = Auth::id();
        $data['funding_project_id'] = $projectId;
        $data['status'] = 'pending';
        $data['is_anonymous'] = $data['is_anonymous'] ?? false;

        // Validate reward availability if specified
        if (!empty($data['funding_reward_id'])) {
            $reward = FundingReward::findOrFail($data['funding_reward_id']);

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
            'message' => 'Pledge created successfully',
            'data' => $pledge->load('reward')
        ], 201);
    }

    public function show($pledgeId)
    {
        $pledge = FundingPledge::with(['fundingProject', 'reward', 'user'])->findOrFail($pledgeId);

        // Check authorization
        if ($pledge->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
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
        $pledges = FundingPledge::where('user_id', Auth::id())
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
        $pledge = FundingPledge::findOrFail($pledgeId);

        // Only admin or project owner can update pledge status
        if (Auth::id() !== $pledge->fundingProject->user_id && Auth::user()->role !== 'admin') {
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

        if ($data['status'] === 'completed' && !$pledge->completed_at) {
            $data['completed_at'] = now();

            // Increment reward claimed count
            if ($pledge->funding_reward_id) {
                $pledge->reward->increment('claimed_count');
            }

            // Update project amount raised and backer count
            $pledge->fundingProject->increment('amount_raised', $pledge->amount);
            $pledge->fundingProject->increment('backer_count');
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

        // Only allow deletion if status is pending
        if ($pledge->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending pledges can be deleted'
            ], 403);
        }

        // Check authorization
        if ($pledge->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
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
                          ->when(!$request->include_anonymous, function ($query) {
                              $query->where('is_anonymous', false);
                          })
                          ->with('user')
                          ->latest()
                          ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $pledges
        ]);
    }
}
