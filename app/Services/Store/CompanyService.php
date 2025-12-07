<?php

namespace App\Services\Store;

use App\Models\Store\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyService
{
    // Get all companies (active + inactive)
    public function getAllCompanies()
    {
        $companies = Company::orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $companies,
        ], 200);
    }

    // Get only active companies
    public function getActiveCompanies()
    {
        $companies = Company::where('is_active', true)->orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $companies,
        ], 200);
    }

    // Create a new company
    public function createCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = Company::create([
            'company_name' => $request->company_name,
        ]);

        return response()->json([
            'message' => 'Company created successfully.',
            'data' => $company,
        ], 201);
    }

    // Get single company by ID
    public function getCompanyById($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        return response()->json(['data' => $company], 200);
    }

    // Update a company
    public function updateCompany(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $company->update([
            'company_name' => $request->company_name,
        ]);

        return response()->json([
            'message' => 'Company updated successfully.',
            'data' => $company,
        ], 200);
    }

    // Delete a company
    public function deleteCompany($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully.'], 200);
    }

    // Toggle active/inactive status
    public function toggleStatus($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $company->is_active = !$company->is_active;
        $company->save();

        $status = $company->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Company {$status} successfully.",
            'data' => $company,
        ], 200);
    }
}
