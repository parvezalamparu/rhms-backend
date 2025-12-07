<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\ApprovedReturnService;

class ApprovedReturnController extends Controller
{
    protected $service;

    public function __construct(ApprovedReturnService $service)
    {
        $this->service = $service;
    }

    // Approve Returned Items
    public function approve(Request $request, $returned_id)
    {
        $validated = $request->validate([
            'approved_by' => 'required|string|max:255'
        ]);

        $response = $this->service->approve($validated, $returned_id);
        return response()->json($response, $response['status']);
    }

    // List all approved records
    public function index()
    {
        return response()->json($this->service->getAll());
    }

    // Show single approved record
    public function show($id)
    {
        $response = $this->service->getOne($id);
        return response()->json($response, $response['status']);
    }

    // Delete approved return
    public function destroy($id)
    {
        $response = $this->service->delete($id);
        return response()->json($response, $response['status']);
    }
}
