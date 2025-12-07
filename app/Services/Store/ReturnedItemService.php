<?php

namespace App\Services\Store;

use App\Models\Store\ReturnedItem;
use App\Models\Store\ReturnedItemDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReturnedItemService
{
    // List all returned items (summary)
    public function getAll()
    {
        $returned = ReturnedItem::latest()->get();
        return response()->json(['data' => $returned], 200);
    }

    // Get single returned item with details
    public function getById($returned_id)
    {
        $returned = ReturnedItem::with('details')->where('returned_id', $returned_id)->first();
        if (!$returned) {
            return response()->json(['message' => 'Returned record not found.'], 404);
        }
        return response()->json(['data' => $returned], 200);
    }

    // Store new returned item with multiple details
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'department' => 'required|string',
            'returned_by' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.batch_no' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.reason' => 'required|string',
            'items.*.note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $returned = ReturnedItem::create([
                'date' => $request->date,
                'department' => $request->department,
                'returned_by' => $request->returned_by,
            ]);

            foreach ($request->items as $item) {
                ReturnedItemDetail::create([
                    'returned_id' => $returned->returned_id,
                    'date' => $returned->date,
                    'department' => $returned->department,
                    'returned_by' => $returned->returned_by,
                    'note' => $item['note'] ?? null,
                    'item_name' => $item['item_name'],
                    'batch_no' => $item['batch_no'],
                    'qty' => $item['qty'],
                    'reason' => $item['reason'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Returned item record created successfully.',
                'returned_id' => $returned->returned_id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create returned record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update returned item and its details
    public function update($returned_id, $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'department' => 'sometimes|string',
            'returned_by' => 'sometimes|string',
            'items' => 'nullable|array|min:1',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.batch_no' => 'required_with:items|string',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.reason' => 'required_with:items|string',
            'items.*.note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $returned = ReturnedItem::where('returned_id', $returned_id)->first();
        if (!$returned) {
            return response()->json(['message' => 'Returned record not found.'], 404);
        }

        DB::beginTransaction();
        try {
            // Update main record
            $returned->update([
                'date' => $request->date ?? $returned->date,
                'department' => $request->department ?? $returned->department,
                'returned_by' => $request->returned_by ?? $returned->returned_by,
            ]);

            // Update / replace details if provided
            if (!empty($request->items)) {
                ReturnedItemDetail::where('returned_id', $returned_id)->delete();

                foreach ($request->items as $item) {
                    ReturnedItemDetail::create([
                        'returned_id' => $returned->returned_id,
                        'date' => $returned->date,
                        'department' => $returned->department,
                        'returned_by' => $returned->returned_by,
                        'note' => $item['note'] ?? null,
                        'item_name' => $item['item_name'],
                        'batch_no' => $item['batch_no'],
                        'qty' => $item['qty'],
                        'reason' => $item['reason'],
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Returned record updated successfully.',
                'data' => $returned->load('details'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update returned record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete returned item and its details
    public function delete($returned_id)
    {
        $returned = ReturnedItem::where('returned_id', $returned_id)->first();
        if (!$returned) {
            return response()->json(['message' => 'Returned record not found.'], 404);
        }

        DB::beginTransaction();
        try {
            ReturnedItemDetail::where('returned_id', $returned_id)->delete();
            $returned->delete();

            DB::commit();
            return response()->json(['message' => 'Returned record deleted successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete returned record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
