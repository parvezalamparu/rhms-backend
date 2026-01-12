<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\Store\ItemStockService;

class ItemStockController extends Controller
{
    protected $itemStockService;

    public function __construct(ItemStockService $itemStockService)
    {
        $this->itemStockService = $itemStockService;
    }

    /**
     * Get available batches for a specific item
     */
    public function getAvailableBatches($itemId)
    {
        return $this->itemStockService->getAvailableBatches($itemId);
    }

    /**
     * Test endpoint - Check database structure
     */
    public function testDatabase()
    {
        return $this->itemStockService->testDatabaseStructure();
    }
}