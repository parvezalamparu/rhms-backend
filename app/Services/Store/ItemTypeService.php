<?php

namespace App\Services\Store;

use App\Models\Store\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemTypeService
{
    // Get all item types
    public function getAllItemTypes()
    {
        $types = ItemType::all();

        return response()->json(['data' => $types], 200);
    }

    // Get only active item types
    public function getActiveItemTypes()
    {
        $types = ItemType::where('is_active', true)->get();

        return response()->json(['data' => $types], 200);
    }

    // Create new item type
    public function createItemType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255|unique:item_types,type_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = ItemType::create($validator->validated());

        return response()->json([
            'message' => 'Item type created successfully.',
            'data' => $type,
        ], 201);
    }

    // Get single item type
    public function getItemTypeById($id)
    {
        $type = ItemType::findOrFail($id);

        return response()->json(['data' => $type], 200);
    }

    // Update item type
    public function updateItemType(Request $request, $id)
    {
        $type = ItemType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255|unique:item_types,type_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type->update($validator->validated());

        return response()->json([
            'message' => 'Item type updated successfully.',
            'data' => $type,
        ], 200);
    }

    // Delete item type
    public function deleteItemType($id)
    {
        $type = ItemType::findOrFail($id);
        $type->delete();

        return response()->json(['message' => 'Item type deleted successfully.'], 200);
    }

    // Toggle active/inactive status
    public function toggleStatus($id)
    {
        $type = ItemType::findOrFail($id);
        $type->is_active = !$type->is_active;
        $type->save();

        $status = $type->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Item type {$status} successfully.",
            'data' => $type,
        ], 200);
    }
}
