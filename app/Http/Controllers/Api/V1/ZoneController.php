<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ZoneController extends Controller
{
    /**
     * Get zones (cities/states) for a given country.
     * Public endpoint for cascading dropdowns.
     */
    public function getByCountry(Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        
        if (! $countryId) {
            return response()->json([
                'success' => false,
                'message' => 'country_id is required',
                'data' => [],
            ], 400);
        }

        $zones = Zone::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['zone_id', 'name', 'code', 'country_id']);

        return response()->json([
            'success' => true,
            'data' => $zones->map(fn ($z) => [
                'id' => $z->zone_id,
                'name' => $z->name,
                'code' => $z->code,
                'country_id' => $z->country_id,
            ]),
        ]);
    }

    /**
     * Get all active zones for a country (admin usage).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Zone::query()
            ->where('is_active', true)
            ->with('country:id,name,iso_code');

        if ($countryId = $request->query('country_id')) {
            $query->where('country_id', $countryId);
        }

        if ($search = $request->query('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $zones = $query->orderBy('country_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['zone_id', 'name', 'code', 'country_id', 'sort_order']);

        return response()->json([
            'success' => true,
            'data' => $zones->map(fn ($z) => [
                'id' => $z->zone_id,
                'name' => $z->name,
                'code' => $z->code,
                'country_id' => $z->country_id,
                'country' => $z->country ? [
                    'id' => $z->country->country_id,
                    'name' => $z->country->name,
                    'iso_code' => $z->country->iso_code,
                ] : null,
            ]),
        ]);
    }
}