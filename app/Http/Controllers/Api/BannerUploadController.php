<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BannerUploadController extends Controller
{
    /**
     * Upload banner image.
     */
    public function uploadBannerImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'banner_size' => 'required|string|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
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
            $bannerSize = $request->banner_size;
            
            // Validate image dimensions
            $imageInfo = getimagesize($image->getPathname());
            $expectedDimensions = $this->getBannerDimensions($bannerSize);
            
            if ($imageInfo[0] !== $expectedDimensions['width'] || $imageInfo[1] !== $expectedDimensions['height']) {
                return response()->json([
                    'success' => false,
                    'message' => "Image dimensions must be exactly {$expectedDimensions['width']}x{$expectedDimensions['height']} pixels for {$bannerSize} banners"
                ], 422);
            }

            // Generate unique filename
            $filename = 'banner_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            // Store the image
            $path = $image->storeAs('banner-images', $filename, 'public');
            
            // Create optimized versions if needed
            $this->createOptimizedVersions($image, $filename, $bannerSize);

            return response()->json([
                'success' => true,
                'message' => 'Banner image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $image->getSize(),
                    'dimensions' => [
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload banner image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload business logo.
     */
    public function uploadBusinessLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $logo = $request->file('logo');
            
            // Generate unique filename
            $filename = 'logo_' . Str::uuid() . '.' . $logo->getClientOriginalExtension();
            
            // Store the logo
            $path = $logo->storeAs('business-logos', $filename, 'public');
            
            // Create optimized versions
            $this->createLogoOptimizations($logo, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Business logo uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $logo->getSize()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload business logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload animated banner (GIF).
     */
    public function uploadAnimatedBanner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gif' => 'required|file|mimes:gif|max:10240', // 10MB max for animated GIFs
            'banner_size' => 'required|string|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $gif = $request->file('gif');
            $bannerSize = $request->banner_size;
            
            // Validate GIF dimensions
            $imageInfo = getimagesize($gif->getPathname());
            $expectedDimensions = $this->getBannerDimensions($bannerSize);
            
            if ($imageInfo[0] !== $expectedDimensions['width'] || $imageInfo[1] !== $expectedDimensions['height']) {
                return response()->json([
                    'success' => false,
                    'message' => "GIF dimensions must be exactly {$expectedDimensions['width']}x{$expectedDimensions['height']} pixels for {$bannerSize} banners"
                ], 422);
            }

            // Generate unique filename
            $filename = 'animated_' . Str::uuid() . '.gif';
            
            // Store the GIF
            $path = $gif->storeAs('banner-images', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Animated banner uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $gif->getSize(),
                    'dimensions' => [
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload animated banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload HTML5 banner (ZIP file).
     */
    public function uploadHtml5Banner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zip' => 'required|file|mimes:zip|max:20480', // 20MB max for HTML5 banners
            'banner_size' => 'required|string|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $zip = $request->file('zip');
            
            // Generate unique filename
            $filename = 'html5_' . Str::uuid() . '.zip';
            
            // Store the ZIP file
            $path = $zip->storeAs('banner-html5', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'HTML5 banner uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $zip->getSize()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload HTML5 banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload video banner.
     */
    public function uploadVideoBanner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,webm|max:51200', // 50MB max for video
            'banner_size' => 'required|string|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
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
            $filename = 'video_' . Str::uuid() . '.' . $video->getClientOriginalExtension();
            
            // Store the video
            $path = $video->storeAs('banner-videos', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Video banner uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $video->getSize()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video banner',
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
            'type' => 'required|string|in:banner,logo,animated,html5,video',
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
                'banner', 'animated' => 'banner-images',
                'logo' => 'business-logos',
                'html5' => 'banner-html5',
                'video' => 'banner-videos',
                default => 'banner-images'
            };

            $path = $directory . '/' . $filename;
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                // Also delete optimized versions if they exist
                if ($type === 'banner') {
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
     * Get banner dimensions for a specific size.
     */
    private function getBannerDimensions(string $size): array
    {
        $dimensions = [
            '728x90' => ['width' => 728, 'height' => 90],
            '300x250' => ['width' => 300, 'height' => 250],
            '160x600' => ['width' => 160, 'height' => 600],
            '970x250' => ['width' => 970, 'height' => 250],
            '468x60' => ['width' => 468, 'height' => 60],
            '1080x1080' => ['width' => 1080, 'height' => 1080],
        ];

        return $dimensions[$size] ?? ['width' => 300, 'height' => 250];
    }

    /**
     * Create optimized versions of banner images.
     */
    private function createOptimizedVersions($image, string $filename, string $bannerSize): void
    {
        try {
            $imagePath = $image->getPathname();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                // Create webp version for better compression
                $webpFilename = str_replace('.' . $extension, '.webp', $filename);
                $webpPath = storage_path('app/public/banner-images/' . $webpFilename);
                
                $img = Image::make($imagePath);
                $img->encode('webp', 85)->save($webpPath);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create optimized banner versions: ' . $e->getMessage());
        }
    }

    /**
     * Create optimized versions of logos.
     */
    private function createLogoOptimizations($logo, string $filename): void
    {
        try {
            $logoPath = $logo->getPathname();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                // Create different sizes
                $img = Image::make($logoPath);
                
                // Small version (100x100)
                $smallFilename = str_replace('.' . $extension, '_small.' . $extension, $filename);
                $img->fit(100, 100)->save(storage_path('app/public/business-logos/' . $smallFilename));
                
                // Webp version
                $webpFilename = str_replace('.' . $extension, '.webp', $filename);
                $img->encode('webp', 85)->save(storage_path('app/public/business-logos/' . $webpFilename));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create optimized logo versions: ' . $e->getMessage());
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
            Storage::disk('public')->delete('banner-images/' . $webpFilename);
            
            // Delete other optimized versions
            $smallFilename = $baseFilename . '_small.' . $extension;
            Storage::disk('public')->delete('business-logos/' . $smallFilename);
        } catch (\Exception $e) {
            // Log error but don't fail the deletion
            \Log::error('Failed to delete optimized versions: ' . $e->getMessage());
        }
    }
}
