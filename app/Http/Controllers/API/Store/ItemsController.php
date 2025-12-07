<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ItemService;

class ItemsController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
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
}
