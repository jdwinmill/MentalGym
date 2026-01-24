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
];
