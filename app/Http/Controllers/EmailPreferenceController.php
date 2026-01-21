<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailPreferenceController extends Controller
{
    public function unsubscribe(Request $request, string $type)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to manage your email preferences.');
        }

        $validTypes = ['weekly_report', 'teaser_emails', 'product_updates'];

        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        $preferences = $user->email_preferences ?? [];
        $preferences[$type] = false;
        $user->update(['email_preferences' => $preferences]);

        return Inertia::render('emails/unsubscribed', [
            'type' => $type,
            'typeName' => $this->getTypeName($type),
        ]);
    }

    private function getTypeName(string $type): string
    {
        return match ($type) {
            'weekly_report' => 'Weekly Blind Spots Reports',
            'teaser_emails' => 'Teaser Emails',
            'product_updates' => 'Product Updates',
            default => 'Emails',
        };
    }
}
