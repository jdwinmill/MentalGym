<?php

namespace App\Services;

class ArticleRecommender
{
    private array $articles = [
        'authority' => [
            'title' => 'Why Smart People Hedge (And Why It Hurts Them)',
            'description' => 'The hidden cost of "I think" and "maybe" in professional settings.',
            'url' => '/blog/why-smart-people-hedge',
        ],
        'brevity' => [
            'title' => 'The First Draft Is Always Too Long',
            'description' => 'How to cut your communication in half without losing meaning.',
            'url' => '/blog/first-draft-too-long',
        ],
        'structure' => [
            'title' => 'Frameworks Are Thinking Tools',
            'description' => 'Why STAR, PREP, and other structures make you sound smarter.',
            'url' => '/blog/frameworks-thinking-tools',
        ],
        'composure' => [
            'title' => 'Pressure Reveals Training Gaps',
            'description' => 'What happens to your skills when stakes get real.',
            'url' => '/blog/pressure-reveals-gaps',
        ],
        'directness' => [
            'title' => 'Stop Burying the Lead',
            'description' => 'The first sentence problem and how to fix it.',
            'url' => '/blog/stop-burying-lead',
        ],
        'ownership' => [
            'title' => 'The Blame Reflex',
            'description' => 'How to take responsibility without taking all the heat.',
            'url' => '/blog/blame-reflex',
        ],
        'authenticity' => [
            'title' => 'Rehearsed vs. Prepared',
            'description' => 'The difference between sounding scripted and sounding ready.',
            'url' => '/blog/rehearsed-vs-prepared',
        ],
        'clarity' => [
            'title' => 'The Jargon Trap',
            'description' => 'Why complex language often hides unclear thinking.',
            'url' => '/blog/jargon-trap',
        ],
    ];

    public function recommend(?string $skill): ?array
    {
        if (!$skill || !isset($this->articles[$skill])) {
            return $this->getDefault();
        }

        return $this->articles[$skill];
    }

    private function getDefault(): array
    {
        return [
            'title' => 'The Case for Reps',
            'description' => 'Why practice beats tips every time.',
            'url' => '/blog/case-for-reps',
        ];
    }
}
