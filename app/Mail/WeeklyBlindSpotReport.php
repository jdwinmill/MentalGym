<?php

namespace App\Mail;

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\WeeklyEmailContent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class WeeklyBlindSpotReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public WeeklyEmailContent $content,
        public int $sessionsThisWeek,
        public ?array $recommendedArticle = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->content->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.blind-spots.weekly-report',
            with: [
                'userName' => $this->user->first_name,
                'sessionsThisWeek' => $this->sessionsThisWeek,
                'improving' => $this->content->improving,
                'needsWork' => $this->content->needsWork,
                'patternToWatch' => $this->content->patternToWatch,
                'weeklyFocus' => $this->content->weeklyFocus,
                'article' => $this->recommendedArticle,
                'startSessionUrl' => route('practice-modes.index'),
                'unsubscribeUrl' => URL::signedRoute('email.unsubscribe', ['type' => 'weekly_report']),
            ],
        );
    }
}
