<?php

namespace App\Services\Store;

use App\Models\Store\ApprovedReturn;
use App\Models\Store\ApprovedReturnDetail;
use App\Models\Store\ReturnedItem;
use App\Models\Store\ReturnedItemDetail;

class ApprovedReturnService
{
    // Approve Returned Items
    public function approve(array $data, $returned_id)
    {
        $returned = ReturnedItem::where('returned_id', $returned_id)->first();

        if (!$returned) {
            return ['message' => 'Return record not found!', 'status' => 404];
        }

        $details = ReturnedItemDetail::where('returned_id', $returned_id)->get();

        // Create Approved Return Header
        $approved = ApprovedReturn::create([
            'returned_id' => $returned->returned_id,
            'date' => now(),
            'department' => $returned->department,
            'returned_by' => $returned->returned_by,
            'approved_by' => $data['approved_by'],
        ]);

        // Create Approved Details
        foreach ($details as $item) {
            ApprovedReturnDetail::create([
                'approved_id' => $approved->approved_id,
                'returned_id' => $returned_id,
                'date' => $approved->date,
                'department' => $approved->department,
                'returned_by' => $approved->returned_by,
                'approved_by' => $approved->approved_by,
                'note' => $item->note,
                'item_name' => $item->item_name,
                'batch_no' => $item->batch_no,
                'qty' => $item->qty,
                'reason' => $item->reason,
            ]);
        }

        return [
            'message' => 'Returned items successfully approved!',
            'data' => $approved->load('details'),
            'status' => 201
        ];
    }

    // List All
    public function getAll()
    {
        return ApprovedReturn::withCount('details')->latest()->get();
    }

    // Show One
    public function getOne($id)
    {
        $record = ApprovedReturn::with('details')->where('approved_id', $id)->first();

        if (!$record) {
            return ['message' => 'Record not found', 'status' => 404];
        }

        return ['data' => $record, 'status' => 200];
    }

    // Delete
    public function delete($id)
    {
        $record = ApprovedReturn::where('approved_id', $id)->first();

        if (!$record) {
            return ['message' => 'Record not found', 'status' => 404];
        }

        ApprovedReturnDetail::where('approved_id', $id)->delete();
        $record->delete();

        return ['message' => 'Record deleted successfully', 'status' => 200];
    }
}
