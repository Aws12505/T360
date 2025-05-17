<?php

namespace App\Http\Controllers\Web\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\FeedbackSubjectsService;
use App\Http\Requests\Support\StoreFeedbackSubjectRequest;

class FeedbackSubjectsController extends Controller
{
    public function __construct(protected FeedbackSubjectsService $service) {}

    public function store(StoreFeedbackSubjectRequest $request)
    {
        $this->service->create($request->validated());
        return back()->with('success', 'Category created successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return back()->with('success', 'Category deleted successfully.');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);
        return back()->with('success', 'Category restored successfully.');
    }

    public function forceDelete(int $id)
    {
        $this->service->forceDelete($id);
        return back()->with('success', 'Category permanently deleted.');
    }
}
