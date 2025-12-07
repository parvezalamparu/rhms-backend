<?php

namespace App\Services\Store;

use App\Models\Store\Requisition;
use App\Models\Store\RequisitionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequisitionService
{
    public function getAll()
    {
        return response()->json([
            'data' => Requisition::withCount('details')->get(),
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'generated_by' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'nullable|in:pending,approved',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.req_qty' => 'required|string|min:1',
            'items.*.unit_qty' => 'nullable|integer|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.sub_unit_qty' => 'nullable|integer|min:0',
            'items.*.sub_unit' => 'nullable|string|max:50',
            'items.*.issued_unit' => 'nullable|integer|min:0',
            'items.*.total' => 'nullable|string|max:255',
            'items.*.relation' => 'nullable|string|max:255',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create requisition - requisition_no is auto-generated in Model's boot method
            $requisition = Requisition::create([
                'generated_by' => $validated['generated_by'],
                'department' => $validated['department'],
                'date' => $validated['date'],
                'status' => $validated['status'] ?? 'pending',
            ]);

            // Create requisition details
            foreach ($validated['items'] as $item) {
                RequisitionDetail::create([
                    'requisition_no' => $requisition->requisition_no,
                    'item_id' => $item['item_id'],
                    'req_qty' => $item['req_qty'],
                    'unit_qty' => $item['unit_qty'] ?? 0,
                    'unit' => $item['unit'] ?? null,
                    'sub_unit_qty' => $item['sub_unit_qty'] ?? 0,
                    'sub_unit' => $item['sub_unit'] ?? null,
                    'issued_unit' => $item['issued_unit'] ?? 0,
                    'relation' => $item['relation'] ?? null,
                    'total' => $item['total'] ?? null,
                    'note' => $item['note'] ?? null,
                    'status' => 'pending',
                    'generated_by' => $validated['generated_by'],
                    'department' => $validated['department'],
                    'date' => $validated['date'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Requisition created successfully.',
                'data' => $requisition->load('details'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create requisition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'generated_by' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'nullable|in:pending,approved',
    
            'items' => 'required|array|min:1',
            'items.*.uuid' => 'nullable|string',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.req_qty' => 'required|string|min:1',
            'items.*.issued_unit' => 'nullable|integer|min:0',
            'items.*.unit_qty' => 'nullable|integer|min:0',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.sub_unit_qty' => 'nullable|integer|min:0',
            'items.*.sub_unit' => 'nullable|string|max:50',
            'items.*.total' => 'nullable|string|max:255',
            'items.*.relation' => 'nullable|string|max:255',
            'items.*.note' => 'nullable|string|max:255',
        ]);
    
        DB::beginTransaction();
    
        try {
            $requisition = Requisition::where('uuid', $uuid)->first();
    
            if (!$requisition) {
                return response()->json(['message' => 'Requisition not found'], 404);
            }
    
            // Update requisition header
            $requisition->update([
                'generated_by' => $validated['generated_by'],
                'department' => $validated['department'],
                'date' => $validated['date'],
                'status' => $validated['status'] ?? 'pending',
            ]);
    
            // Track existing detail UUIDs from request
            $incomingUUIDs = collect($validated['items'])->pluck('uuid')->filter();
    
            // Delete details that are no longer in the request
            RequisitionDetail::where('requisition_no', $requisition->requisition_no)
                ->whereNotIn('uuid', $incomingUUIDs)
                ->delete();
    
            // Insert or update items
            foreach ($validated['items'] as $item) {
                if (!empty($item['uuid'])) {
                    // Update existing detail
                    RequisitionDetail::where('uuid', $item['uuid'])->update([
                        'item_id' => $item['item_id'],
                        'req_qty' => $item['req_qty'],
                        'unit_qty' => $item['unit_qty'] ?? 0,
                        'unit' => $item['unit'] ?? null,
                        'sub_unit_qty' => $item['sub_unit_qty'] ?? 0,
                        'sub_unit' => $item['sub_unit'] ?? null,
                        'issued_unit' => $item['issued_unit'] ?? 0,
                        'total' => $item['total'] ?? null,
                        'relation' => $item['relation'] ?? null,
                        'note' => $item['note'] ?? null,
                    ]);
                } else {
                    // Create new detail
                    RequisitionDetail::create([
                        'requisition_no' => $requisition->requisition_no,
                        'item_id' => $item['item_id'],
                        'req_qty' => $item['req_qty'],
                        'unit_qty' => $item['unit_qty'] ?? 0,
                        'unit' => $item['unit'] ?? null,
                        'sub_unit_qty' => $item['sub_unit_qty'] ?? 0,
                        'sub_unit' => $item['sub_unit'] ?? null,
                        'issued_unit' => $item['issued_unit'] ?? 0,
                        'total' => $item['total'] ?? null,
                        'relation' => $item['relation'] ?? null,
                        'note' => $item['note'] ?? null,
                        'status' => 'pending',
                        'generated_by' => $validated['generated_by'],
                        'department' => $validated['department'],
                        'date' => $validated['date'],
                    ]);
                    
                }
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Requisition updated successfully.',
                'data' => $requisition->load('details'),
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Failed to update requisition.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($uuid)
    {
        $requisition = Requisition::with('details')
            ->where('uuid', $uuid)
            ->first();

        if (!$requisition) {
            return response()->json(['message' => 'Requisition not found'], 404);
        }

        return response()->json(['data' => $requisition], 200);
    }

    public function updateStatus(Request $request, $requisition_no)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved',
        ]);

        $requisition = Requisition::where('requisition_no', $requisition_no)->first();

        if (!$requisition) {
            return response()->json(['message' => 'Requisition not found'], 404);
        }

        $requisition->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Requisition status updated successfully.',
            'data' => $requisition,
        ], 200);
    }

    public function destroy($requisition_no)
    {
        $requisition = Requisition::where('requisition_no', $requisition_no)->first();

        if (!$requisition) {
            return response()->json(['message' => 'Requisition not found'], 404);
        }

        $requisition->delete();

        return response()->json([
            'message' => 'Requisition deleted successfully.',
        ], 200);
    }
}