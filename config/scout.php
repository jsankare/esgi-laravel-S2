<?php

return [
    'driver' => 'database',
    'prefix' => '',
    'queue' => false,
    'after_commit' => false,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),
];
