<?php

namespace App\Services\Store;

use App\Models\Store\PurchaseOrder;
use App\Models\Store\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderService
{
    // Get all purchase orders with details count
    public function getAll()
    {
        $orders = PurchaseOrder::withCount('details')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $orders], 200);
    }

    // Create a new purchase order with multiple items
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'vendor' => 'required|string|max:255',
            'generated_by' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'nullable|in:pending,approved',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit_qty' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.sub_unit_qty' => 'nullable|integer|min:0',
            'items.*.sub_unit' => 'nullable|string|max:50',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create([
                'vendor' => $validator->validated()['vendor'],
                'generated_by' => $validator->validated()['generated_by'],
                'date' => $validator->validated()['date'],
                'status' => $validator->validated()['status'] ?? 'pending',
            ]);

            foreach ($validator->validated()['items'] as $item) {
                PurchaseOrderDetail::create([
                    'po_no' => $po->po_no,
                    'vendor' => $po->vendor,
                    'generated_by' => $po->generated_by,
                    'date' => $po->date,
                    'note' => $item['note'] ?? null,
                    'item_name' => $item['item_name'],
                    'unit_qty' => $item['unit_qty'],
                    'unit' => $item['unit'],
                    'sub_unit_qty' => $item['sub_unit_qty'] ?? null,
                    'sub_unit' => $item['sub_unit'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Purchase Order created successfully!',
                'data' => $po->load('details'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create Purchase Order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get single purchase order by po_no
    public function getByUUID($uuid)
    {
        $order = PurchaseOrder::with('details')->where('uuid', $uuid)->first();
        if (!$order) {
            return response()->json(['message' => 'Purchase Order not found.'], 404);
        }
        return response()->json(['data' => $order], 200);
    }

    // Update a purchase order and its items
    public function update(Request $request, $uuid)
{
    $validator = Validator::make($request->all(), [
        'vendor' => 'sometimes|string|max:255',
        'generated_by' => 'sometimes|string|max:255',
        'date' => 'sometimes|date',
        'status' => 'nullable|in:pending,approved',
        'items' => 'nullable|array|min:1',
        'items.*.item_name' => 'required_with:items|string|max:255',
        'items.*.unit_qty' => 'required_with:items|integer|min:1',
        'items.*.unit' => 'required_with:items|string|max:50',
        'items.*.sub_unit_qty' => 'nullable|integer|min:0',
        'items.*.sub_unit' => 'nullable|string|max:50',
        'items.*.note' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();

    try {
        $order = PurchaseOrder::where('uuid', $uuid)->first();

        if (!$order) {
            return response()->json(['message' => 'Purchase Order not found.'], 404);
        }

        $data = $validator->validated();

        $order->update([
            'vendor' => $data['vendor'] ?? $order->vendor,
            'generated_by' => $data['generated_by'] ?? $order->generated_by,
            'date' => $data['date'] ?? $order->date,
            'status' => $data['status'] ?? $order->status,
        ]);

        // Use order's po_no
        $po_no = $order->po_no;

        if (!empty($data['items'])) {
            // Delete previous details
            PurchaseOrderDetail::where('po_no', $po_no)->delete();

            // Add new items
            foreach ($data['items'] as $item) {
                PurchaseOrderDetail::create([
                    'po_no' => $po_no,
                    'vendor' => $order->vendor,
                    'generated_by' => $order->generated_by,
                    'date' => $order->date,
                    'note' => $item['note'] ?? null,
                    'item_name' => $item['item_name'],
                    'unit_qty' => $item['unit_qty'],
                    'unit' => $item['unit'],
                    'sub_unit_qty' => $item['sub_unit_qty'] ?? null,
                    'sub_unit' => $item['sub_unit'] ?? null,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Purchase Order updated successfully!',
            'data' => $order->load('details'),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to update Purchase Order.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Delete a purchase order and its items
    public function delete($uuid)
    {
        DB::beginTransaction();
        try {
            $order = PurchaseOrder::where('uuid', $uuid)->first();
            if (!$order) {
                return response()->json(['message' => 'Purchase Order not found.'], 404);
            }

            PurchaseOrderDetail::where('uuid', $uuid)->delete();
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Purchase Order deleted successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete Purchase Order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
