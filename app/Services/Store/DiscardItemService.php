<?php

namespace App\Services\Store;

use App\Models\Store\DiscardItem;
use Illuminate\Support\Facades\Validator;

class DiscardItemService
{
    // Get all discarded items
    public function getAll()
    {
        $data = DiscardItem::orderBy('id', 'desc')->get();
        return response()->json(['data' => $data], 200);
    }

    // Store a new discard item
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'return_id' => 'required|string',
            'item_id' => 'required|integer|exists:items,id',
            'batch_no' => 'nullable|string|max:255',
            'returned_department' => 'required|string|max:255',
            'return_by' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'unit' => 'required|string|max:255',
            'sub_unit_qty' => 'nullable|integer',
            'sub_unit' => 'nullable|string',
            'discarded_by' => 'required|string|max:255',
            'discarded_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $discard = DiscardItem::create($validator->validated());

        return response()->json([
            'message' => 'Item discarded successfully',
            'data' => $discard
        ], 201);
    }

    // Get a single discard item by ID
    public function getById($id)
    {
        $discard = DiscardItem::find($id);
        if (!$discard) {
            return response()->json(['message' => 'Discard record not found'], 404);
        }
        return response()->json(['data' => $discard], 200);
    }

    // Delete a discard item
    public function delete($id)
    {
        $discard = DiscardItem::find($id);
        if (!$discard) {
            return response()->json(['message' => 'Discard record not found'], 404);
        }

        $discard->delete();

        return response()->json(['message' => 'Discard record deleted successfully'], 200);
    }
}
