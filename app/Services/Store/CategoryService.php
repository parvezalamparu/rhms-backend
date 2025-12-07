<?php

namespace App\Services\Store;

use App\Models\Store\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class CategoryService
{
    // Get all categories (active + inactive)
    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    // Get only active categories (for item assignment)
    public function getActiveCategories()
    {
        $categories = Category::where('is_active', true)->get();

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    // Create a new category
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255|unique:categories,category_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = Category::create([
            'category_name' => $request->category_name,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    // Get category by ID
    public function getCategoryById($id)
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'data' => $category,
        ], 200);
    }

    // Update category
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255|unique:categories,category_name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update([
            'category_name' => $request->category_name,
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category,
        ], 200);
    }

    // Delete category
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }

    // Toggle active/inactive status
    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Category {$status} successfully.",
            'data' => $category,
        ], 200);
    }
}
