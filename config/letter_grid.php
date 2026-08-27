<?php

return [
    'default_time_limit' => 30,

    /* Team A = fire/red, Team B = gold — matches site identity */
    'team_colors' => [
        'a' => ['fill' => '#FF1744', 'glow' => 'rgba(255,23,68,.4)', 'label' => 'أحمر'],
        'b' => ['fill' => '#FFB300', 'glow' => 'rgba(255,179,0,.4)', 'label' => 'ذهبي'],
    ],

    /*
    | Default honeycomb layout for 28 Arabic letters.
    | row/col define placement; letters are assigned in order.
    */
    'default_layout' => [
        ['row' => 0, 'col' => 1], ['row' => 0, 'col' => 2], ['row' => 0, 'col' => 3], ['row' => 0, 'col' => 4],
        ['row' => 1, 'col' => 0], ['row' => 1, 'col' => 1], ['row' => 1, 'col' => 2], ['row' => 1, 'col' => 3], ['row' => 1, 'col' => 4],
        ['row' => 2, 'col' => 0], ['row' => 2, 'col' => 1], ['row' => 2, 'col' => 2], ['row' => 2, 'col' => 3], ['row' => 2, 'col' => 4], ['row' => 2, 'col' => 5],
        ['row' => 3, 'col' => 0], ['row' => 3, 'col' => 1], ['row' => 3, 'col' => 2], ['row' => 3, 'col' => 3], ['row' => 3, 'col' => 4],
        ['row' => 4, 'col' => 1], ['row' => 4, 'col' => 2], ['row' => 4, 'col' => 3], ['row' => 4, 'col' => 4],
        ['row' => 5, 'col' => 1], ['row' => 5, 'col' => 2], ['row' => 5, 'col' => 3], ['row' => 5, 'col' => 4],
    ],

    'default_letters' => [
        'أ', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض',
        'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي',
    ],
];
