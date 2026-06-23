<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdController extends Controller
{
    /**
     * Show create ad form
     */
    public function index()
    {
        return view('create-ad');
    }

    /**
     * Store new ad
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|string|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|string|in:property,vehicle,job,service,business',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('public/ads', 'public');
                    $imagePaths[] = $path;
                }
            }

            $ad = auth()->user()->ads()->create([
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category,
                'price' => $request->price,
                'location' => $request->location,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'type' => $request->type,
                'images' => json_encode($imagePaths),
                'slug' => Str::slug($request->title),
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad created successfully',
                'data' => $ad
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show edit form for existing ad
     */
    public function edit($id)
    {
        $ad = auth()->user()->ads()->findOrFail($id);
        
        if ($ad->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('create-ad', compact('ad'));
    }

    /**
     * Update existing ad
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category' => 'required|string|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|string|in:property,vehicle,job,service,business',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ad = auth()->user()->ads()->findOrFail($id);
            
            if ($ad->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('public/ads', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Update ad
            $ad->update([
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category,
                'price' => $request->price,
                'location' => $request->location,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'type' => $request->type,
                'images' => json_encode($imagePaths),
                'slug' => Str::slug($request->title),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad updated successfully',
                'data' => $ad
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ad
     */
    public function destroy($id)
    {
        $ad = auth()->user()->ads()->findOrFail($id);
        
        if ($ad->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Delete images if they exist
        if ($ad->images) {
            $images = json_decode($ad->images, true);
            foreach ($images as $image) {
                if (file_exists(public_path($image))) {
                    unlink(public_path($image));
                }
            }
        }

        $ad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ad deleted successfully'
        ]);
    }
}
