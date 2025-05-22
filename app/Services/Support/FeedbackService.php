<?php

namespace App\Services\Support;

use App\Models\Feedback;
use App\Models\FeedbackSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class FeedbackService
{
    /**
     * List + filters + pagination.
     */
    public function getIndex(): array
    {
        $user  = Auth::user();
        $query = Feedback::with('user');

        if ($user->tenant_id !== null) {
            $query->where('user_id', $user->id);
        }

        $feedbacks = $query
            ->orderBy(Request::input('sort_field', 'created_at'),
                      Request::input('sort_direction', 'desc'))
            ->paginate(Request::input('per_page', 10))
            ->withQueryString();

        $subjects = FeedbackSubject::withTrashed()->get();
        $permissions = Auth::user()->getAllPermissions();

        return [
            'feedbacks'        => $feedbacks,
            'filters'          => ['per_page' => $feedbacks->perPage()],
            'feedback_subjects'=> $subjects,
            'permissions'      => $permissions,
        ];
    }

    /**
     * Show one and mark seen.
     */
    public function getOne(int $id): array
    {
        $user  = Auth::user();
        $query = Feedback::with('user');

        if ($user->tenant_id !== null) {
            $query->where('user_id', $user->id);
        }

        $fb = $query->findOrFail($id);

        if ($user->tenant_id === null) {
            $fb->update(['seen_by_admin' => true]);
        }

        return ['feedback' => $fb];
    }

    /**
     * Create new feedback.
     */
    public function create(array $data): Feedback
    {
        $user = Auth::user();
        return Feedback::create([
            'tenant_id'     => $user->tenant_id,
            'user_id'       => $user->id,
            'subject'       => $data['subject'],
            'message'       => $data['message'],
            'seen_by_admin' => false,
        ]);
    }

    /**
     * Delete one.
     */
    public function delete(int $id): void
    {
        Feedback::findOrFail($id)->delete();
    }

    /**
     * Bulk delete.
     */
    public function deleteMultiple(array $ids): void
    {
        $user  = Auth::user();
        $query = Feedback::whereIn('id', $ids);
        if ($user->tenant_id !== null) {
            $query->where('user_id', $user->id);
        }
        $query->delete();
    }
}
