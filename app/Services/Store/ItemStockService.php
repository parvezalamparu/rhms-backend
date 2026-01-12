<?php

namespace App\Services\Store;

use Illuminate\Support\Facades\DB;

class ItemStockService
{
    /**
     * Get available batches for a specific item
     * 
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableBatches($itemId)
    {
        try {
            // Get all purchases for this item grouped by batch
            $purchases = DB::table('purchase_details')
                ->select(
                    'batch_no',
                    'exp_date',
                    DB::raw('SUM(qty) as total_purchased')
                )
                ->where('item_id', $itemId)
                ->whereNotNull('batch_no')
                ->where('batch_no', '!=', '')
                ->groupBy('batch_no', 'exp_date')
                ->get();

            // Get all issues for this item grouped by batch
            $issues = DB::table('issue_details')
                ->select(
                    'batch_no',
                    DB::raw('SUM(unit_qty) as total_issued')
                )
                ->where('item_id', $itemId)
                ->whereNotNull('batch_no')
                ->where('batch_no', '!=', '')
                ->groupBy('batch_no')
                ->get()
                ->keyBy('batch_no');

            // Calculate available quantity for each batch
            $availableBatches = [];

            foreach ($purchases as $purchase) {
                $batchNo = $purchase->batch_no;
                $purchased = floatval($purchase->total_purchased);
                $issued = isset($issues[$batchNo]) ? floatval($issues[$batchNo]->total_issued) : 0;
                $available = $purchased - $issued;

                // Only include batches with available quantity > 0
                if ($available > 0) {
                    $availableBatches[] = [
                        'batch_no' => $batchNo,
                        'exp_date' => $purchase->exp_date,
                        'available_qty' => $available,
                        'total_purchased' => $purchased,
                        'total_issued' => $issued,
                    ];
                }
            }

            // Sort by expiry date (oldest first - FIFO)
            usort($availableBatches, function ($a, $b) {
                return strtotime($a['exp_date']) - strtotime($b['exp_date']);
            });

            return response()->json([
                'data' => $availableBatches,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available batches.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed stock information for a specific item and batch
     * 
     * @param int $itemId
     * @param string $batchNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBatchDetails($itemId, $batchNo)
    {
        try {
            // Get purchase information for this batch
            $purchaseInfo = DB::table('purchase_details')
                ->select(
                    'batch_no',
                    'exp_date',
                    'mrp_per_unit',
                    'rate_per_unit',
                    DB::raw('SUM(qty) as total_purchased')
                )
                ->where('item_id', $itemId)
                ->where('batch_no', $batchNo)
                ->groupBy('batch_no', 'exp_date', 'mrp_per_unit', 'rate_per_unit')
                ->first();

            if (!$purchaseInfo) {
                return response()->json([
                    'message' => 'Batch not found.',
                ], 404);
            }

            // Get total issued for this batch
            $totalIssued = DB::table('issue_details')
                ->where('item_id', $itemId)
                ->where('batch_no', $batchNo)
                ->sum('unit_qty');

            $available = floatval($purchaseInfo->total_purchased) - floatval($totalIssued);

            return response()->json([
                'data' => [
                    'batch_no' => $purchaseInfo->batch_no,
                    'exp_date' => $purchaseInfo->exp_date,
                    'available_qty' => $available,
                    'total_purchased' => floatval($purchaseInfo->total_purchased),
                    'total_issued' => floatval($totalIssued),
                    'mrp_per_unit' => floatval($purchaseInfo->mrp_per_unit),
                    'rate_per_unit' => floatval($purchaseInfo->rate_per_unit),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch batch details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}