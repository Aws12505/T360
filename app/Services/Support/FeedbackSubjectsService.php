<?php

namespace App\Services\Support;

use App\Models\FeedbackSubject;

class FeedbackSubjectsService
{
    public function create(array $data)
    {
        FeedbackSubject::create($data);
    }

    public function delete(int $id)
    {
        $sub = FeedbackSubject::findOrFail($id);
        $sub->delete();
    }

    public function restore(int $id)
    {
        $sub = FeedbackSubject::withTrashed()->findOrFail($id);
        $sub->restore();
    }

    public function forceDelete(int $id)
    {
        $sub = FeedbackSubject::withTrashed()->findOrFail($id);
        $sub->forceDelete();
    }
}
