<?php

return [
    /*
     * School name shown in the sidebar, the login card and printed statements.
     * Set SCHOOL_NAME in .env for a real deployment; the default is deliberately
     * generic so the project can be shared without naming a client.
     */
    'name' => env('SCHOOL_NAME', 'دبیرستان نمونه'),

    'subtitle' => env('SCHOOL_SUBTITLE', 'سیستم حسابداری شهریه'),
];
