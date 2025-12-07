<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ReturnedItemService;

class ReturnedItemController extends Controller
{
    protected $service;

    public function __construct(ReturnedItemService $service)
    {
        $this->service = $service;
    }

    // View all returned items (summary)
    public function index()
    {
        return $this->service->getAll();
    }

    // View single returned item with details
    public function show($returned_id)
    {
        return $this->service->getById($returned_id);
    }

    // Store new returned item with details
    public function store(Request $request)
    {
        return $this->service->create($request);
    }

    // Update returned item
    public function update(Request $request, $returned_id)
    {
        return $this->service->update($returned_id, $request);
    }

    // Delete returned item
    public function destroy($returned_id)
    {
        return $this->service->delete($returned_id);
    }
}
