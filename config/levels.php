<?php

return [
    'thresholds' => [
        1 => 10,   // 10 exchanges at Level 1 to reach Level 2
        2 => 15,   // 15 exchanges at Level 2 to reach Level 3
        3 => 20,   // 20 exchanges at Level 3 to reach Level 4
        4 => 30,   // 30 exchanges at Level 4 to reach Level 5
        5 => null, // Max level
    ],

    'max_level' => 5,

    'messages' => [
        2 => 'Scenarios will now include competing priorities and multiple stakeholders.',
        3 => 'Scenarios will now be ambiguous with no clear right answer.',
        4 => 'Scenarios will now involve high stakes and incomplete information.',
        5 => 'Scenarios will now challenge your core values. Every choice has real cost.',
    ],
];
