<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Handle general file uploads
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:20480', // 20MB max
            'type' => 'required|string|in:identity,marketing,reward,other,profile,cover',
            'project_id' => 'nullable|uuid|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid file upload data',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->type;
            $projectId = $request->project_id;

            // Validate file type based on upload type
            $allowedMimes = $this->getAllowedMimes($type);
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_FILE_TYPE',
                        'message' => 'File type not allowed for this upload type',
                    ],
                ], 422);
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Determine storage path based on type
            $storagePath = $this->getStoragePath($type, $projectId);
            $filePath = $file->storeAs($storagePath, $fileName, 'public');

            // Return file information
            $fileData = [
                'id' => Str::uuid(),
                'name' => $file->getClientOriginalName(),
                'file_url' => Storage::url($filePath),
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'upload_type' => $type,
                'created_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $fileData,
                'message' => 'File uploaded successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPLOAD_ERROR',
                    'message' => 'Failed to upload file: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request data',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            $filePath = $request->file_path;
            
            // Remove /storage/ prefix if present
            $cleanPath = str_replace('/storage/', '', $filePath);
            
            if (Storage::disk('public')->exists($cleanPath)) {
                Storage::disk('public')->delete($cleanPath);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'File not found',
                ],
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_ERROR',
                    'message' => 'Failed to delete file: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get file information
     */
    public function getFileInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request data',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            $filePath = $request->file_path;
            $cleanPath = str_replace('/storage/', '', $filePath);

            if (Storage::disk('public')->exists($cleanPath)) {
                $fileInfo = [
                    'exists' => true,
                    'size' => Storage::disk('public')->size($cleanPath),
                    'last_modified' => Storage::disk('public')->lastModified($cleanPath),
                    'mime_type' => Storage::disk('public')->mimeType($cleanPath),
                    'url' => Storage::url($cleanPath),
                ];

                return response()->json([
                    'success' => true,
                    'data' => $fileInfo,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'File not found',
                ],
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INFO_ERROR',
                    'message' => 'Failed to get file info: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get allowed MIME types for different upload types
     */
    private function getAllowedMimes(string $type): array
    {
        $mimeTypes = [
            'identity' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg',
            ],
            'marketing' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'video/mp4',
                'video/quicktime',
                'video/x-msvideo',
            ],
            'reward' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg',
                'video/mp4',
                'audio/mpeg',
                'audio/wav',
            ],
            'other' => [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'video/mp4',
                'video/quicktime',
                'video/x-msvideo',
                'audio/mpeg',
                'audio/wav',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'profile' => [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/webp',
            ],
            'cover' => [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/webp',
            ],
        ];

        return $mimeTypes[$type] ?? $mimeTypes['other'];
    }

    /**
     * Get storage path based on upload type and project ID
     */
    private function getStoragePath(string $type, ?string $projectId = null): string
    {
        $basePaths = [
            'identity' => 'project-documents/identity',
            'marketing' => 'project-documents/marketing',
            'reward' => 'project-documents/rewards',
            'other' => 'project-documents/other',
            'profile' => 'user-profiles',
            'cover' => 'project-covers',
        ];

        $path = $basePaths[$type] ?? 'project-documents/other';

        if ($projectId) {
            $path .= '/' . $projectId;
        }

        return $path;
    }

    /**
     * Validate file size and type
     */
    private function validateFile($file, string $type): array
    {
        $maxSizes = [
            'identity' => 5 * 1024 * 1024, // 5MB
            'marketing' => 20 * 1024 * 1024, // 20MB
            'reward' => 15 * 1024 * 1024, // 15MB
            'other' => 10 * 1024 * 1024, // 10MB
            'profile' => 5 * 1024 * 1024, // 5MB
            'cover' => 10 * 1024 * 1024, // 10MB
        ];

        $maxSize = $maxSizes[$type] ?? 10 * 1024 * 1024;
        $allowedMimes = $this->getAllowedMimes($type);

        $errors = [];

        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size for this type';
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type not allowed for this upload type';
        }

        return $errors;
    }

    /**
     * Generate secure file name
     */
    private function generateSecureFileName($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Sanitize filename
        $sanitizedName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalName);
        $sanitizedName = substr($sanitizedName, 0, 50); // Limit length
        
        return time() . '_' . $sanitizedName . '_' . Str::random(8) . '.' . $extension;
    }
}
