<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ItemStoreService;

class ItemStoreController extends Controller
{
    protected $itemStoreService;

    public function __construct(ItemStoreService $itemStoreService)
    {
        $this->itemStoreService = $itemStoreService;
    }

    // Get all item stores (active + inactive)
    public function index()
    {
        return $this->itemStoreService->getAllItemStores();
    }

    // Get only active item stores
    public function active()
    {
        return $this->itemStoreService->getActiveItemStores();
    }

    // Create new item store
    public function store(Request $request)
    {
        return $this->itemStoreService->createItemStore($request);
    }

    // Show specific item store
    public function show($id)
    {
        return $this->itemStoreService->getItemStoreById($id);
    }

    // Update item store
    public function update(Request $request, $id)
    {
        return $this->itemStoreService->updateItemStore($request, $id);
    }

    // Delete item store
    public function destroy($id)
    {
        return $this->itemStoreService->deleteItemStore($id);
    }

    // Toggle active/inactive
    public function toggle($id)
    {
        return $this->itemStoreService->toggleStatus($id);
    }
}
