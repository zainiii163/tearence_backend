<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get list of conversations for the authenticated user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversations(Request $request)
    {
        try {
            $user_id = auth()->user()->user_id;

            // TODO: Implement actual conversation retrieval logic when chat models are created
            // For now, return empty array to prevent frontend errors
            $conversations = [];

            return $this->successResponse($conversations, 'Conversations retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get unread message count for the authenticated user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user_id = auth()->user()->user_id;

            // TODO: Implement actual unread count logic when chat models are created
            // For now, return zero to prevent frontend errors
            $unreadCount = 0;

            return $this->successResponse([
                'unread_count' => $unreadCount
            ], 'Unread count retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
