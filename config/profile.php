<?php

return [
    // Profile fields available for practice mode context injection
    // Keys are column names, values are display labels
    'context_fields' => [
        'birth_year' => 'Birth Year',
        'gender' => 'Gender',
        'zip_code' => 'Zip Code',
        'job_title' => 'Job Title',
        'industry' => 'Industry',
        'company_size' => 'Company Size',
        'career_level' => 'Career Level',
        'years_in_role' => 'Years in Role',
        'years_experience' => 'Years Experience',
        'manages_people' => 'Manages People',
        'direct_reports' => 'Direct Reports',
        'reports_to_role' => 'Reports To',
        'team_composition' => 'Team Composition',
        'collaboration_style' => 'Collaboration Style',
        'cross_functional_teams' => 'Cross-Functional Teams',
        'communication_tools' => 'Communication Tools',
        'improvement_areas' => 'Improvement Areas',
        'upcoming_challenges' => 'Upcoming Challenges',
    ],

    'company_sizes' => [
        'startup' => 'Startup (1-50)',
        'smb' => 'SMB (51-500)',
        'enterprise' => 'Enterprise (500+)',
    ],

    'career_levels' => [
        'entry' => 'Entry Level',
        'mid' => 'Mid-Level',
        'senior' => 'Senior',
        'executive' => 'Executive',
        'founder' => 'Founder',
    ],

    'team_compositions' => [
        'colocated' => 'Co-located',
        'remote' => 'Fully Remote',
        'hybrid' => 'Hybrid',
        'international' => 'International/Distributed',
    ],

    'collaboration_styles' => [
        'async' => 'Async-heavy',
        'meeting-heavy' => 'Meeting-heavy',
        'mixed' => 'Mixed',
    ],

    'cross_functional_options' => [
        'engineering' => 'Engineering',
        'design' => 'Design',
        'product' => 'Product',
        'sales' => 'Sales',
        'marketing' => 'Marketing',
        'customer_success' => 'Customer Success',
        'finance' => 'Finance',
        'hr' => 'HR',
        'legal' => 'Legal',
        'operations' => 'Operations',
    ],

    'improvement_areas' => [
        'communication' => 'Communication',
        'decision_making' => 'Decision Making',
        'leadership' => 'Leadership',
        'delegation' => 'Delegation',
        'feedback' => 'Giving/Receiving Feedback',
        'conflict_resolution' => 'Conflict Resolution',
        'executive_presence' => 'Executive Presence',
        'negotiation' => 'Negotiation',
    ],

    'challenges' => [
        'new_role' => 'New Role',
        'first_time_manager' => 'First-Time Manager',
        'managing_managers' => 'Managing Managers',
        'executive_transition' => 'Executive Transition',
        'cross_functional_project' => 'Cross-Functional Project',
        'difficult_stakeholders' => 'Difficult Stakeholders',
    ],

    // Field metadata for dynamic form generation
    'context_fields_meta' => [
        'birth_year' => [
            'label' => 'Birth Year',
            'type' => 'number',
            'min' => 1920,
            'max' => 2015,
            'placeholder' => 'e.g., 1990',
        ],
        'gender' => [
            'label' => 'Gender',
            'type' => 'text',
            'placeholder' => 'e.g., Male, Female, Non-binary',
        ],
        'zip_code' => [
            'label' => 'Zip Code',
            'type' => 'text',
            'placeholder' => 'e.g., 94103',
        ],
        'job_title' => [
            'label' => 'Job Title',
            'type' => 'text',
            'placeholder' => 'e.g., Engineering Manager',
        ],
        'industry' => [
            'label' => 'Industry',
            'type' => 'text',
            'placeholder' => 'e.g., Technology, Finance',
        ],
        'company_size' => [
            'label' => 'Company Size',
            'type' => 'select',
        ],
        'career_level' => [
            'label' => 'Career Level',
            'type' => 'select',
        ],
        'years_in_role' => [
            'label' => 'Years in Current Role',
            'type' => 'number',
            'min' => 0,
            'max' => 50,
            'placeholder' => 'e.g., 3',
        ],
        'years_experience' => [
            'label' => 'Total Years of Experience',
            'type' => 'number',
            'min' => 0,
            'max' => 60,
            'placeholder' => 'e.g., 10',
        ],
        'manages_people' => [
            'label' => 'Do you manage people?',
            'type' => 'checkbox',
        ],
        'direct_reports' => [
            'label' => 'Number of Direct Reports',
            'type' => 'number',
            'min' => 0,
            'max' => 1000,
            'placeholder' => 'e.g., 5',
        ],
        'reports_to_role' => [
            'label' => 'Your Manager\'s Role',
            'type' => 'text',
            'placeholder' => 'e.g., VP of Engineering',
        ],
        'team_composition' => [
            'label' => 'Team Composition',
            'type' => 'select',
        ],
        'collaboration_style' => [
            'label' => 'Collaboration Style',
            'type' => 'select',
        ],
        'cross_functional_teams' => [
            'label' => 'Teams You Work With',
            'type' => 'multiselect',
        ],
        'communication_tools' => [
            'label' => 'Communication Tools',
            'type' => 'text',
            'placeholder' => 'e.g., Slack, Email, Zoom',
        ],
        'improvement_areas' => [
            'label' => 'Areas to Improve',
            'type' => 'multiselect',
        ],
        'upcoming_challenges' => [
            'label' => 'Upcoming Challenges',
            'type' => 'multiselect',
        ],
    ],
];
