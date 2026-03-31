<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Kanban Stages
    |--------------------------------------------------------------------------
    |
    | Default kanban stages that will be created for every new team.
    | These stages represent the typical lead workflow.
    |
    */
    'kanban_stages' => [
        [
            'name' => 'Open',
            'code' => 'OPEN',
            'color' => '#0d6efd',
            'sort_order' => 0,
            'is_system' => true,
        ],
        [
            'name' => 'New',
            'code' => 'NEW',
            'color' => '#6c757d',
            'sort_order' => 5,
            'is_system' => true,
        ],
        [
            'name' => 'Contacted',
            'code' => 'CONTACTED',
            'color' => '#17a2b8',
            'sort_order' => 10,
            'is_system' => false,
        ],
        [
            'name' => 'Proposal Sent',
            'code' => 'PROPOSAL_SENT',
            'color' => '#ffc107',
            'sort_order' => 15,
            'is_system' => false,
        ],
        [
            'name' => 'Follow-up',
            'code' => 'FOLLOW_UP',
            'color' => '#fd7e14',
            'sort_order' => 20,
            'is_system' => false,
        ],
        [
            'name' => 'Closed Won',
            'code' => 'WON',
            'color' => '#28a745',
            'sort_order' => 25,
            'is_system' => true,
        ],
        [
            'name' => 'Closed Lost',
            'code' => 'LOST',
            'color' => '#dc3545',
            'sort_order' => 30,
            'is_system' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Lead Sources
    |--------------------------------------------------------------------------
    |
    | Default lead sources that will be created for every new team.
    | These represent common channels where leads come from.
    |
    */
    'lead_sources' => [
        [
            'name' => 'Website',
            'color' => '#007bff',
            'sort_order' => 5,
            'description' => 'Leads from company website',
        ],
        [
            'name' => 'Referral',
            'color' => '#28a745',
            'sort_order' => 10,
            'description' => 'Referrals from existing clients',
        ],
        [
            'name' => 'Social Media',
            'color' => '#17a2b8',
            'sort_order' => 15,
            'description' => 'Social media platforms',
        ],
        [
            'name' => 'Email Campaign',
            'color' => '#ffc107',
            'sort_order' => 20,
            'description' => 'Email marketing campaigns',
        ],
        [
            'name' => 'Cold Outreach',
            'color' => '#6c757d',
            'sort_order' => 25,
            'description' => 'Direct outreach to prospects',
        ],
        [
            'name' => 'Paid Ads',
            'color' => '#fd7e14',
            'sort_order' => 30,
            'description' => 'Paid advertising campaigns',
        ],
        [
            'name' => 'Events',
            'color' => '#e83e8c',
            'sort_order' => 35,
            'description' => 'Trade shows and events',
        ],
        [
            'name' => 'Other',
            'color' => '#6f42c1',
            'sort_order' => 40,
            'description' => 'Other lead sources',
        ],
        [
            'name' => 'Upwork',
            'color' => '#14a800',
            'sort_order' => 45,
            'description' => 'Upwork',
        ],
    ],
];

