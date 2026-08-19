<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocalAreaAlert;
use App\Support\GeoIpLocator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LocalAreaAlertController extends Controller
{
    public function index(Request $request)
    {
        $geo = GeoIpLocator::locate($request);
        $city = $geo['city'];
        $country = $geo['country'];
        $type = $request->get('type');

        $query = LocalAreaAlert::query()->active()->orderByDesc('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        if ($city || $country) {
            $query->inArea($city, $country);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'geo' => $geo,
                    'message' => 'Share your city to see local parking and traffic alerts.',
                ],
            ]);
        }

        $items = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'geo' => array_merge($geo, [
                    'city' => $city,
                    'country' => $country,
                ]),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:parking,traffic',
            'title' => 'required|string|max:180',
            'message' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:120',
            'country' => 'nullable|string|max:120',
            'area' => 'nullable|string|max:180',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $geo = GeoIpLocator::locate($request);
        $user = auth('api')->user();
        $city = $geo['city'] ?: $request->city;
        $country = $geo['country'] ?: $request->country;

        if (! $city && ! $country) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect your area. Try again from a public network.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $alert = LocalAreaAlert::create([
            'customer_id' => $user?->customer_id ?? $user?->getKey(),
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'city' => $city,
            'country' => $country,
            'area' => $request->area,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'expires_at' => now()->addHours(12),
        ]);

        return response()->json([
            'success' => true,
            'data' => $alert,
            'message' => 'Alert shared with people in this area.',
        ], Response::HTTP_CREATED);
    }
}
