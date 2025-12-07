<?php

namespace App\Services\Store;

use App\Models\Store\Item;
use Illuminate\Support\Facades\Validator;

class ItemService
{
    // Get all items
    public function getAllItems()
    {
        $items = Item::orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $items,
        ], 200);
    }

    // Create new item
    public function createItem($request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:items,item_code',
            'item_type' => 'required|string',
            'item_category' => 'required|string',
            'item_subcategory' => 'nullable|string',
            'low_level' => 'required|numeric|min:0',
            'high_level' => 'required|numeric|min:0',
            'company' => 'nullable|string|max:50',
            'stored' => 'nullable|string|max:50',
            'hsn_or_sac_no' => 'nullable|string|max:255',
            'item_unit' => 'required|string|max:50',
            'item_subunit' => 'required|string|max:50',
            'unit_subunit_ratio' => 'required|string|max:50',
            'rack_no' => 'required|string|max:20',
            'shelf_no' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($data);

        return response()->json([
            'message' => 'Item created successfully.',
            'data' => $item,
        ], 201);
    }

    // Get item by UUID
    // In your ItemService class
public function getItemById($uuid)
{
    $item = Item::where('uuid', $uuid)->first();
    
    if (!$item) {
        return response()->json(['message' => 'Item not found.'], 404);
    }

    return response()->json(['data' => $item], 200);
}
    // Update item
    public function updateItem($request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:items,item_code,' . $id,
            'item_type' => 'required|string',
            'item_category' => 'required|string',
            'item_subcategory' => 'nullable|string',
            'low_level' => 'required|numeric|min:0',
            'high_level' => 'required|numeric|min:0',
            'company' => 'nullable|string|max:50',
            'stored' => 'nullable|string|max:50',
            'hsn_or_sac_no' => 'nullable|string|max:255',
            'item_unit' => 'required|string|max:50',
            'item_subunit' => 'required|string|max:50',
            'unit_subunit_ratio' => 'required|string|max:50',
            'rack_no' => 'required|string|max:20',
            'shelf_no' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);

        return response()->json([
            'message' => 'Item updated successfully.',
            'data' => $item,
        ], 200);
    }

    // Delete item
    public function deleteItem($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully.'], 200);
    }

    // Toggle Active/Inactive
    public function toggleItemStatus($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json([
            'message' => 'Item status updated successfully.',
            'data' => $item,
        ], 200);
    }
}
