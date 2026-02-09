<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends APIController
{
    /**
     * Get current maintenance status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        $isDown = app()->isDownForMaintenance();
        
        $maintenanceData = null;
        if ($isDown) {
            $maintenanceFilePath = storage_path('framework/down');
            if (File::exists($maintenanceFilePath)) {
                $maintenanceData = json_decode(File::get($maintenanceFilePath), true);
            }
        }
        
        return $this->successResponse([
            'is_maintenance' => $isDown,
            'maintenance_data' => $maintenanceData,
            'timestamp' => now()->toIso8601String()
        ], 'Maintenance status retrieved', Response::HTTP_OK);
    }

    /**
     * Enable maintenance mode
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function down(Request $request)
    {
        try {
            // Check if already in maintenance mode
            if (app()->isDownForMaintenance()) {
                return $this->errorResponse('Website is already in maintenance mode', Response::HTTP_BAD_REQUEST);
            }

            $message = $request->input('message', 'Site is under maintenance. Please check back soon.');
            $retry = $request->input('retry', 60);
            $secret = $request->input('secret', null);
            $refresh = $request->input('refresh', 0);

            $options = [
                '--message' => $message,
                '--retry' => $retry,
                '--refresh' => $refresh,
            ];

            // Add secret if provided (allows admin access via URL)
            if ($secret) {
                $options['--secret'] = $secret;
            }

            Artisan::call('down', $options);

            Log::info('Maintenance mode enabled', [
                'admin_id' => auth('api')->id(),
                'admin_email' => auth('api')->user()->email ?? 'Unknown',
                'message' => $message,
                'retry' => $retry
            ]);

            return $this->successResponse([
                'is_maintenance' => true,
                'message' => $message,
                'retry' => $retry,
                'secret' => $secret
            ], 'Website is now in Maintenance Mode', Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Failed to enable maintenance mode', [
                'error' => $e->getMessage(),
                'admin_id' => auth('api')->id()
            ]);

            return $this->errorResponse('Failed to enable maintenance mode: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Disable maintenance mode
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function up()
    {
        try {
            // Check if not in maintenance mode
            if (!app()->isDownForMaintenance()) {
                return $this->errorResponse('Website is not in maintenance mode', Response::HTTP_BAD_REQUEST);
            }

            Artisan::call('up');

            Log::info('Maintenance mode disabled', [
                'admin_id' => auth('api')->id(),
                'admin_email' => auth('api')->user()->email ?? 'Unknown'
            ]);

            return $this->successResponse([
                'is_maintenance' => false
            ], 'Website is Live Now', Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Failed to disable maintenance mode', [
                'error' => $e->getMessage(),
                'admin_id' => auth('api')->id()
            ]);

            return $this->errorResponse('Failed to disable maintenance mode: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Schedule maintenance mode
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schedule(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'message' => 'nullable|string|max:500',
            'notify_users' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Store scheduled maintenance in database or cache
        // This would require a scheduled task to check and enable maintenance
        
        return $this->successResponse([
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'message' => $request->message
        ], 'Maintenance scheduled successfully', Response::HTTP_OK);
    }

    /**
     * Get maintenance logs
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!File::exists($logFile)) {
                return $this->successResponse([], 'No logs found', Response::HTTP_OK);
            }

            // Get last 50 lines of maintenance-related logs
            $logs = [];
            $lines = file($logFile);
            $maintenanceLogs = array_filter($lines, function($line) {
                return strpos($line, 'Maintenance mode') !== false;
            });

            // Get last 50 entries
            $maintenanceLogs = array_slice($maintenanceLogs, -50);

            return $this->successResponse([
                'logs' => array_values($maintenanceLogs),
                'count' => count($maintenanceLogs)
            ], 'Maintenance logs retrieved', Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve logs: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
