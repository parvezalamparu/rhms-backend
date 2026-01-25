<?php

namespace App\Services\Store;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    /**
     * Get General Stock Report
     */
    public function getGeneralStockReport($filters = [])
    {
        try {
            Log::info("Starting General Stock Report generation");
            
            $itemsQuery = DB::table('items')
                ->select(
                    'items.id',
                    'items.item_name',
                    'items.item_code',
                    'items.item_category',
                    'items.item_subcategory',
                    'items.item_type',
                    'items.item_unit',
                    'items.item_subunit',
                    'items.company',
                    'items.low_level',
                    'items.high_level',
                    'items.is_active'
                );

            if (!empty($filters['category'])) {
                $itemsQuery->where('items.item_category', $filters['category']);
            }
            if (!empty($filters['subcategory'])) {
                $itemsQuery->where('items.item_subcategory', $filters['subcategory']);
            }
            if (!empty($filters['type'])) {
                $itemsQuery->where('items.item_type', $filters['type']);
            }
            if (!empty($filters['company'])) {
                $itemsQuery->where('items.company', $filters['company']);
            }
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $itemsQuery->where('items.is_active', 1);
                } else if ($filters['status'] === 'inactive') {
                    $itemsQuery->where('items.is_active', 0);
                }
            }

            $items = $itemsQuery->get();
            Log::info("Found {$items->count()} items");

            $reportData = [];

            foreach ($items as $item) {
                try {
                    $totalPurchased = DB::table('purchase_details')
                        ->where('item_id', $item->id)
                        ->sum('qty') ?? 0;

                    $totalIssued = DB::table('issue_item_details')
                        ->where('item_id', $item->id)
                        ->sum('unit_qty') ?? 0;

                    $totalReturned = 0;
                    if (Schema::hasTable('returned_item_details')) {
                        $totalReturned = DB::table('returned_item_details')
                            ->where('item_id', $item->id)
                            ->sum('qty') ?? 0;
                    }

                    $availableQty = floatval($totalPurchased) - floatval($totalIssued) + floatval($totalReturned);

                    $avgRate = DB::table('purchase_details')
                        ->where('item_id', $item->id)
                        ->avg('rate_per_unit') ?? 0;

                    $stockValue = $availableQty * floatval($avgRate);

                    $stockStatus = 'available';
                    if ($availableQty <= 0) {
                        $stockStatus = 'out_of_stock';
                    } else if ($availableQty <= floatval($item->low_level ?? 0)) {
                        $stockStatus = 'low_stock';
                    } else if ($availableQty >= floatval($item->high_level ?? 999999)) {
                        $stockStatus = 'overstock';
                    }

                    $batchCount = DB::table('purchase_details')
                        ->where('item_id', $item->id)
                        ->whereNotNull('batch_no')
                        ->where('batch_no', '!=', '')
                        ->distinct()
                        ->count('batch_no');

                    $reportData[] = [
                        'id' => $item->id,
                        'item_name' => $item->item_name ?? '',
                        'item_code' => $item->item_code ?? '',
                        'category' => $item->item_category ?? '',
                        'subcategory' => $item->item_subcategory ?? '',
                        'type' => $item->item_type ?? '',
                        'unit' => $item->item_unit ?? '',
                        'sub_unit' => $item->item_subunit ?? '',
                        'company' => $item->company ?? '',
                        'total_purchased' => floatval($totalPurchased),
                        'total_issued' => floatval($totalIssued),
                        'total_returned' => floatval($totalReturned),
                        'available_qty' => $availableQty,
                        'low_level' => floatval($item->low_level ?? 0),
                        'high_level' => floatval($item->high_level ?? 0),
                        'avg_rate' => floatval($avgRate),
                        'stock_value' => $stockValue,
                        'stock_status' => $stockStatus,
                        'batch_count' => $batchCount,
                        'is_active' => (bool) ($item->is_active ?? 0),
                    ];
                } catch (\Exception $itemError) {
                    Log::error("Error processing item {$item->id}: " . $itemError->getMessage());
                    continue;
                }
            }

            if (!empty($filters['stock_status'])) {
                $reportData = array_filter($reportData, function($item) use ($filters) {
                    return $item['stock_status'] === $filters['stock_status'];
                });
                $reportData = array_values($reportData);
            }

            $summary = [
                'total_items' => count($reportData),
                'total_stock_value' => array_sum(array_column($reportData, 'stock_value')),
                'out_of_stock_count' => count(array_filter($reportData, fn($i) => $i['stock_status'] === 'out_of_stock')),
                'low_stock_count' => count(array_filter($reportData, fn($i) => $i['stock_status'] === 'low_stock')),
                'overstock_count' => count(array_filter($reportData, fn($i) => $i['stock_status'] === 'overstock')),
                'available_count' => count(array_filter($reportData, fn($i) => $i['stock_status'] === 'available')),
            ];

            Log::info("Report generated successfully with {$summary['total_items']} items");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getGeneralStockReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate stock report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Department Stock Report
     * WITHOUT relying on amount column - calculates from qty instead
     */
    public function getDepartmentStockReport($department, $filters = [])
    {
        try {
            Log::info("Generating Department Stock Report for: {$department}");

            if (empty($department)) {
                return response()->json([
                    'message' => 'Department is required.',
                ], 400);
            }

            // First, get issued items with their quantities
            $issuesQuery = DB::table('issue_item_details')
                ->join('issue_items', 'issue_item_details.issue_no', '=', 'issue_items.issue_no')
                ->join('items', 'issue_item_details.item_id', '=', 'items.id')
                ->leftJoin('purchase_details', function($join) {
                    $join->on('issue_item_details.item_id', '=', 'purchase_details.item_id')
                         ->on('issue_item_details.batch_no', '=', 'purchase_details.batch_no');
                })
                ->select(
                    'issue_item_details.item_id',
                    'items.item_name',
                    'items.item_code',
                    'items.item_unit',
                    'items.item_subunit',
                    DB::raw('SUM(issue_item_details.unit_qty) as total_issued_qty'),
                    DB::raw('SUM(COALESCE(issue_item_details.sub_unit_qty, 0)) as total_issued_sub_qty'),
                    DB::raw('AVG(purchase_details.rate_per_unit) as avg_rate')
                )
                ->where('issue_items.issue_to', $department);

            if (!empty($filters['from_date'])) {
                $issuesQuery->where('issue_items.issue_date', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $issuesQuery->where('issue_items.issue_date', '<=', $filters['to_date']);
            }

            $issuesQuery->groupBy(
                'issue_item_details.item_id',
                'items.item_name',
                'items.item_code',
                'items.item_unit',
                'items.item_subunit'
            );

            $issuedItems = $issuesQuery->get();
            Log::info("Found {$issuedItems->count()} items issued to {$department}");

            $reportData = [];

            foreach ($issuedItems as $item) {
                // Get returned quantity
                $returnedQty = 0;
                
                if (Schema::hasTable('returned_item_details') && Schema::hasTable('returned_items')) {
                    try {
                        $returnedQty = DB::table('returned_item_details')
                            ->join('returned_items', 'returned_item_details.returned_id', '=', 'returned_items.returned_id')
                            ->where('returned_items.department', $department)
                            ->where('returned_item_details.item_id', $item->item_id);

                        if (!empty($filters['from_date'])) {
                            $returnedQty->where('returned_items.returned_date', '>=', $filters['from_date']);
                        }
                        if (!empty($filters['to_date'])) {
                            $returnedQty->where('returned_items.returned_date', '<=', $filters['to_date']);
                        }

                        $returnedQty = $returnedQty->sum('returned_item_details.qty') ?? 0;
                    } catch (\Exception $e) {
                        Log::warning("Error fetching returns: " . $e->getMessage());
                        $returnedQty = 0;
                    }
                }

                $issuedQty = floatval($item->total_issued_qty ?? 0);
                $returnedQty = floatval($returnedQty);
                $netQty = $issuedQty - $returnedQty;

                // Calculate amounts using average rate
                $avgRate = floatval($item->avg_rate ?? 0);
                $issuedAmount = $issuedQty * $avgRate;
                $returnedAmount = $returnedQty * $avgRate;
                $netAmount = $netQty * $avgRate;

                $reportData[] = [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name ?? '',
                    'item_code' => $item->item_code ?? '',
                    'unit' => $item->item_unit ?? '',
                    'sub_unit' => $item->item_subunit ?? '',
                    'issued_qty' => $issuedQty,
                    'issued_sub_qty' => floatval($item->total_issued_sub_qty ?? 0),
                    'returned_qty' => $returnedQty,
                    'net_qty' => $netQty,
                    'issued_amount' => $issuedAmount,
                    'returned_amount' => $returnedAmount,
                    'net_amount' => $netAmount,
                ];
            }

            $summary = [
                'department' => $department,
                'total_items' => count($reportData),
                'total_issued_amount' => array_sum(array_column($reportData, 'issued_amount')),
                'total_returned_amount' => array_sum(array_column($reportData, 'returned_amount')),
                'net_amount' => array_sum(array_column($reportData, 'net_amount')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Department report generated: {$summary['total_items']} items");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getDepartmentStockReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            Log::error("Line: " . $e->getLine());
            
            return response()->json([
                'message' => 'Failed to generate department stock report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get Requisition Report
     * Shows all requisitions with their details
     * 
     * @param array $filters (optional date range, status, department)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequisitionReport($filters = [])
    {
        try {
            Log::info("Generating Requisition Report");
            Log::info("Filters: " . json_encode($filters));

            // Build query for requisitions
            $requisitionsQuery = DB::table('requisitions')
                ->select(
                    'requisitions.id',
                    'requisitions.uuid',
                    'requisitions.requisition_no',
                    'requisitions.department',
                    'requisitions.generated_by',
                    'requisitions.date',
                    'requisitions.status'
                );

            // Apply filters if provided
            if (!empty($filters['department'])) {
                $requisitionsQuery->where('requisitions.department', $filters['department']);
            }
            
            if (!empty($filters['status'])) {
                $requisitionsQuery->where('requisitions.status', $filters['status']);
            }

            if (!empty($filters['from_date'])) {
                $requisitionsQuery->where('requisitions.date', '>=', $filters['from_date']);
            }
            
            if (!empty($filters['to_date'])) {
                $requisitionsQuery->where('requisitions.date', '<=', $filters['to_date']);
            }

            $requisitionsQuery->orderBy('requisitions.date', 'desc');

            $requisitions = $requisitionsQuery->get();
            Log::info("Found {$requisitions->count()} requisitions");

            $reportData = [];

            foreach ($requisitions as $requisition) {
                // Get requisition details (items)
                $details = DB::table('requisition_details')
                    ->join('items', 'requisition_details.item_id', '=', 'items.id')
                    ->where('requisition_details.requisition_no', $requisition->requisition_no)
                    ->select(
                        'items.item_name',
                        'items.item_code',
                        'requisition_details.unit_qty',
                        'requisition_details.unit',
                        'requisition_details.sub_unit_qty',
                        'requisition_details.sub_unit'
                    )
                    ->get();

                // Calculate total quantities
                $totalUnitQty = $details->sum('unit_qty');
                $totalItems = $details->count();

                $reportData[] = [
                    'id' => $requisition->id,
                    'uuid' => $requisition->uuid,
                    'requisition_no' => $requisition->requisition_no,
                    'department' => $requisition->department ?? '',
                    'generated_by' => $requisition->generated_by ?? '',
                    'date' => $requisition->date,
                    'status' => $requisition->status ?? 'Pending',
                    'total_items' => $totalItems,
                    'total_qty' => $totalUnitQty,
                    'items' => $details->map(function($detail) {
                        return [
                            'item_name' => $detail->item_name,
                            'item_code' => $detail->item_code,
                            'unit_qty' => $detail->unit_qty,
                            'unit' => $detail->unit,
                            'sub_unit_qty' => $detail->sub_unit_qty ?? 0,
                            'sub_unit' => $detail->sub_unit ?? '',
                        ];
                    })->toArray()
                ];
            }

            // Calculate summary
            $summary = [
                'total_requisitions' => count($reportData),
                'pending_count' => count(array_filter($reportData, fn($r) => $r['status'] === 'Pending')),
                'approved_count' => count(array_filter($reportData, fn($r) => $r['status'] === 'Approved')),
                'rejected_count' => count(array_filter($reportData, fn($r) => $r['status'] === 'Rejected')),
                'total_items' => array_sum(array_column($reportData, 'total_items')),
                'total_qty' => array_sum(array_column($reportData, 'total_qty')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Requisition report generated: {$summary['total_requisitions']} requisitions");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getRequisitionReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate requisition report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }


    /**
     * Get Item Issue Report
     * Shows all issued items with their details
     * 
     * @param array $filters (optional date range, department, item)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemIssueReport($filters = [])
    {
        try {
            Log::info("Generating Item Issue Report");
            Log::info("Filters: " . json_encode($filters));

            // Build query for issued items
            $issuesQuery = DB::table('issue_item_details')
                ->join('issue_items', 'issue_item_details.issue_no', '=', 'issue_items.issue_no')
                ->join('items', 'issue_item_details.item_id', '=', 'items.id')
                ->leftJoin('purchase_details', function($join) {
                    $join->on('issue_item_details.item_id', '=', 'purchase_details.item_id')
                         ->on('issue_item_details.batch_no', '=', 'purchase_details.batch_no');
                })
                ->select(
                    'issue_items.issue_no',
                    'issue_items.uuid',
                    'issue_items.issue_date',
                    'issue_items.issue_to as department',
                    'issue_items.generated_by',
                    'items.item_name',
                    'items.item_code',
                    'issue_item_details.batch_no',
                    'issue_item_details.exp_date',
                    'issue_item_details.unit_qty',
                    'issue_item_details.unit',
                    'issue_item_details.sub_unit_qty',
                    'issue_item_details.sub_unit',
                    DB::raw('AVG(purchase_details.mrp_per_unit) as mrp_per_unit'),
                    DB::raw('AVG(purchase_details.rate_per_unit) as rate_per_unit')
                );

            // Apply filters
            if (!empty($filters['department'])) {
                $issuesQuery->where('issue_items.issue_to', $filters['department']);
            }

            if (!empty($filters['item_id'])) {
                $issuesQuery->where('issue_item_details.item_id', $filters['item_id']);
            }

            if (!empty($filters['from_date'])) {
                $issuesQuery->where('issue_items.issue_date', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $issuesQuery->where('issue_items.issue_date', '<=', $filters['to_date']);
            }

            $issuesQuery->groupBy(
                'issue_items.issue_no',
                'issue_items.uuid',
                'issue_items.issue_date',
                'issue_items.issue_to',
                'issue_items.generated_by',
                'items.item_name',
                'items.item_code',
                'issue_item_details.batch_no',
                'issue_item_details.exp_date',
                'issue_item_details.unit_qty',
                'issue_item_details.unit',
                'issue_item_details.sub_unit_qty',
                'issue_item_details.sub_unit'
            );

            $issuesQuery->orderBy('issue_items.issue_date', 'desc');

            $issuedItems = $issuesQuery->get();
            Log::info("Found {$issuedItems->count()} issued items");

            $reportData = [];

            foreach ($issuedItems as $item) {
                $unitQty = floatval($item->unit_qty ?? 0);
                $mrpPerUnit = floatval($item->mrp_per_unit ?? 0);
                $ratePerUnit = floatval($item->rate_per_unit ?? 0);

                // Calculate amounts
                $subTotal = $unitQty * $ratePerUnit;
                
                // Calculate GST (assuming 6% CGST + 6% SGST = 12% total)
                $cgst = $subTotal * 0.06;
                $sgst = $subTotal * 0.06;
                $igst = 0; // IGST only for inter-state
                
                $total = $subTotal + $cgst + $sgst + $igst;

                $reportData[] = [
                    'issue_no' => $item->issue_no,
                    'uuid' => $item->uuid,
                    'issue_date' => $item->issue_date,
                    'department' => $item->department ?? '',
                    'generated_by' => $item->generated_by ?? '',
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'batch_no' => $item->batch_no ?? '',
                    'exp_date' => $item->exp_date ?? '',
                    'unit_qty' => $unitQty,
                    'unit' => $item->unit ?? '',
                    'sub_unit_qty' => floatval($item->sub_unit_qty ?? 0),
                    'sub_unit' => $item->sub_unit ?? '',
                    'mrp_per_unit' => $mrpPerUnit,
                    'rate_per_unit' => $ratePerUnit,
                    'sub_total' => $subTotal,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total' => $total,
                ];
            }

            // Calculate summary
            $summary = [
                'total_issues' => count($reportData),
                'total_qty' => array_sum(array_column($reportData, 'unit_qty')),
                'total_sub_total' => array_sum(array_column($reportData, 'sub_total')),
                'total_cgst' => array_sum(array_column($reportData, 'cgst')),
                'total_sgst' => array_sum(array_column($reportData, 'sgst')),
                'total_igst' => array_sum(array_column($reportData, 'igst')),
                'grand_total' => array_sum(array_column($reportData, 'total')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Item issue report generated: {$summary['total_issues']} items, Grand Total: {$summary['grand_total']}");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getItemIssueReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate item issue report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get Item Stock Report
     * Shows purchased items with their stock details
     * 
     * @param array $filters (optional date range, item)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemStockReport($filters = [])
{
    try {
        Log::info("Generating Item Stock Report");
        Log::info("Filters: " . json_encode($filters));

        // Build query for purchase details with stock calculations
        $purchaseQuery = DB::table('purchase_details')
            ->join('items', 'purchase_details.item_id', '=', 'items.id')
            ->join('purchase_lists', 'purchase_details.purchase_no', '=', 'purchase_lists.purchase_no')
            ->leftJoin(DB::raw('(SELECT item_id, batch_no, SUM(unit_qty) as total_issued 
                FROM issue_item_details 
                GROUP BY item_id, batch_no) as issued'), function($join) {
                $join->on('purchase_details.item_id', '=', 'issued.item_id')
                     ->on('purchase_details.batch_no', '=', 'issued.batch_no');
            })
            ->leftJoin(DB::raw('(SELECT item_id, batch_no, SUM(qty) as total_returned 
                FROM returned_item_details 
                WHERE EXISTS(SELECT 1 FROM information_schema.tables WHERE table_name = "returned_item_details")
                GROUP BY item_id, batch_no) as returned'), function($join) {
                $join->on('purchase_details.item_id', '=', 'returned.item_id')
                     ->on('purchase_details.batch_no', '=', 'returned.batch_no');
            })
            ->select(
                'purchase_details.id',
                'items.item_name',
                'items.item_code',
                'purchase_lists.date as purchase_date',
                'purchase_details.exp_date',
                'purchase_lists.generated_by as updated_by',
                'purchase_details.batch_no',
                'purchase_details.qty as purchased_qty',
                DB::raw('COALESCE(issued.total_issued, 0) as issued_qty'),
                DB::raw('COALESCE(returned.total_returned, 0) as returned_qty'),
                DB::raw('purchase_details.qty - COALESCE(issued.total_issued, 0) + COALESCE(returned.total_returned, 0) as current_qty'),
                'purchase_details.mrp_per_unit',
                'purchase_details.rate_per_unit'
            );

        // Apply filters
        if (!empty($filters['item_id'])) {
            $purchaseQuery->where('purchase_details.item_id', $filters['item_id']);
        }

        if (!empty($filters['from_date'])) {
            $purchaseQuery->where('purchase_lists.date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $purchaseQuery->where('purchase_lists.date', '<=', $filters['to_date']);
        }

        if (!empty($filters['batch_no'])) {
            $purchaseQuery->where('purchase_details.batch_no', 'LIKE', '%' . $filters['batch_no'] . '%');
        }

        $purchaseQuery->orderBy('purchase_lists.date', 'desc');

        $purchaseLists = $purchaseQuery->get();
        Log::info("Found {$purchaseLists->count()} purchase items");

        $reportData = [];
        $totalItems = 0;
        $totalCurrentQty = 0;
        $totalPurchasedQty = 0;
        $totalIssuedQty = 0;
        $totalReturnedQty = 0;

        foreach ($purchaseLists as $purchaseList) {
            $purchasedQty = floatval($purchaseList->purchased_qty ?? 0);
            $issuedQty = floatval($purchaseList->issued_qty ?? 0);
            $returnedQty = floatval($purchaseList->returned_qty ?? 0);
            $currentQty = floatval($purchaseList->current_qty ?? 0);
            $mrpPerUnit = floatval($purchaseList->mrp_per_unit ?? 0);
            $ratePerUnit = floatval($purchaseList->rate_per_unit ?? 0);

            // Calculate amounts based on CURRENT quantity (not purchased)
            $subTotal = $currentQty * $ratePerUnit;
            
            // Calculate GST (6% CGST + 6% SGST = 12% total)
            $cgst = $subTotal * 0.06;
            $sgst = $subTotal * 0.06;
            $igst = 0;
            
            $total = $subTotal + $cgst + $sgst + $igst;

            $reportData[] = [
                'id' => $purchaseList->id,
                'item_name' => $purchaseList->item_name,
                'item_code' => $purchaseList->item_code,
                'purchase_date' => $purchaseList->purchase_date,
                'exp_date' => $purchaseList->exp_date ?? '',
                'updated_by' => $purchaseList->updated_by ?? '',
                'batch_no' => $purchaseList->batch_no ?? '',
                'purchased_qty' => $purchasedQty,
                'issued_qty' => $issuedQty,
                'returned_qty' => $returnedQty,
                'current_qty' => $currentQty,
                'mrp_per_unit' => $mrpPerUnit,
                'rate_per_unit' => $ratePerUnit,
                'sub_total' => $subTotal,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'total' => $total,
            ];

            $totalItems++;
            $totalCurrentQty += $currentQty;
            $totalPurchasedQty += $purchasedQty;
            $totalIssuedQty += $issuedQty;
            $totalReturnedQty += $returnedQty;
        }

        // Calculate summary
        $summary = [
            'total_records' => $totalItems,
            'total_current_qty' => $totalCurrentQty,
            'total_purchased_qty' => $totalPurchasedQty,
            'total_issued_qty' => $totalIssuedQty,
            'total_returned_qty' => $totalReturnedQty,
            'total_sub_total' => array_sum(array_column($reportData, 'sub_total')),
            'total_cgst' => array_sum(array_column($reportData, 'cgst')),
            'total_sgst' => array_sum(array_column($reportData, 'sgst')),
            'total_igst' => array_sum(array_column($reportData, 'igst')),
            'grand_total' => array_sum(array_column($reportData, 'total')),
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
        ];

        Log::info("Item stock report generated: {$summary['total_records']} items, Grand Total: {$summary['grand_total']}");

        return response()->json([
            'data' => $reportData,
            'summary' => $summary,
        ], 200);

    } catch (\Exception $e) {
        Log::error("Error in getItemStockReport: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        
        return response()->json([
            'message' => 'Failed to generate item stock report.',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}

    /**
     * Get Purchase Order Report
     * Shows all purchase orders with their item details
     * 
     * @param array $filters (optional date range, supplier, status)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseOrderReport($filters = [])
    {
        try {
            Log::info("Generating Purchase Order Report");
            Log::info("Filters: " . json_encode($filters));

            // Build query for purchase orders - using correct table name
            $poQuery = DB::table('purchase_orders')
                ->join('purchase_order_details', 'purchase_orders.po_no', '=', 'purchase_order_details.po_no')
                ->join('items', 'purchase_order_details.item_id', '=', 'items.id')
                ->select(
                    'purchase_orders.id',
                    'purchase_orders.uuid',
                    'purchase_orders.po_no',
                    'purchase_orders.vendor',
                    'purchase_orders.date as date',
                    'purchase_orders.generated_by',
                    'items.item_name',
                    'items.item_code',
                    'purchase_order_details.unit_qty',
                    'purchase_order_details.unit',
                    'purchase_order_details.sub_unit_qty',
                    'purchase_order_details.sub_unit'
                );

            // Apply filters
            if (!empty($filters['vendor'])) {
                $poQuery->where('purchase_orders.vendor', 'LIKE', '%' . $filters['vendor'] . '%');
            }

            if (!empty($filters['from_date'])) {
                $poQuery->where('purchase_orders.date', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $poQuery->where('purchase_orders.date', '<=', $filters['to_date']);
            }

            $poQuery->orderBy('purchase_orders.date', 'desc');

            $poItems = $poQuery->get();
            Log::info("Found {$poItems->count()} purchase order items");

            $reportData = $poItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'uuid' => $item->uuid,
                    'po_no' => $item->po_no,
                    'vendor' => $item->vendor ?? '',
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'date' => $item->date,
                    'generated_by' => $item->generated_by ?? '',
                    'unit_qty' => floatval($item->unit_qty ?? 0),
                    'unit' => $item->unit ?? '',
                    'sub_unit_qty' => floatval($item->sub_unit_qty ?? 0),
                    'sub_unit' => $item->sub_unit ?? '',
                    'total_qty' => floatval($item->unit_qty ?? 0) + floatval($item->sub_unit_qty ?? 0),
                ];
            })->toArray();

            // Calculate summary
            $summary = [
                'total_pos' => DB::table('purchase_orders')
                    ->when(!empty($filters['from_date']), fn($q) => $q->where('date', '>=', $filters['from_date']))
                    ->when(!empty($filters['to_date']), fn($q) => $q->where('date', '<=', $filters['to_date']))
                    ->count(),
                'total_items' => count($reportData),
                'total_qty' => array_sum(array_column($reportData, 'unit_qty')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Purchase order report generated: {$summary['total_items']} items");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getPurchaseOrderReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate purchase order report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get Purchase Report
     * Shows all purchases with item details and financial calculations
     * 
     * @param array $filters (optional date range, vendor, item)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseReport($filters = [])
    {
        try {
            Log::info("Generating Purchase Report");
            Log::info("Filters: " . json_encode($filters));

            // Build query - join purchase_details with purchase_lists and items
            $purchaseQuery = DB::table('purchase_details')
                ->join('purchase_lists', 'purchase_details.purchase_no', '=', 'purchase_lists.purchase_no')
                ->join('items', 'purchase_details.item_id', '=', 'items.id')
                ->select(
                    'purchase_details.id',
                    'purchase_details.uuid',
                    'purchase_details.purchase_no',
                    'purchase_lists.vendor',
                    'purchase_lists.generated_by',
                    'purchase_lists.date as purchase_date',
                    'items.item_name',
                    'items.item_code',
                    'purchase_details.batch_no',
                    'purchase_details.qty',
                    'purchase_details.unit',
                    'purchase_details.mrp_per_unit',
                    'purchase_details.rate_per_unit',
                    'purchase_details.cgst_percent',
                    'purchase_details.sgst_percent',
                    'purchase_details.igst_percent',
                    'purchase_details.amount'
                );

            // Apply filters
            if (!empty($filters['vendor'])) {
                $purchaseQuery->where('purchase_lists.vendor', 'LIKE', '%' . $filters['vendor'] . '%');
            }

            if (!empty($filters['item_id'])) {
                $purchaseQuery->where('purchase_details.item_id', $filters['item_id']);
            }

            if (!empty($filters['from_date'])) {
                $purchaseQuery->where('purchase_lists.date', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $purchaseQuery->where('purchase_lists.date', '<=', $filters['to_date']);
            }

            $purchaseQuery->orderBy('purchase_lists.date', 'desc');

            $purchases = $purchaseQuery->get();
            Log::info("Found {$purchases->count()} purchase items");

            $reportData = [];

            foreach ($purchases as $purchase) {
                $qty = floatval($purchase->qty ?? 0);
                $mrpPerUnit = floatval($purchase->mrp_per_unit ?? 0);
                $ratePerUnit = floatval($purchase->rate_per_unit ?? 0);
                
                // Calculate Sub Total
                $subTotal = $qty * $ratePerUnit;
                
                // Calculate GST
                $cgstPercent = floatval($purchase->cgst_percent ?? 0);
                $sgstPercent = floatval($purchase->sgst_percent ?? 0);
                $igstPercent = floatval($purchase->igst_percent ?? 0);
                
                $cgst = ($subTotal * $cgstPercent) / 100;
                $sgst = ($subTotal * $sgstPercent) / 100;
                $igst = ($subTotal * $igstPercent) / 100;
                
                $total = $subTotal + $cgst + $sgst + $igst;

                $reportData[] = [
                    'id' => $purchase->id,
                    'uuid' => $purchase->uuid,
                    'purchase_no' => $purchase->purchase_no,
                    'vendor' => $purchase->vendor ?? '',
                    'purchase_date' => $purchase->purchase_date,
                    'generated_by' => $purchase->generated_by ?? '',
                    'item_name' => $purchase->item_name,
                    'item_code' => $purchase->item_code,
                    'batch_no' => $purchase->batch_no ?? '',
                    'qty' => $qty,
                    'unit' => $purchase->unit ?? '',
                    'mrp_per_unit' => $mrpPerUnit,
                    'rate_per_unit' => $ratePerUnit,
                    'sub_total' => $subTotal,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total' => $total,
                ];
            }

            // Calculate summary
            $summary = [
                'total_purchases' => DB::table('purchase_lists')
                    ->when(!empty($filters['from_date']), fn($q) => $q->where('date', '>=', $filters['from_date']))
                    ->when(!empty($filters['to_date']), fn($q) => $q->where('date', '<=', $filters['to_date']))
                    ->count(),
                'total_items' => count($reportData),
                'total_qty' => array_sum(array_column($reportData, 'qty')),
                'total_sub_total' => array_sum(array_column($reportData, 'sub_total')),
                'total_cgst' => array_sum(array_column($reportData, 'cgst')),
                'total_sgst' => array_sum(array_column($reportData, 'sgst')),
                'total_igst' => array_sum(array_column($reportData, 'igst')),
                'grand_total' => array_sum(array_column($reportData, 'total')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Purchase report generated: {$summary['total_items']} items, Grand Total: {$summary['grand_total']}");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getPurchaseReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate purchase report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get Discard Items Report
     * Shows all discarded items with details
     * 
     * @param array $filters (optional date range, item, department)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDiscardItemsReport($filters = [])
    {
        try {
            Log::info("Generating Discard Items Report");
            Log::info("Filters: " . json_encode($filters));

            // Build query - using discard_items table
            $discardQuery = DB::table('discard_items')
                ->join('items', 'discard_items.item_id', '=', 'items.id')
                ->select(
                    'discard_items.id',
                    'discard_items.uuid',
                    'discard_items.return_id',
                    'items.item_name',
                    'items.item_code',
                    'discard_items.batch_no',
                    'discard_items.returned_department',
                    'discard_items.return_by',
                    'discard_items.discarded_by',
                    'discard_items.discarded_reason',
                    'discard_items.qty',
                    'discard_items.unit',
                    'discard_items.created_at as discard_date'
                );

            // Apply filters
            if (!empty($filters['department'])) {
                $discardQuery->where('discard_items.returned_department', 'LIKE', '%' . $filters['department'] . '%');
            }

            if (!empty($filters['item_id'])) {
                $discardQuery->where('discard_items.item_id', $filters['item_id']);
            }

            if (!empty($filters['from_date'])) {
                $discardQuery->whereDate('discard_items.created_at', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $discardQuery->whereDate('discard_items.created_at', '<=', $filters['to_date']);
            }

            $discardQuery->orderBy('discard_items.created_at', 'desc');

            $discards = $discardQuery->get();
            Log::info("Found {$discards->count()} discarded items");

            $reportData = $discards->map(function($discard) {
                return [
                    'id' => $discard->id,
                    'uuid' => $discard->uuid,
                    'return_id' => $discard->return_id ?? '',
                    'item_name' => $discard->item_name,
                    'item_code' => $discard->item_code,
                    'batch_no' => $discard->batch_no ?? '',
                    'returned_department' => $discard->returned_department ?? '',
                    'return_by' => $discard->return_by ?? '',
                    'discarded_by' => $discard->discarded_by ?? '',
                    'discarded_reason' => $discard->discarded_reason ?? '',
                    'qty' => floatval($discard->qty ?? 0),
                    'unit' => $discard->unit ?? '',
                    'discard_date' => $discard->discard_date ? date('Y-m-d', strtotime($discard->discard_date)) : '',
                ];
            })->toArray();

            // Calculate summary
            $summary = [
                'total_discards' => count($reportData),
                'total_qty' => array_sum(array_column($reportData, 'qty')),
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
            ];

            Log::info("Discard items report generated: {$summary['total_discards']} items discarded");

            return response()->json([
                'data' => $reportData,
                'summary' => $summary,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error in getDiscardItemsReport: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate discard items report.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}

