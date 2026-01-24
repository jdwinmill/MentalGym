<?php

namespace Database\Seeders;

use App\Models\Principle;
use Illuminate\Database\Seeder;

class PrincipleSeeder extends Seeder
{
    public function run(): void
    {
        $principles = [
            [
                'name' => 'Clarity Under Pressure',
                'slug' => 'clarity-under-pressure',
                'description' => 'Master the art of communicating clearly when stakes are high. Learn to distill complex situations into actionable insights and deliver them with confidence.',
                'icon' => 'target',
                'position' => 0,
                'is_active' => true,
                'blog_urls' => null,
            ],
            [
                'name' => 'Executive Presence',
                'slug' => 'executive-presence',
                'description' => 'Develop the gravitas and credibility that commands attention in any room. Project confidence, speak with authority, and lead conversations effectively.',
                'icon' => 'crown',
                'position' => 1,
                'is_active' => true,
                'blog_urls' => null,
            ],
            [
                'name' => 'Decision Architecture',
                'slug' => 'decision-architecture',
                'description' => 'Build robust frameworks for making better decisions faster. Learn to structure complex problems, weigh tradeoffs, and communicate decisions clearly.',
                'icon' => 'git-branch',
                'position' => 2,
                'is_active' => true,
                'blog_urls' => null,
            ],
            [
                'name' => 'Precision Writing',
                'slug' => 'precision-writing',
                'description' => 'Write with impact and efficiency. Learn to craft clear, concise messages that drive action and avoid common pitfalls in professional communication.',
                'icon' => 'pen-tool',
                'position' => 3,
                'is_active' => true,
                'blog_urls' => null,
            ],
            [
                'name' => 'Meeting Leadership',
                'slug' => 'meeting-leadership',
                'description' => 'Transform meetings from time sinks into engines of progress. Lead discussions that drive decisions, engage participants, and produce clear outcomes.',
                'icon' => 'users',
                'position' => 4,
                'is_active' => true,
                'blog_urls' => null,
            ],
        ];

        foreach ($principles as $principleData) {
            Principle::updateOrCreate(
                ['slug' => $principleData['slug']],
                $principleData
            );
        }
    }
}
