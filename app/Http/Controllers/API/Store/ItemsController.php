<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ItemService;
use App\Services\Store\ItemStockService;

class ItemsController extends Controller
{
    protected $itemService;
    protected $itemStockService;

    public function __construct(ItemService $itemService, ItemStockService $itemStockService)
    {
        $this->itemService = $itemService;
        $this->itemStockService = $itemStockService;
    }

    public function view()
    {
        return $this->itemService->getAllItems();
    }

    public function store(Request $request)
    {
        return $this->itemService->createItem($request);
    }

    public function show($uuid)
    {
        return $this->itemService->getItemById($uuid);
    }

    public function update(Request $request, $id)
    {
        return $this->itemService->updateItem($request, $id);
    }

    public function destroy($id)
    {
        return $this->itemService->deleteItem($id);
    }

    public function toggleStatus($id)
    {
        return $this->itemService->toggleItemStatus($id);
    }

    /**
     * Get available batches for a specific item
     * 
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableBatches($itemId)
    {
        return $this->itemStockService->getAvailableBatches($itemId);
    }

    /**
     * Get detailed stock information for a specific item and batch
     * 
     * @param int $itemId
     * @param string $batchNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBatchDetails($itemId, $batchNo)
    {
        return $this->itemStockService->getBatchDetails($itemId, $batchNo);
    }
}