<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(): Response
    {
        $feedback = Feedback::with('user')
            ->orderByRaw("CASE WHEN type = 'bug' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'title' => $item->title,
                'body' => $item->body,
                'url' => $item->url,
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                    'email' => $item->user->email,
                ],
                'created_at' => $item->created_at->format('M j, Y g:i A'),
            ]);

        return Inertia::render('admin/feedback/index', [
            'feedback' => $feedback,
        ]);
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted.');
    }
}
