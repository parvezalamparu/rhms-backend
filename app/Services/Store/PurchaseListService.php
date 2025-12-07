<?php

namespace App\Services\Store;

use App\Models\Store\PurchaseList;
use App\Models\Store\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseListService
{
    /**
     * Get all purchase lists with item count
     */
    public function getAll()
    {
        $purchases = PurchaseList::withCount('details')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $purchases], 200);
    }

    /**
     * Create Purchase List with Items
     */
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'required|string|max:255',
            'requisition_no' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'vendor' => 'required|string|max:255',
            'generated_by' => 'required|string|max:255',
            'date' => 'required|date',
            'payment_terms' => 'nullable|string|max:255',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id', // Changed to item_id
            'items.*.batch_no' => 'nullable|string|max:255',
            'items.*.exp_date' => 'nullable|date',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.test_qty' => 'nullable|integer|min:0',
            'items.*.rate_per_unit' => 'nullable|numeric|min:0',
            'items.*.discount_rs' => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0',
            'items.*.mrp_per_unit' => 'nullable|numeric|min:0',
            'items.*.cgst_percent' => 'nullable|numeric|min:0',
            'items.*.sgst_percent' => 'nullable|numeric|min:0',
            'items.*.igst_percent' => 'nullable|numeric|min:0',
            'items.*.total_gst_amount' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $purchase = PurchaseList::create([
                'invoice_no' => $validated['invoice_no'],
                'requisition_no' => $validated['requisition_no'] ?? null,
                'po_no' => $validated['po_no'] ?? null,
                'vendor' => $validated['vendor'],
                'generated_by' => $validated['generated_by'],
                'date' => $validated['date'],
                'payment_terms' => $validated['payment_terms'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseDetail::create([
                    'purchase_no' => $purchase->purchase_no,
                    'purchase_date' => $purchase->date,
                    'generated_by' => $purchase->generated_by,
                    'vendor' => $purchase->vendor,
                    'payment_terms' => $purchase->payment_terms,

                    'item_id' => $item['item_id'], // Save only item_id
                    'batch_no' => $item['batch_no'] ?? null,
                    'exp_date' => $item['exp_date'] ?? null,
                    'qty' => $item['qty'],
                    'test_qty' => $item['test_qty'] ?? 0,
                    'rate_per_unit' => $item['rate_per_unit'] ?? 0,
                    'discount_rs' => $item['discount_rs'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'mrp_per_unit' => $item['mrp_per_unit'] ?? 0,
                    'cgst_percent' => $item['cgst_percent'] ?? 0,
                    'sgst_percent' => $item['sgst_percent'] ?? 0,
                    'igst_percent' => $item['igst_percent'] ?? 0,
                    'total_gst_amount' => $item['total_gst_amount'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                    'note' => $item['note'] ?? null,
                ]);
            }

            DB::commit();

            // Load with item details to show item_name to user
            return response()->json([
                'message' => 'Purchase created successfully!',
                'data' => $purchase->load('details.item'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update purchase
     */
    public function update($request, $purchase_no)
    {
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'required|string|max:255',
            'requisition_no' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'vendor' => 'required|string|max:255',
            'generated_by' => 'required|string|max:255',
            'date' => 'required|date',
            'payment_terms' => 'nullable|string|max:255',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id', // Changed to item_id
            'items.*.batch_no' => 'nullable|string|max:255',
            'items.*.exp_date' => 'nullable|date',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.test_qty' => 'nullable|integer|min:0',
            'items.*.rate_per_unit' => 'nullable|numeric|min:0',
            'items.*.discount_rs' => 'nullable|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0',
            'items.*.mrp_per_unit' => 'nullable|numeric|min:0',
            'items.*.cgst_percent' => 'nullable|numeric|min:0',
            'items.*.sgst_percent' => 'nullable|numeric|min:0',
            'items.*.igst_percent' => 'nullable|numeric|min:0',
            'items.*.total_gst_amount' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $purchase = PurchaseList::where('purchase_no', $purchase_no)->first();

            if (!$purchase) {
                return response()->json(['message' => 'Purchase not found.'], 404);
            }

            // Update purchase header
            $purchase->update([
                'invoice_no' => $validated['invoice_no'],
                'requisition_no' => $validated['requisition_no'] ?? null,
                'po_no' => $validated['po_no'] ?? null,
                'vendor' => $validated['vendor'],
                'generated_by' => $validated['generated_by'],
                'date' => $validated['date'],
                'payment_terms' => $validated['payment_terms'] ?? null,
            ]);

            // Delete previous items
            PurchaseDetail::where('purchase_no', $purchase_no)->delete();

            // Insert new items
            foreach ($validated['items'] as $item) {
                PurchaseDetail::create([
                    'purchase_no' => $purchase->purchase_no,
                    'purchase_date' => $purchase->date,
                    'generated_by' => $purchase->generated_by,
                    'vendor' => $purchase->vendor,
                    'payment_terms' => $purchase->payment_terms,

                    'item_id' => $item['item_id'], // Save only item_id
                    'batch_no' => $item['batch_no'] ?? null,
                    'exp_date' => $item['exp_date'] ?? null,
                    'qty' => $item['qty'],
                    'test_qty' => $item['test_qty'] ?? 0,
                    'rate_per_unit' => $item['rate_per_unit'] ?? 0,
                    'discount_rs' => $item['discount_rs'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'mrp_per_unit' => $item['mrp_per_unit'] ?? 0,
                    'cgst_percent' => $item['cgst_percent'] ?? 0,
                    'sgst_percent' => $item['sgst_percent'] ?? 0,
                    'igst_percent' => $item['igst_percent'] ?? 0,
                    'total_gst_amount' => $item['total_gst_amount'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                    'note' => $item['note'] ?? null,
                ]);
            }

            DB::commit();

            // Load with item details to show item_name to user
            return response()->json([
                'message' => 'Purchase updated successfully!',
                'data' => $purchase->load('details.item'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update purchase.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get purchase list by Purchase No
     */
    public function getByPurchaseNo($purchase_no)
    {
        // Load with item details to show item_name to user
        $purchase = PurchaseList::with('details.item')
            ->where('purchase_no', $purchase_no)
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found.'], 404);
        }

        return response()->json(['data' => $purchase], 200);
    }

    /**
     * Delete Purchase and its items
     */
    public function delete($purchase_no)
    {
        DB::beginTransaction();

        try {
            $purchase = PurchaseList::where('purchase_no', $purchase_no)->first();

            if (!$purchase) {
                return response()->json(['message' => 'Purchase not found.'], 404);
            }

            PurchaseDetail::where('purchase_no', $purchase_no)->delete();
            $purchase->delete();

            DB::commit();

            return response()->json(['message' => 'Purchase deleted successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete purchase.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}