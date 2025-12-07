<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    protected $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function view()
    {
        return $this->poService->getAll();
    }

    public function store(Request $request)
    {
        return $this->poService->create($request);
    }

    public function show($uuid)
    {
        return $this->poService->getByUUID($uuid);
    }

    public function update(Request $request, $uuid)
    {
        return $this->poService->update($request, $uuid);
    }

    public function destroy($uuid)
    {
        return $this->poService->delete($uuid);
    }
}
