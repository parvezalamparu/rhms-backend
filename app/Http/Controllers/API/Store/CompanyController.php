<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\CompanyService;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    // Get all companies (active + inactive)
    public function index()
    {
        return $this->companyService->getAllCompanies();
    }

    // Get only active companies
    public function active()
    {
        return $this->companyService->getActiveCompanies();
    }

    // Create a new company
    public function store(Request $request)
    {
        return $this->companyService->createCompany($request);
    }

    // Show single company
    public function show($id)
    {
        return $this->companyService->getCompanyById($id);
    }

    // Update a company
    public function update(Request $request, $id)
    {
        return $this->companyService->updateCompany($request, $id);
    }

    // Delete a company
    public function destroy($id)
    {
        return $this->companyService->deleteCompany($id);
    }

    // Toggle active/inactive status
    public function toggle($id)
    {
        return $this->companyService->toggleStatus($id);
    }
}
