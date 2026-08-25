<?php

return [
    'free_trial_limit' => 5, // legacy; free play is now 1 category

    // Admin can add unlimited questions per category.
    'max_questions_per_category' => null,
    'questions_per_level' => null,

    // Each play round locks this many questions per difficulty (2+2+2 = 6).
    'board_questions_per_level' => 2,

    'points_map' => [
        'easy' => 200,
        'medium' => 400,
        'hard' => 600,
    ],
    'default_helpers' => [
        'swap' => 1,
        'phone_friend' => 1,
        'two_answers' => 1,
    ],
    'default_time_limit' => 60,
];
