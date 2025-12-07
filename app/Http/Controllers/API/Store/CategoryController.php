<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // Get all categories (active + inactive)
    public function index()
    {
        return $this->categoryService->getAllCategories();
    }

    // Get only active categories (for item assignment)
    public function active()
    {
        return $this->categoryService->getActiveCategories();
    }

    // Create a new category
    public function store(Request $request)
    {
        return $this->categoryService->createCategory($request);
    }

    // Show a specific category
    public function show($id)
    {
        return $this->categoryService->getCategoryById($id);
    }

    // Update category
    public function update(Request $request, $id)
    {
        return $this->categoryService->updateCategory($request, $id);
    }

    // Delete category
    public function destroy($id)
    {
        return $this->categoryService->deleteCategory($id);
    }

    // Toggle active/inactive status
    public function toggle($id)
    {
        return $this->categoryService->toggleStatus($id);
    }
}
