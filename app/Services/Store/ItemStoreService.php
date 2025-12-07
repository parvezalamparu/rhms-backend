<?php

namespace App\Services\Store;

use App\Models\Store\ItemStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemStoreService
{
    // Get all item stores
    public function getAllItemStores()
    {
        $stores = ItemStore::all();

        return response()->json(['data' => $stores], 200);
    }

    // Get only active item stores
    public function getActiveItemStores()
    {
        $stores = ItemStore::where('is_active', true)->get();

        return response()->json(['data' => $stores], 200);
    }

    // Create new item store
    public function createItemStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255|unique:item_stores,store_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store = ItemStore::create($validator->validated());

        return response()->json([
            'message' => 'Item store created successfully.',
            'data' => $store,
        ], 201);
    }

    // Get item store by ID
    public function getItemStoreById($id)
    {
        $store = ItemStore::findOrFail($id);

        return response()->json(['data' => $store], 200);
    }

    /**
     * Update item store
     */
    public function updateItemStore(Request $request, $id)
    {
        $store = ItemStore::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255|unique:item_stores,store_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store->update($validator->validated());

        return response()->json([
            'message' => 'Item store updated successfully.',
            'data' => $store,
        ], 200);
    }

    /**
     * Delete item store
     */
    public function deleteItemStore($id)
    {
        $store = ItemStore::findOrFail($id);
        $store->delete();

        return response()->json(['message' => 'Item store deleted successfully.'], 200);
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        $store = ItemStore::findOrFail($id);
        $store->is_active = !$store->is_active;
        $store->save();

        $status = $store->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Item store {$status} successfully.",
            'data' => $store,
        ], 200);
    }
}
