<?php

return [
    'name' => 'E-commerce',
    'version' => '1.0.0', 
    'description' => 'Full-featured e-commerce platform',
    
    // Store settings
    'store_name' => env('ECOMMERCE_STORE_NAME', 'My Store'),
    'store_currency' => env('ECOMMERCE_CURRENCY', 'USD'),
    'store_timezone' => env('ECOMMERCE_TIMEZONE', 'UTC'),
    
    // Product settings
    'products_per_page' => env('ECOMMERCE_PRODUCTS_PER_PAGE', 24),
    'enable_reviews' => env('ECOMMERCE_REVIEWS', true),
    'require_review_approval' => env('ECOMMERCE_REVIEW_APPROVAL', true),
    'enable_wishlist' => env('ECOMMERCE_WISHLIST', true),
    
    // Order settings
    'order_number_prefix' => env('ECOMMERCE_ORDER_PREFIX', 'ORD'),
    'order_number_length' => env('ECOMMERCE_ORDER_LENGTH', 8),
    'auto_invoice_orders' => env('ECOMMERCE_AUTO_INVOICE', true),
    
    // Shipping settings
    'free_shipping_threshold' => env('ECOMMERCE_FREE_SHIPPING', 100.00),
    'default_shipping_cost' => env('ECOMMERCE_DEFAULT_SHIPPING', 10.00),
    
    // Tax settings  
    'tax_calculation' => env('ECOMMERCE_TAX_CALCULATION', 'inclusive'), // inclusive or exclusive
    'default_tax_rate' => env('ECOMMERCE_TAX_RATE', 8.00),
    
    // Inventory settings
    'track_inventory' => env('ECOMMERCE_TRACK_INVENTORY', true),
    'allow_backorders' => env('ECOMMERCE_BACKORDERS', false),
    'low_stock_threshold' => env('ECOMMERCE_LOW_STOCK', 10),
    
    // Cart settings
    'cart_session_lifetime' => env('ECOMMERCE_CART_LIFETIME', 7), // days
    'persistent_cart' => env('ECOMMERCE_PERSISTENT_CART', true),
    
    // Integration settings
    'sync_with_pos' => env('ECOMMERCE_SYNC_POS', true),
    'award_loyalty_points' => env('ECOMMERCE_LOYALTY_POINTS', true),
];
