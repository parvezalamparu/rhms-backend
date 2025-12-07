<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\PurchaseListService;

class PurchaseListController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseListService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function view()
    {
        return $this->purchaseService->getAll();
    }

    public function store(Request $request)
    {
        return $this->purchaseService->create($request);
    }

    public function update(Request $request, $purchase_no)
    {
        return $this->purchaseService->update($request, $purchase_no);
    }

    public function show($purchase_no)
    {
        return $this->purchaseService->getByPurchaseNo($purchase_no);
    }

    public function destroy($purchase_no)
    {
        return $this->purchaseService->delete($purchase_no);
    }
}
