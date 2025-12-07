<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\VendorService;

class VendorController extends Controller
{
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    public function view()
    {
        return $this->vendorService->view();
    }

    public function create(Request $request)
    {
        return $this->vendorService->create($request);
    }

    public function update(Request $request, $id)
    {
        return $this->vendorService->update($request, $id);
    }
    public function delete($id)
    {
        return $this->vendorService->delete($id);
    }

}
