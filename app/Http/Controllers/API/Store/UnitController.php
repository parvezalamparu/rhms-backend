<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\UnitService;

class UnitController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    // Get all units
    public function view()
    {
        return $this->unitService->getAllUnits();
    }

    // Get only active units
    public function active()
    {
        return $this->unitService->getActiveUnits();
    }

    // Create a new unit
    public function store(Request $request)
    {
        return $this->unitService->createUnit($request);
    }

    // Show a single unit
    public function show($id)
    {
        return $this->unitService->getUnitById($id);
    }

    // Update a unit
    public function update(Request $request, $id)
    {
        return $this->unitService->updateUnit($request, $id);
    }

    // Delete a unit
    public function destroy($id)
    {
        return $this->unitService->deleteUnit($id);
    }

    // Toggle active/inactive status
    public function toggle($id)
    {
        return $this->unitService->toggleStatus($id);
    }
}
