<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan ?? 'free',
                'trial_ends_at' => $user->trial_ends_at?->toDateTimeString(),
                'has_access' => $user->hasAccess(),
                'subscription_status' => $user->getSubscriptionStatus(),
                'created_at' => $user->created_at->toDateString(),
            ]);

        return Inertia::render('admin/users/index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan ?? 'free',
                'trial_ends_at' => $user->trial_ends_at?->format('Y-m-d\TH:i'),
                'has_access' => $user->hasAccess(),
                'current_status' => $user->getSubscriptionStatus(),
            ],
            'plans' => array_keys(config('plans')),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => 'required|in:free,pro,unlimited',
            'trial_ends_at' => 'nullable|date',
        ]);

        $user->update([
            'plan' => $validated['plan'],
            'trial_ends_at' => $validated['trial_ends_at'] ? \Carbon\Carbon::parse($validated['trial_ends_at']) : null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function extendTrial(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $user->extendTrial($validated['days']);

        return redirect()->back()->with('success', "Trial extended by {$validated['days']} days.");
    }
}
