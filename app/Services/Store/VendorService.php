<?php

namespace App\Services\Store;

use App\Models\Store\Vendor;
use Illuminate\Support\Facades\Validator;

class VendorService
{
    // View all vendors
    public function view()
    {
        $vendors = Vendor::all();
        return response()->json(['data' => $vendors], 200);
    }

    // Create vendor
    public function create($request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:255',
            'email' => 'nullable|string|max:50',
            'phone_number' => 'required|string|unique:vendors,phone_number|size:10',
            'gst' => 'required|string|unique:vendors,gst|max:100',
            'contact_person_name' => 'nullable|string|max:255',
            'pin_code' => 'nullable|string|size:6',
            'address' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor = Vendor::create($validator->validated());

        return response()->json([
            'message' => 'Vendor added successfully',
            'data' => $vendor
        ], 201);
    }

    // Update vendor
    public function update($request, $id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found!'], 404);
        }

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:255',
            'email' => 'nullable|string|max:50',
            'phone_number' => 'required|string|size:10|unique:vendors,phone_number,' . $id,
            'gst' => 'required|string|max:100|unique:vendors,gst,' . $id,
            'contact_person_name' => 'nullable|string|max:255',
            'pin_code' => 'nullable|string|size:6',
            'address' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor->update($validator->validated());

        return response()->json([
            'message' => 'Vendor updated successfully.',
            'data' => $vendor
        ], 200);
    }

    // delete vendor

    public function delete($id)
    {
       $vendor = Vendor::find($id);

       if (!$vendor) {
         return response()->json([
             'message' => 'Vendor not found!'
         ], 404);
     }

        $vendor->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully.'
        ], 200);
    }
}
