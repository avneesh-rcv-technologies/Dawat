<?php

return [
    'who_can_review' => env('RATING_WHO_CAN_REVIEW', 'both'), // Options: 'guest', 'logged_in', 'both'
    'require_approval' => env('RATING_REQUIRE_APPROVAL', true),
    'allow_rating_edit' => env('RATING_ALLOW_EDIT', false),
    'min_rating' => env('RATING_MIN', 1),
    'max_rating' => env('RATING_MAX', 5),
    'require_review_text' => env('RATING_REQUIRE_REVIEW_TEXT', false),
];
