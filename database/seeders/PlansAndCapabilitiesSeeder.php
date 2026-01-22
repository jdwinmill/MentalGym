<?php

namespace Database\Seeders;

use App\Models\Capability;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansAndCapabilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createCapabilities();
        $this->createPlans();
        $this->assignCapabilitiesToPlans();
    }

    protected function createCapabilities(): void
    {
        $capabilities = [
            // Access capabilities
            [
                'key' => 'track_switching',
                'name' => 'Track Switching',
                'description' => 'Ability to switch between different training tracks',
                'category' => Capability::CATEGORY_ACCESS,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 1,
            ],
            [
                'key' => 'multiple_active_tracks',
                'name' => 'Multiple Active Tracks',
                'description' => 'Access multiple training tracks simultaneously',
                'category' => Capability::CATEGORY_ACCESS,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 2,
            ],
            [
                'key' => 'mastery_content',
                'name' => 'Mastery Content',
                'description' => 'Access to advanced mastery-level training content',
                'category' => Capability::CATEGORY_ACCESS,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 3,
            ],

            // Limits capabilities
            [
                'key' => 'track_switch_cooldown',
                'name' => 'Track Switch Cooldown',
                'description' => 'Days required between track switches (0 = no cooldown)',
                'category' => Capability::CATEGORY_LIMITS,
                'value_type' => Capability::TYPE_INTEGER,
                'default_value' => '0',
                'sort_order' => 10,
            ],
            [
                'key' => 'max_active_tracks',
                'name' => 'Maximum Active Tracks',
                'description' => 'Maximum number of tracks that can be active at once',
                'category' => Capability::CATEGORY_LIMITS,
                'value_type' => Capability::TYPE_INTEGER,
                'default_value' => '1',
                'sort_order' => 11,
            ],
            [
                'key' => 'api_rate_limit',
                'name' => 'API Rate Limit',
                'description' => 'Maximum API requests per hour',
                'category' => Capability::CATEGORY_LIMITS,
                'value_type' => Capability::TYPE_INTEGER,
                'default_value' => '100',
                'sort_order' => 12,
            ],
            [
                'key' => 'daily_lesson_limit',
                'name' => 'Daily Lesson Limit',
                'description' => 'Maximum lessons per day (0 = unlimited)',
                'category' => Capability::CATEGORY_LIMITS,
                'value_type' => Capability::TYPE_INTEGER,
                'default_value' => '0',
                'sort_order' => 13,
            ],

            // Features capabilities
            [
                'key' => 'ai_analytics',
                'name' => 'AI Analytics',
                'description' => 'AI-powered insights and personalized recommendations',
                'category' => Capability::CATEGORY_FEATURES,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 20,
            ],
            [
                'key' => 'progress_insights',
                'name' => 'Progress Insights',
                'description' => 'Detailed progress analytics and trend reports',
                'category' => Capability::CATEGORY_FEATURES,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 21,
            ],
            [
                'key' => 'weakness_analysis',
                'name' => 'Weakness Analysis',
                'description' => 'Detailed analysis of weakness patterns',
                'category' => Capability::CATEGORY_FEATURES,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 22,
            ],
            [
                'key' => 'export_data',
                'name' => 'Export Data',
                'description' => 'Export progress and training data',
                'category' => Capability::CATEGORY_FEATURES,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 23,
            ],

            // Support capabilities
            [
                'key' => 'priority_support',
                'name' => 'Priority Support',
                'description' => 'Priority access to customer support',
                'category' => Capability::CATEGORY_SUPPORT,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 30,
            ],
            [
                'key' => 'coach_access',
                'name' => 'Coach Access',
                'description' => 'Access to one-on-one coaching sessions',
                'category' => Capability::CATEGORY_SUPPORT,
                'value_type' => Capability::TYPE_BOOLEAN,
                'default_value' => 'true',
                'sort_order' => 31,
            ],
        ];

        foreach ($capabilities as $data) {
            Capability::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }

    protected function createPlans(): void
    {
        $plans = [
            [
                'key' => 'essentials',
                'name' => 'Essentials',
                'description' => 'Perfect for beginners starting their mental fitness journey. Includes core training tracks with structured progression.',
                'tagline' => 'Start your mental fitness journey',
                'price' => 19.00,
                'billing_interval' => 'monthly',
                'yearly_price' => 190.00, // ~17% savings
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'key' => 'all_access',
                'name' => 'All Access',
                'description' => 'Unlock your full potential with unlimited access to all tracks, AI-powered insights, and premium features.',
                'tagline' => 'Unlock your full potential',
                'price' => 39.00,
                'billing_interval' => 'monthly',
                'yearly_price' => 390.00, // ~17% savings
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }

    protected function assignCapabilitiesToPlans(): void
    {
        $essentials = Plan::where('key', 'essentials')->first();
        $allAccess = Plan::where('key', 'all_access')->first();

        if (! $essentials || ! $allAccess) {
            return;
        }

        // Essentials tier capabilities
        $essentialsCapabilities = [
            'track_switching' => true,          // Can switch tracks
            'track_switch_cooldown' => 30,      // But must wait 30 days between switches
            'multiple_active_tracks' => false,  // One track at a time
            'max_active_tracks' => 1,
            'mastery_content' => false,         // No mastery content
            'ai_analytics' => false,            // No AI features
            'progress_insights' => true,        // Basic insights
            'weakness_analysis' => true,        // Basic weakness tracking
            'export_data' => false,             // No export
            'daily_lesson_limit' => 3,          // Limited lessons per day
            'api_rate_limit' => 100,
            'priority_support' => false,
            'coach_access' => false,
        ];

        // All Access tier capabilities
        $allAccessCapabilities = [
            'track_switching' => true,          // Can switch tracks
            'track_switch_cooldown' => 0,       // No cooldown
            'multiple_active_tracks' => true,   // Multiple tracks
            'max_active_tracks' => 10,          // Up to 10 tracks
            'mastery_content' => true,          // Full mastery content
            'ai_analytics' => true,             // AI-powered insights
            'progress_insights' => true,        // Full insights
            'weakness_analysis' => true,        // Full analysis
            'export_data' => true,              // Can export
            'daily_lesson_limit' => 0,          // Unlimited lessons
            'api_rate_limit' => 1000,
            'priority_support' => true,         // Priority support
            'coach_access' => false,            // Could be separate tier
        ];

        $this->syncPlanCapabilities($essentials, $essentialsCapabilities);
        $this->syncPlanCapabilities($allAccess, $allAccessCapabilities);
    }

    protected function syncPlanCapabilities(Plan $plan, array $capabilities): void
    {
        $syncData = [];

        foreach ($capabilities as $key => $value) {
            $capability = Capability::where('key', $key)->first();

            if ($capability) {
                $syncData[$capability->id] = ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value];
            }
        }

        $plan->capabilities()->sync($syncData);
    }
}
