<?php

return [
    'pos' => [
        'enabled' => env('MODULE_POS_ENABLED', true),
        'namespace' => 'App\\Modules\\POS',
        'service_provider' => 'App\\Modules\\POS\\Providers\\POSServiceProvider',
        'migration_path' => 'app/Modules/POS/Database/Migrations',
        'routes_api' => 'app/Modules/POS/Routes/api.php',
        'routes_web' => 'app/Modules/POS/Routes/web.php',
        'config' => 'app/Modules/POS/Config/pos.php',
        'views' => 'app/Modules/POS/Resources/Views',
    ],
    
    'ecommerce' => [
        'enabled' => env('MODULE_ECOMMERCE_ENABLED', true),
        'namespace' => 'App\\Modules\\Ecommerce',
        'service_provider' => 'App\\Modules\\Ecommerce\\Providers\\EcommerceServiceProvider',
        'migration_path' => 'app/Modules/Ecommerce/Database/Migrations',
        'routes_api' => 'app/Modules/Ecommerce/Routes/api.php',
        'routes_web' => 'app/Modules/Ecommerce/Routes/web.php',
        'config' => 'app/Modules/Ecommerce/Config/ecommerce.php',
        'views' => 'app/Modules/Ecommerce/Resources/Views',
    ],
    
    'loyalty' => [
        'enabled' => env('MODULE_LOYALTY_ENABLED', true),
        'namespace' => 'App\\Modules\\Loyalty',
        'service_provider' => 'App\\Modules\\Loyalty\\Providers\\LoyaltyServiceProvider',
        'migration_path' => 'app/Modules/Loyalty/Database/Migrations',
        'routes_api' => 'app/Modules/Loyalty/Routes/api.php',
        'routes_web' => 'app/Modules/Loyalty/Routes/web.php',
        'config' => 'app/Modules/Loyalty/Config/loyalty.php',
        'views' => 'app/Modules/Loyalty/Resources/Views',
    ],
];