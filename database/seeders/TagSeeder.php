<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Skills
            ['name' => 'Decision-Making', 'slug' => 'decision-making', 'category' => 'skill', 'display_order' => 0],
            ['name' => 'Communication', 'slug' => 'communication', 'category' => 'skill', 'display_order' => 1],
            ['name' => 'Leadership', 'slug' => 'leadership', 'category' => 'skill', 'display_order' => 2],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'category' => 'skill', 'display_order' => 3],
            ['name' => 'Critical Thinking', 'slug' => 'critical-thinking', 'category' => 'skill', 'display_order' => 4],
            ['name' => 'Emotional Intelligence', 'slug' => 'emotional-intelligence', 'category' => 'skill', 'display_order' => 5],

            // Context
            ['name' => 'Meetings', 'slug' => 'meetings', 'category' => 'context', 'display_order' => 0],
            ['name' => '1:1s', 'slug' => 'one-on-ones', 'category' => 'context', 'display_order' => 1],
            ['name' => 'Presentations', 'slug' => 'presentations', 'category' => 'context', 'display_order' => 2],
            ['name' => 'Written', 'slug' => 'written', 'category' => 'context', 'display_order' => 3],
            ['name' => 'Conflict', 'slug' => 'conflict', 'category' => 'context', 'display_order' => 4],
            ['name' => 'Hiring', 'slug' => 'hiring', 'category' => 'context', 'display_order' => 5],

            // Duration
            ['name' => 'Quick (5 min)', 'slug' => 'quick', 'category' => 'duration', 'display_order' => 0],
            ['name' => 'Standard (10-15 min)', 'slug' => 'standard', 'category' => 'duration', 'display_order' => 1],
            ['name' => 'Deep (20+ min)', 'slug' => 'deep', 'category' => 'duration', 'display_order' => 2],

            // Role
            ['name' => 'Individual Contributor', 'slug' => 'ic', 'category' => 'role', 'display_order' => 0],
            ['name' => 'Manager', 'slug' => 'manager', 'category' => 'role', 'display_order' => 1],
            ['name' => 'Executive', 'slug' => 'executive', 'category' => 'role', 'display_order' => 2],
            ['name' => 'Founder', 'slug' => 'founder', 'category' => 'role', 'display_order' => 3],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
