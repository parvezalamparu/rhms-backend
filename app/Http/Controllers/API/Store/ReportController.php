<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ReportService;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function generalStockReport(Request $request)
    {
        $filters = $request->only([
            'category',
            'subcategory', 
            'type',
            'company',
            'status',
            'stock_status'
        ]);

        return $this->reportService->getGeneralStockReport($filters);
    }

    public function departmentStockReport(Request $request)
    {
        $department = $request->input('department');
        
        if (empty($department)) {
            return response()->json([
                'message' => 'Department parameter is required.',
            ], 400);
        }

        $filters = $request->only(['from_date', 'to_date']);

        return $this->reportService->getDepartmentStockReport($department, $filters);
    }

    /**
     * Get Requisition Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requisitionReport(Request $request)
    {
        $filters = $request->only(['department', 'status', 'from_date', 'to_date']);

        return $this->reportService->getRequisitionReport($filters);
    }

    /**
     * Get Item Issue Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function itemIssueReport(Request $request)
    {
        $filters = $request->only(['department', 'item_id', 'from_date', 'to_date']);

        return $this->reportService->getItemIssueReport($filters);
    }

    /**
     * Get Item Stock Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function itemStockReport(Request $request)
    {
        $filters = $request->only(['item_id', 'from_date', 'to_date', 'batch_no']);

        return $this->reportService->getItemStockReport($filters);
    }

    /**
     * Get Purchase Order Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseOrderReport(Request $request)
    {
        $filters = $request->only(['vendor', 'status', 'from_date', 'to_date']);

        return $this->reportService->getPurchaseOrderReport($filters);
    }

    /**
     * Get Purchase Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseReport(Request $request)
    {
        $filters = $request->only(['vendor', 'item_id', 'from_date', 'to_date']);

        return $this->reportService->getPurchaseReport($filters);
    }

    /**
     * Get Discard Items Report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function discardItemsReport(Request $request)
    {
        $filters = $request->only(['department', 'item_id', 'from_date', 'to_date']);

        return $this->reportService->getDiscardItemsReport($filters);
    }
}