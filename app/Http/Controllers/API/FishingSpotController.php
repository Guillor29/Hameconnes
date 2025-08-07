<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FishingSpot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FishingSpotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fishingSpots = FishingSpot::with('fishSpecies')->get();
        return response()->json($fishingSpots);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'water_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'tips' => 'nullable|string',
            'access_type' => 'nullable|string|max:50',
            'user_id' => 'required|exists:users,id',
            'fish_species_ids' => 'nullable|array',
            'fish_species_ids.*' => 'exists:fish_species,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create fishing spot
        $fishingSpot = FishingSpot::create($request->except('fish_species_ids'));

        // Associate with fish species if provided
        if ($request->has('fish_species_ids') && !empty($request->fish_species_ids)) {
            $fishingSpot->fishSpecies()->attach($request->fish_species_ids);
        }

        // Load fish species relationship
        $fishingSpot->load('fishSpecies');

        return response()->json($fishingSpot, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fishingSpot = FishingSpot::with('fishSpecies')->find($id);

        if (!$fishingSpot) {
            return response()->json(['message' => 'Fishing spot not found'], 404);
        }

        return response()->json($fishingSpot);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Find fishing spot
        $fishingSpot = FishingSpot::find($id);

        if (!$fishingSpot) {
            return response()->json(['message' => 'Fishing spot not found'], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'water_type' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
            'tips' => 'nullable|string',
            'access_type' => 'nullable|string|max:50',
            'user_id' => 'sometimes|required|exists:users,id',
            'fish_species_ids' => 'nullable|array',
            'fish_species_ids.*' => 'exists:fish_species,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update fishing spot
        $fishingSpot->update($request->except('fish_species_ids'));

        // Sync fish species if provided
        if ($request->has('fish_species_ids')) {
            $fishingSpot->fishSpecies()->sync($request->fish_species_ids);
        }

        // Load fish species relationship
        $fishingSpot->load('fishSpecies');

        return response()->json($fishingSpot);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find fishing spot
        $fishingSpot = FishingSpot::find($id);

        if (!$fishingSpot) {
            return response()->json(['message' => 'Fishing spot not found'], 404);
        }

        // Delete fishing spot (this will also detach fish species due to onDelete('cascade') in migration)
        $fishingSpot->delete();

        return response()->json(['message' => 'Fishing spot deleted successfully']);
    }
}
