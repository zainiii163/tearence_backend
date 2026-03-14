<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BuySellUploadController extends Controller
{
    /**
     * Upload advert images.
     */
    public function uploadImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedImages = [];
            $images = $request->file('images');

            foreach ($images as $index => $image) {
                // Generate unique filename
                $filename = 'buysell_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
                
                // Store the original image
                $path = $image->storeAs('buysell-images', $filename, 'public');
                
                // Create thumbnail
                $thumbnailPath = $this->createThumbnail($image, $filename);
                
                // Create optimized versions
                $this->createOptimizedVersions($image, $filename);
                
                $uploadedImages[] = [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'thumbnail_url' => asset('storage/' . $thumbnailPath),
                    'size' => $image->getSize(),
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'dimensions' => getimagesize($image->getPathname()),
                    'sort_order' => $index,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => $uploadedImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a single advert image.
     */
    public function uploadSingleImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = 'buysell_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Store the original image
            $path = $image->storeAs('buysell-images', $filename, 'public');
            
            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($image, $filename);
            
            // Create optimized versions
            $this->createOptimizedVersions($image, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'thumbnail_url' => asset('storage/' . $thumbnailPath),
                    'size' => $image->getSize(),
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'dimensions' => getimagesize($image->getPathname()),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload advert video.
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,webm,mov,avi|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $video = $request->file('video');
            
            // Generate unique filename
            $filename = 'buysell_video_' . Str::uuid() . '.' . $video->getClientOriginalExtension();
            
            // Store the video
            $path = $video->storeAs('buysell-videos', $filename, 'public');
            
            // Create thumbnail from video or use provided thumbnail
            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailFilename = 'buysell_thumb_' . Str::uuid() . '.' . $thumbnailFile->getClientOriginalExtension();
                $thumbnailPath = $thumbnailFile->storeAs('buysell-thumbnails', $thumbnailFilename, 'public');
            } else {
                $thumbnailPath = $this->createVideoThumbnail($video, $filename);
            }
            
            // Get video duration (mock for now)
            $duration = $this->getVideoDuration($video);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'thumbnail_url' => asset('storage/' . $thumbnailPath),
                    'size' => $video->getSize(),
                    'original_name' => $video->getClientOriginalName(),
                    'mime_type' => $video->getMimeType(),
                    'duration' => $duration,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete uploaded file.
     */
    public function deleteFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'type' => 'required|string|in:image,video,thumbnail',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filename = $request->filename;
            $type = $request->type;
            
            $directory = match($type) {
                'image' => 'buysell-images',
                'video' => 'buysell-videos',
                'thumbnail' => 'buysell-thumbnails',
                default => 'buysell-images'
            };

            $path = $directory . '/' . $filename;
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                // Also delete optimized versions if they exist
                if ($type === 'image') {
                    $this->deleteOptimizedVersions($filename);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create thumbnail for image.
     */
    private function createThumbnail($image, string $filename): string
    {
        try {
            $thumbnailFilename = str_replace('.', '_thumb.', $filename);
            $thumbnailPath = storage_path('app/public/buysell-thumbnails/' . $thumbnailFilename);
            
            $img = Image::make($image->getPathname());
            $img->fit(300, 300, function ($constraint) {
                $constraint->upsize();
            })->save($thumbnailPath, 85);
            
            return 'buysell-thumbnails/' . $thumbnailFilename;
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create thumbnail: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Create optimized versions of images.
     */
    private function createOptimizedVersions($image, string $filename): void
    {
        try {
            $imagePath = $image->getPathname();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                // Create webp version for better compression
                $webpFilename = str_replace('.' . $extension, '.webp', $filename);
                $webpPath = storage_path('app/public/buysell-images/' . $webpFilename);
                
                $img = Image::make($imagePath);
                $img->encode('webp', 85)->save($webpPath);
                
                // Create medium version for gallery
                $mediumFilename = str_replace('.' . $extension, '_medium.' . $extension, $filename);
                $mediumPath = storage_path('app/public/buysell-images/' . $mediumFilename);
                
                $img->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($mediumPath, 85);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create optimized image versions: ' . $e->getMessage());
        }
    }

    /**
     * Create thumbnail for video.
     */
    private function createVideoThumbnail($video, string $filename): string
    {
        try {
            $thumbnailFilename = 'buysell_thumb_' . Str::uuid() . '.jpg';
            $thumbnailPath = storage_path('app/public/buysell-thumbnails/' . $thumbnailFilename);
            
            // For now, create a placeholder thumbnail
            // In production, you would use ffmpeg to extract frame from video
            $img = Image::canvas(300, 300, '#f0f0f0');
            $img->text('Video', 150, 150, function ($font) {
                $font->size(24);
                $font->color('#666666');
                $font->align('center');
                $font->valign('middle');
            })->save($thumbnailPath, 85);
            
            return 'buysell-thumbnails/' . $thumbnailFilename;
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create video thumbnail: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get video duration.
     */
    private function getVideoDuration($video): string
    {
        try {
            // For now, return a mock duration
            // In production, you would use ffmpeg to get actual duration
            return '00:30';
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    /**
     * Delete optimized versions.
     */
    private function deleteOptimizedVersions(string $filename): void
    {
        try {
            $baseFilename = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Delete webp version
            $webpFilename = $baseFilename . '.webp';
            Storage::disk('public')->delete('buysell-images/' . $webpFilename);
            
            // Delete medium version
            $mediumFilename = $baseFilename . '_medium.' . $extension;
            Storage::disk('public')->delete('buysell-images/' . $mediumFilename);
            
            // Delete thumbnail
            $thumbnailFilename = $baseFilename . '_thumb.' . $extension;
            Storage::disk('public')->delete('buysell-thumbnails/' . $thumbnailFilename);
        } catch (\Exception $e) {
            // Log error but don't fail the deletion
            \Log::error('Failed to delete optimized versions: ' . $e->getMessage());
        }
    }
}
