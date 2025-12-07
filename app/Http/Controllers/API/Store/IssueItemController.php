<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Store\IssueItemService;

class IssueItemController extends Controller
{
    protected $issueService;

    public function __construct(IssueItemService $issueService)
    {
        $this->issueService = $issueService;
    }

    public function view()
    {
        return $this->issueService->getAllIssues();
    }

    public function store(Request $request)
    {
        return $this->issueService->createIssue($request);
    }

    public function show($issue_no)
    {
        return $this->issueService->getIssueByNo($issue_no);
    }

    public function update(Request $request, $issue_no)
    {
        return $this->issueService->updateIssue($request, $issue_no);
    }

    public function destroy($issue_no)
    {
        return $this->issueService->deleteIssue($issue_no);
    }
}
