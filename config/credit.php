<?php

return [
    // Welcome bonus credits for new workspaces
    'credit_welcome' => 50,
    
    // Credits debit per portfolio creation
    'credit_portfolio' =>  0.5,
    
    'packages' => [
        [
            'id' => 'credit_50',
            'credits' => 50,
            'price' => 5,
            'label' => '50 Credits',
            'description' => 'Perfect for small teams',
            'popular' => false,
        ],
        [
            'id' => 'credit_120',
            'credits' => 120,
            'price' => 10,
            'label' => '120 Credits',
            'description' => 'Most popular choice',
            'popular' => true,
        ],
        [
            'id' => 'credit_250',
            'credits' => 250,
            'price' => 20,
            'label' => '250 Credits',
            'description' => 'Great value for growing teams',
            'popular' => false,
        ],
        [
            'id' => 'credit_650',
            'credits' => 650,
            'price' => 50,
            'label' => '650 Credits',
            'description' => 'Best value for large teams',
            'popular' => false,
        ],
    ],
];

