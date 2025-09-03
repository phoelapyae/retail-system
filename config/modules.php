<?php

return [
    'ecommerce' => [
        'enabled' => env('MODULE_ECOMMERCE_ENABLED', true),
        'namespace' => 'App\\Modules\\Ecommerce',
        'service_provider' => 'App\\Modules\\Ecommerce\\Providers\\EcommerceServiceProvider',
        'migration_path' => 'app/Modules/Ecommerce/Database/Migrations',
        'routes' => 'routes/modules/ecommerce.php',
    ],
    
    'pos' => [
        'enabled' => env('MODULE_POS_ENABLED', true),
        'namespace' => 'App\\Modules\\POS',
        'service_provider' => 'App\\Modules\\POS\\Providers\\POSServiceProvider',
        'migration_path' => 'app/Modules/POS/Database/Migrations',
        'routes' => 'routes/modules/pos.php',
    ],
    
    'loyalty' => [
        'enabled' => env('MODULE_LOYALTY_ENABLED', true),
        'namespace' => 'App\\Modules\\Loyalty',
        'service_provider' => 'App\\Modules\\Loyalty\\Providers\\LoyaltyServiceProvider',
        'migration_path' => 'app/Modules/Loyalty/Database/Migrations',
        'routes' => 'routes/modules/loyalty.php',
    ],
];