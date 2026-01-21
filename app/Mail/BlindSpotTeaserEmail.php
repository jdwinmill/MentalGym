<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BlindSpotTeaserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $blindSpotCount,
        public int $totalResponses,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->blindSpotCount === 1
            ? 'We found a pattern in your training'
            : "We found {$this->blindSpotCount} patterns in your training";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.blind-spots.teaser',
            with: [
                'userName' => $this->user->first_name,
                'blindSpotCount' => $this->blindSpotCount,
                'totalResponses' => $this->totalResponses,
                'upgradeUrl' => route('practice-modes.index'),
                'unsubscribeUrl' => URL::signedRoute('email.unsubscribe', ['type' => 'teaser_emails']),
            ],
        );
    }
}
