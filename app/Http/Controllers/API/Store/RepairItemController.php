<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\RepairItemService;

class RepairItemController extends Controller
{
    protected $service;

    public function __construct(RepairItemService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->getAll();
    }

    public function show($return_id)
    {
        return $this->service->getById($return_id);
    }

    public function store(Request $request)
    {
        return $this->service->create($request);
    }

    public function update(Request $request, $return_id)
    {
        return $this->service->update($return_id, $request);
    }

    public function destroy($return_id)
    {
        return $this->service->delete($return_id);
    }

    public function discardRepairItem($return_id)
    {
        $service = new RepairItemService();
        return $service->discardRepairItem($return_id);
    }
}
