<?php

namespace App\Services\Store;

use App\Models\Store\IssueItems;
use App\Models\Store\IssueItemDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IssueItemService
{
    /**
     * List all issues with pagination
     */
    public function getAllIssues()
    {
        return response()->json([
            'data' => IssueItems::withCount('details')
                ->orderBy('id', 'desc')
                ->paginate(20)
        ], 200);
    }

    /**
     * Create a new Issue with details
     */
    public function createIssue($request)
    {
        $validator = Validator::make($request->all(), [
            'requisition_no' => 'required|string|max:255',
            'issue_to' => 'required|string|max:255',
            'generated_by' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required|string|max:255',
            'items.*.exp_date' => 'nullable|date',
            'items.*.unit_qty' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:255',
            'items.*.sub_unit_qty' => 'nullable|numeric|min:0',
            'items.*.sub_unit' => 'nullable|string|max:255',
            'items.*.qty' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            // Only allowed keys
            $issueData = collect($validated)->only([
                'requisition_no', 'issue_to', 'generated_by', 'issue_date'
            ])->toArray();

            $issue = IssueItems::create($issueData);

            $foreignKey = ['issue_no' => $issue->issue_no];

            $details = collect($validated['items'])->map(function ($item) use ($foreignKey, $validated) {
                return array_merge($foreignKey, [
                    'requisition_no' => $item['requisition_no'] ?? $validated['requisition_no'] ?? null,
                    'issue_to' => $validated['issue_to'],          
                    'generated_by' => $validated['generated_by'], 
                    'issue_date' => $validated['issue_date'],
                    'item_id' => $item['item_id'],
                    'batch_no' => $item['batch_no'],
                    'exp_date' => $item['exp_date'] ?? null,
                    'unit_qty' => $item['unit_qty'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'sub_unit_qty' => $item['sub_unit_qty'] ?? null,
                    'sub_unit' => $item['sub_unit'] ?? null,
                    'qty' => $item['qty'] ?? null,
                ]);
            })->toArray();

            IssueItemDetails::insert($details);

            DB::commit();

            // Load with item details to show item_name to user
            return response()->json([
                'message' => 'Issue created successfully!',
                'data' => $issue->load('details.item')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create issue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch a single Issue by issue number
     */
    public function getIssueByNo($issue_no)
    {
        try {
            // Load with item details to show item_name to user
            return response()->json([
                'data' => IssueItems::with('details.item')
                    ->where('issue_no', $issue_no)
                    ->firstOrFail()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Issue not found'], 404);
        }
    }

    /**
     * Update Issue & its details
     */
    public function updateIssue($request, $issue_no)
    {
        $validator = Validator::make($request->all(), [
            'requisition_no' => 'sometimes|string|max:255',
            'issue_to' => 'sometimes|string|max:255',
            'generated_by' => 'sometimes|string|max:255',
            'issue_date' => 'sometimes|date',
            'items' => 'nullable|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.batch_no' => 'required_with:items|string|max:255',
            'items.*.exp_date' => 'nullable|date',
            'items.*.unit_qty' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:255',
            'items.*.sub_unit_qty' => 'nullable|numeric|min:0',
            'items.*.sub_unit' => 'nullable|string|max:255',
            'items.*.qty' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $issue = IssueItems::where('issue_no', $issue_no)->firstOrFail();

            $issue->update(collect($validated)->except('items')->toArray());

            if (!empty($validated['items'])) {

                IssueItemDetails::where('issue_no', $issue_no)->delete();

                $foreignKey = ['issue_no' => $issue->issue_no];

                $details = collect($validated['items'])->map(function ($item) use ($foreignKey, $validated) {
                    return array_merge($foreignKey, [
                        'requisition_no' => $validated['requisition_no'],
                        'issue_to' => $validated['issue_to'],
                        'generated_by' => $validated['generated_by'],
                        'issue_date' => $validated['issue_date'],
                        'item_id' => $item['item_id'],
                        'batch_no' => $item['batch_no'],
                        'exp_date' => $item['exp_date'] ?? null,
                        'unit_qty' => $item['unit_qty'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'sub_unit_qty' => $item['sub_unit_qty'] ?? null,
                        'sub_unit' => $item['sub_unit'] ?? null,
                        'qty' => $item['qty'] ?? null,
                    ]);
                })->toArray();

                IssueItemDetails::insert($details);
            }

            DB::commit();

            // Load with item details to show item_name to user
            return response()->json([
                'message' => 'Issue updated successfully!',
                'data' => $issue->load('details.item')
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Issue not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update issue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Issue and details
     */
    public function deleteIssue($issue_no)
    {
        DB::beginTransaction();

        try {
            $issue = IssueItems::where('issue_no', $issue_no)->firstOrFail();

            IssueItemDetails::where('issue_no', $issue_no)->delete();
            $issue->delete();

            DB::commit();

            return response()->json(['message' => 'Issue deleted successfully'], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Issue not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete issue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}