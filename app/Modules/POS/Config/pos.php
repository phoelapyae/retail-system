<?php

return [
    'name' => 'Point of Sale',
    'version' => '1.0.0',
    'description' => 'Complete POS system with register management',
    
    // Tax settings
    'default_tax_rate' => env('POS_TAX_RATE', 8.00),
    'tax_inclusive' => env('POS_TAX_INCLUSIVE', false),
    
    // Receipt settings
    'receipt_printer_enabled' => env('POS_RECEIPT_PRINTER', false),
    'email_receipts' => env('POS_EMAIL_RECEIPTS', true),
    'receipt_template' => 'pos::receipts.default',
    
    // Register settings
    'auto_open_cash_drawer' => env('POS_AUTO_OPEN_DRAWER', true),
    'require_manager_approval' => [
        'refunds_over' => env('POS_REFUND_APPROVAL_LIMIT', 100.00),
        'discounts_over' => env('POS_DISCOUNT_APPROVAL_LIMIT', 50.00),
        'void_sales' => env('POS_VOID_APPROVAL', true),
    ],
    
    // Integration settings
    'sync_with_ecommerce' => env('POS_SYNC_ECOMMERCE', true),
    'award_loyalty_points' => env('POS_LOYALTY_POINTS', true),
    
    // Performance settings
    'cache_products' => env('POS_CACHE_PRODUCTS', true),
    'cache_duration' => env('POS_CACHE_DURATION', 3600), // 1 hour
];