<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\RequisitionService;

class RequisitionController extends Controller
{
    protected $requisitionService;

    public function __construct(RequisitionService $requisitionService)
    {
        $this->requisitionService = $requisitionService;
    }

    // View all requisitions
    public function index()
    {
        return $this->requisitionService->getAll();
    }

    // Store a new requisition
    public function store(Request $request)
    {
        return $this->requisitionService->store($request);
    }

    // Show single requisition with details
    public function show($uuid)
    {
        return $this->requisitionService->show($uuid);
    }


    // update
    public function update(Request $request, $uuid)
    {
        return $this->requisitionService->update($request, $uuid);
    }

    // Update requisition status
    public function updateStatus(Request $request, $requisition_no)
    {
        return $this->requisitionService->updateStatus($request, $requisition_no);
    }

    // Delete requisition
    public function destroy($requisition_no)
    {
        return $this->requisitionService->destroy($requisition_no);
    }
}
