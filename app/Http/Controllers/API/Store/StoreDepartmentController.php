<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\StoreDepartmentService;

class StoreDepartmentController extends Controller
{
    protected $storeDepartmentService;

    public function __construct(StoreDepartmentService $storeDepartmentService)
    {
        $this->storeDepartmentService = $storeDepartmentService;
    }

    // Get all departments
    public function view()
    {
        return $this->storeDepartmentService->getAllDepartments();
    }

    // Create new department
    public function store(Request $request)
    {
        return $this->storeDepartmentService->createDepartment($request);
    }

    // Show single department
    public function show($id)
    {
        return $this->storeDepartmentService->getDepartmentById($id);
    }

    // Update department
    public function update(Request $request, $id)
    {
        return $this->storeDepartmentService->updateDepartment($request, $id);
    }

    // Delete department
    public function destroy($id)
    {
        return $this->storeDepartmentService->deleteDepartment($id);
    }

    // Toggle active/inactive
    public function toggleStatus($id)
    {
        return $this->storeDepartmentService->toggleDepartmentStatus($id);
    }
}
