<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\DiscardItemService;

class DiscardItemController extends Controller
{
    protected $discardService;

    public function __construct(DiscardItemService $discardService)
    {
        $this->discardService = $discardService;
    }

    public function index()
    {
        return $this->discardService->getAll();
    }

    public function store(Request $request)
    {
        return $this->discardService->create($request);
    }

    public function show($id)
    {
        return $this->discardService->getById($id);
    }

    public function destroy($id)
    {
        return $this->discardService->delete($id);
    }
}
