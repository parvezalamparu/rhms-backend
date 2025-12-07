<?php

namespace App\Services\Store;

use App\Models\Store\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitService
{
    // Get all units (active + inactive)
    public function getAllUnits()
    {
        $units = Unit::orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $units,
        ], 200);
    }

    // Get only active units
    public function getActiveUnits()
    {
        $units = Unit::where('is_active', true)->orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $units,
        ], 200);
    }

    // Create a new unit
    public function createUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_name' => 'required|string|max:255|unique:units,unit_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = Unit::create([
            'unit_name' => $request->unit_name,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Unit created successfully.',
            'data' => $unit,
        ], 201);
    }

    // Get single unit by ID
    public function getUnitById($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        return response()->json(['data' => $unit], 200);
    }

    // Update a unit
    public function updateUnit(Request $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'unit_name' => 'required|string|max:255|unique:units,unit_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit->update([
            'unit_name' => $request->unit_name,
        ]);

        return response()->json([
            'message' => 'Unit updated successfully.',
            'data' => $unit,
        ], 200);
    }

    // Delete a unit
    public function deleteUnit($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully.'], 200);
    }

    // Toggle active/inactive status
    public function toggleStatus($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        $unit->is_active = !$unit->is_active;
        $unit->save();

        $status = $unit->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Unit {$status} successfully.",
            'data' => $unit,
        ], 200);
    }
}
