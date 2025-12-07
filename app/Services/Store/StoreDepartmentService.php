<?php

namespace App\Services\Store;

use App\Models\Store\StoreDepartment;
use Illuminate\Support\Facades\Validator;

class StoreDepartmentService
{
    // Get all departments
    public function getAllDepartments()
    {
        $departments = StoreDepartment::orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $departments
        ], 200);
    }

    // Create new department
    public function createDepartment($request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:store_departments,department_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department = StoreDepartment::create($validator->validated());

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department
        ], 201);
    }

    // Get department by ID
    public function getDepartmentById($id)
    {
        $department = StoreDepartment::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        return response()->json(['data' => $department], 200);
    }

    // Update department
    public function updateDepartment($request, $id)
    {
        $department = StoreDepartment::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:store_departments,department_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department->update($validator->validated());

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $department
        ], 200);
    }

    // Delete department
    public function deleteDepartment($id)
    {
        $department = StoreDepartment::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $department->delete();

        return response()->json(['message' => 'Department deleted successfully.'], 200);
    }

    // Toggle Active/Inactive
    public function toggleDepartmentStatus($id)
    {
        $department = StoreDepartment::find($id);

        if (!$department) {
            return response()->json(['message' => 'Department not found.'], 404);
        }

        $department->is_active = !$department->is_active;
        $department->save();

        return response()->json([
            'message' => 'Department status updated successfully.',
            'data' => $department
        ], 200);
    }
}
