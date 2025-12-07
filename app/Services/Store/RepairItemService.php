<?php

namespace App\Services\Store;

use App\Models\Store\RepairItems;
use App\Models\Store\RepairItemDetails;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RepairItemService
{
    // Get all repair records (summary)
    public function getAll()
    {
        $repairs = RepairItems::withCount('details')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $repairs], 200);
    }

    // Get single repair record with details
    public function getById($return_id)
    {
        $repair = RepairItems::with('details')->where('return_id', $return_id)->first();
        if (!$repair) {
            return response()->json(['message' => 'Repair record not found.'], 404);
        }
        return response()->json(['data' => $repair], 200);
    }

    // Create new repair record
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'return_id' => 'required|unique:repair_items,return_id',
            'date' => 'required|date',
            'sent_by' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.batch_no' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.reason' => 'nullable|string',
            'items.*.note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $repair = RepairItems::create([
                'return_id' => $request->return_id,
                'date' => $request->date,
                'sent_by' => $request->sent_by,
            ]);

            foreach ($request->items as $item) {
                RepairItemDetails::create([
                    'return_id' => $repair->return_id,
                    'date' => $repair->date,
                    'sent_by' => $repair->sent_by,
                    'note' => $item['note'] ?? null,
                    'item_name' => $item['item_name'],
                    'batch_no' => $item['batch_no'],
                    'qty' => $item['qty'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Repair item record created successfully!',
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

    // Update repair record
    public function update($return_id, $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'sent_by' => 'sometimes|string',
            'items' => 'nullable|array|min:1',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.batch_no' => 'required_with:items|string',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.reason' => 'nullable|string',
            'items.*.note' => 'nullable|string',
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
                'sent_by' => $request->sent_by ?? $repair->sent_by,
            ]);

            if (!empty($request->items)) {
                RepairItemDetails::where('return_id', $return_id)->delete();

                foreach ($request->items as $item) {
                    RepairItemDetails::create([
                        'return_id' => $repair->return_id,
                        'date' => $repair->date,
                        'sent_by' => $repair->sent_by,
                        'note' => $item['note'] ?? null,
                        'item_name' => $item['item_name'],
                        'batch_no' => $item['batch_no'],
                        'qty' => $item['qty'],
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

    // Delete repair record and details
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
}
