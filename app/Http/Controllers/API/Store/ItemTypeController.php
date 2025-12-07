<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ItemTypeService;

class ItemTypeController extends Controller
{
    protected $itemTypeService;

    public function __construct(ItemTypeService $itemTypeService)
    {
        $this->itemTypeService = $itemTypeService;
    }

    // Get all item types (active + inactive)
    public function index()
    {
        return $this->itemTypeService->getAllItemTypes();
    }

    // Get only active item types
    public function active()
    {
        return $this->itemTypeService->getActiveItemTypes();
    }

    // Create new item type
    public function store(Request $request)
    {
        return $this->itemTypeService->createItemType($request);
    }

    // Show specific item type
    public function show($id)
    {
        return $this->itemTypeService->getItemTypeById($id);
    }

    // Update item type
    public function update(Request $request, $id)
    {
        return $this->itemTypeService->updateItemType($request, $id);
    }

    // Delete item type
    public function destroy($id)
    {
        return $this->itemTypeService->deleteItemType($id);
    }

    // Toggle active/inactive
    public function toggle($id)
    {
        return $this->itemTypeService->toggleStatus($id);
    }
}
