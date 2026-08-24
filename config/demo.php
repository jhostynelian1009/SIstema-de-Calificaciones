<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seed Demo Data
    |--------------------------------------------------------------------------
    |
    | Controls whether demonstration seeders (users, courses, subjects, grades)
    | are executed during db:seed. Default is false to prevent accidental demo data
    | generation in production or clean development environments.
    |
    */
    'seed_demo' => (bool) env('SEED_DEMO_DATA', false),
];
