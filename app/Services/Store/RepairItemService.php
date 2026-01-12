<?php

namespace App\Services\Store;

use App\Models\Store\RepairItems;
use App\Models\Store\RepairItemDetails;
use App\Models\Store\DiscardItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RepairItemService
{
    // Get all repair records
    public function getAll()
    {
        $repairs = RepairItems::withCount('details')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $repairs], 200);
    }

    // Get single repair record
    public function getById($return_id)
    {
        $repair = RepairItems::with('details')
            ->where('return_id', $return_id)
            ->first();

        if (!$repair) {
            return response()->json(['message' => 'Repair record not found.'], 404);
        }

        return response()->json(['data' => $repair], 200);
    }

    // Create repair record
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'return_id' => 'required|unique:repair_items,return_id',
            'date' => 'required|date',
            'dept' => 'required|string',
            'returned_by' => 'required|string',
            'sent_by' => 'required|string',
            'status' => 'required|string',
            'note' => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',

            'items.*.unit_qty' => 'required|integer|min:0',
            'items.*.unit' => 'nullable|string',

            'items.*.sub_unit_qty' => 'required|integer|min:0',
            'items.*.sub_unit' => 'nullable|string',

            'items.*.repair_amount' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $repair = RepairItems::create([
                'return_id' => $request->return_id,
                'date' => $request->date,
                'dept' => $request->dept,
                'returned_by' => $request->returned_by,
                'sent_by' => $request->sent_by,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            foreach ($request->items as $item) {
                RepairItemDetails::create([
                    'return_id' => $repair->return_id,
                    'date' => $repair->date,
                    'dept' => $repair->dept,
                    'returned_by' => $repair->returned_by,
                    'sent_by' => $repair->sent_by,
                    'status' => $repair->status,
                    'note' => $repair->note,

                    'item_id' => $item['item_id'],
                    'batch_no' => $item['batch_no'],
                    'qty' => $item['qty'],

                    'unit_qty' => $item['unit_qty'],
                    'unit' => $item['unit'] ?? null,

                    'sub_unit_qty' => $item['sub_unit_qty'],
                    'sub_unit' => $item['sub_unit'] ?? null,

                    'repair_amount' => $item['repair_amount'] ?? null,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Repair record created successfully!',
                'data' => $repair->load('details'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create repair record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update repair
    public function update($return_id, $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'dept' => 'sometimes|string',
            'returned_by' => 'sometimes|string',
            'sent_by' => 'sometimes|string',
            'status' => 'sometimes|string',
            'note' => 'nullable|string',

            'items' => 'nullable|array|min:1',

            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required_with:items|string',
            'items.*.qty' => 'required_with:items|integer|min:1',

            'items.*.unit_qty' => 'required_with:items|integer|min:0',
            'items.*.unit' => 'nullable|string',

            'items.*.sub_unit_qty' => 'required_with:items|integer|min:0',
            'items.*.sub_unit' => 'nullable|string',

            'items.*.repair_amount' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $repair = RepairItems::where('return_id', $return_id)->first();

        if (!$repair) {
            return response()->json(['message' => 'Repair record not found.'], 404);
        }

        DB::beginTransaction();

        try {
            $repair->update([
                'date' => $request->date ?? $repair->date,
                'dept' => $request->dept ?? $repair->dept,
                'returned_by' => $request->returned_by ?? $repair->returned_by,
                'sent_by' => $request->sent_by ?? $repair->sent_by,
                'status' => $request->status ?? $repair->status,
                'note' => $request->note ?? $repair->note,
            ]);

            // Update items
            if (!empty($request->items)) {
                RepairItemDetails::where('return_id', $return_id)->delete();

                foreach ($request->items as $item) {
                    RepairItemDetails::create([
                        'return_id' => $repair->return_id,
                        'date' => $repair->date,
                        'dept' => $repair->dept,
                        'returned_by' => $repair->returned_by,
                        'sent_by' => $repair->sent_by,
                        'status' => $repair->status,
                        'note' => $repair->note,

                        'item_id' => $item['item_id'],
                        'batch_no' => $item['batch_no'],
                        'qty' => $item['qty'],

                        'unit_qty' => $item['unit_qty'],
                        'unit' => $item['unit'] ?? null,

                        'sub_unit_qty' => $item['sub_unit_qty'],
                        'sub_unit' => $item['sub_unit'] ?? null,

                        'repair_amount' => $item['repair_amount'] ?? null,
                        'reason' => $item['reason'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Repair record updated successfully!',
                'data' => $repair->load('details'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update repair record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete repair record
    public function delete($return_id)
    {
        $repair = RepairItems::where('return_id', $return_id)->first();

        if (!$repair) {
            return response()->json(['message' => 'Repair record not found.'], 404);
        }

        DB::beginTransaction();

        try {
            RepairItemDetails::where('return_id', $return_id)->delete();
            $repair->delete();

            DB::commit();

            return response()->json(['message' => 'Repair record deleted successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete repair record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Move to discard
public function discardRepairItem($return_id)
{
    $repairItem = RepairItems::with('details')->where('return_id', $return_id)->first();
    
    if (!$repairItem) {
        return response()->json(['message' => 'Repair item not found'], 404);
    }

    // Get discarded_by from request
    $discardedBy = request('discarded_by') ?? auth()->user()->name ?? 'System';

    DB::beginTransaction();
    try {
        // Create discard records for each repair detail
        foreach ($repairItem->details as $detail) {
            DiscardItem::create([
                'return_id' => $repairItem->return_id,
                'item_id' => $detail->item_id,
                'batch_no' => $detail->batch_no,
                'returned_department' => $repairItem->dept,
                'return_by' => $repairItem->returned_by,
                'qty' => $detail->qty,
                'unit' => $detail->unit,
                'sub_unit_qty' => $detail->sub_unit_qty,
                'sub_unit' => $detail->sub_unit,
                'discarded_by' => $discardedBy,
                'discarded_reason' => $detail->reason ?? 'Repair failed',
            ]);
        }

        // Update repair status to cancelled
        $repairItem->update(['status' => 'discarded']);

        DB::commit();
        return response()->json(['message' => 'Repair item discarded successfully'], 200);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to discard repair item.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
