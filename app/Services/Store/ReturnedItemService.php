<?php

namespace App\Services\Store;

use App\Models\Store\ReturnedItem;
use App\Models\Store\ReturnedItemDetail;
use App\Models\Store\RepairItems;
use App\Models\Store\RepairItemDetails;
use App\Models\Store\DiscardItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReturnedItemService
{
    // List all returned items (summary)
    public function getAll()
    {
        $returned = ReturnedItem::withCount('details')->orderBy('id', 'desc')->get();
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

    // Create new returned item and details
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'department' => 'required|string|max:255',
            'returned_by' => 'required|string|max:255',
            'note' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,partially_returned,repair,add_to_item,discard',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:0',
            'items.*.unit_qty' => 'required|integer|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.sub_unit_qty' => 'nullable|integer|min:0',
            'items.*.sub_unit' => 'nullable|string|max:50',
            'items.*.reason' => 'nullable|string',
            'items.*.note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();

            $returned = ReturnedItem::create([
                'date' => $data['date'],
                'department' => $data['department'],
                'returned_by' => $data['returned_by'],
                'note' => $data['note'] ?? null,
                'status' => $data['status'] ?? 'pending',
            ]);

            foreach ($data['items'] as $item) {
                ReturnedItemDetail::create([
                    'returned_id' => $returned->returned_id,
                    'item_id' => $item['item_id'],
                    'batch_no' => $item['batch_no'],
                    'qty' => $item['qty'],
                    'unit_qty' => $item['unit_qty'],
                    'unit' => $item['unit'] ?? null,
                    'sub_unit_qty' => $item['sub_unit_qty'] ?? 0,
                    'sub_unit' => $item['sub_unit'] ?? null,
                    'reason' => $item['reason'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Returned item record created successfully.',
                'returned_id' => $returned->returned_id,
                'data' => $returned->load('details'),
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
            'department' => 'sometimes|string|max:255',
            'returned_by' => 'sometimes|string|max:255',
            'note' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected,partially_returned,repair,add_to_item,discard',

            'items' => 'nullable|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required_with:items|string|max:255',
            'items.*.qty' => 'required_with:items|integer|min:0',
            'items.*.unit_qty' => 'required_with:items|integer|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.sub_unit_qty' => 'nullable|integer|min:0',
            'items.*.sub_unit' => 'nullable|string|max:50',
            'items.*.reason' => 'nullable|string',
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
            $data = $validator->validated();

            $returned->update([
                'date' => $data['date'] ?? $returned->date,
                'department' => $data['department'] ?? $returned->department,
                'returned_by' => $data['returned_by'] ?? $returned->returned_by,
                'note' => $data['note'] ?? $returned->note,
                'status' => $data['status'] ?? $returned->status,
            ]);

            if (!empty($data['items'])) {
                // delete existing details and recreate
                ReturnedItemDetail::where('returned_id', $returned_id)->delete();

                foreach ($data['items'] as $item) {
                    ReturnedItemDetail::create([
                        'returned_id' => $returned->returned_id,
                        'item_id' => $item['item_id'],
                        'batch_no' => $item['batch_no'],
                        'qty' => $item['qty'],
                        'unit_qty' => $item['unit_qty'],
                        'unit' => $item['unit'] ?? null,
                        'sub_unit_qty' => $item['sub_unit_qty'] ?? 0,
                        'sub_unit' => $item['sub_unit'] ?? null,
                        'reason' => $item['reason'] ?? null,
                        'note' => $item['note'] ?? null,
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

 // move to repair table
public function moveToRepair($returned_id)
{
    $returned = ReturnedItem::where('returned_id', $returned_id)->first();
    if (!$returned) {
        return response()->json(['message' => 'Returned record not found.'], 404);
    }


    DB::beginTransaction();
    try {
        // Create repair record - ADD return_id
        $repair = RepairItems::create([
            'return_id' => $returned_id,  // ⭐ ADD THIS - Link to original returned item
            'date' => $returned->date,
            'dept' => $returned->department,
            'returned_by' => $returned->returned_by,
            'sent_by' => $returned->returned_by,
            'note' => $returned->note,
            'status' => 'pending',
        ]);

        // Copy all details
        $details = ReturnedItemDetail::where('returned_id', $returned_id)->get();
        
        foreach ($details as $detail) {
            RepairItemDetails::create([
                'return_id' => $repair->return_id, 
                'date' => $returned->date,
                'sent_by' => $returned->returned_by,
                'note' => $detail->note,
                'item_id' => $detail->item_id,
                'batch_no' => $detail->batch_no,
                'qty' => $detail->qty,
                'unit_qty' => $detail->unit_qty,
                'unit' => $detail->unit,
                'sub_unit_qty' => $detail->sub_unit_qty ?? 0,
                'sub_unit' => $detail->sub_unit,
                'reason' => $detail->reason,
                'repair_amount' => 0,
            ]);
        }

        // Update returned item status
        $returned->update(['status' => 'repair']);

        DB::commit();
        return response()->json([
            'message' => 'Item moved to repair successfully.',
            'return_id' => $repair->return_id,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to move to repair.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// move to discard file
public function discardReturnedItem($id)
{
    $returnedItem = ReturnedItem::with('details')->where('returned_id', $id)->first();
    
    if (!$returnedItem) {
        return response()->json(['message' => 'Item not found'], 404);
    }

    // Create discard records for each detail
    foreach ($returnedItem->details as $detail) {
        DiscardItem::create([
            'return_id' => $returnedItem->returned_id,
            'item_id' => $detail->item_id,
            'batch_no' => $detail->batch_no,
            'returned_department' => $returnedItem->department,
            'return_by' => $returnedItem->returned_by,
            'qty' => $detail->qty,
            'unit' => $detail->unit,
            'sub_unit_qty' => $detail->sub_unit_qty,
            'sub_unit' => $detail->sub_unit,
            'discarded_by' => auth()->user()->name ?? 'Store Manager',
            'discarded_reason' => $detail->reason,
        ]);
    }

    // Update status
    $returnedItem->update(['status' => 'discard']);

    return response()->json(['message' => 'Item discarded successfully'], 200);
}
}
