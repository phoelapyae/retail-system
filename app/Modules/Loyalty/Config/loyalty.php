<?php

return [
    'name' => 'Loyalty Program',
    'version' => '1.0.0',
    'description' => 'Comprehensive customer loyalty and rewards system',
    
    // Points settings
    'points_per_dollar' => env('LOYALTY_POINTS_PER_DOLLAR', 1),
    'points_value' => env('LOYALTY_POINTS_VALUE', 0.01), // $0.01 per point
    'points_expiry_months' => env('LOYALTY_POINTS_EXPIRY', 12),
    'minimum_redemption' => env('LOYALTY_MIN_REDEMPTION', 100),
    
    // Tier settings
    'enable_tiers' => env('LOYALTY_TIERS_ENABLED', true),
    'tier_upgrade_immediate' => env('LOYALTY_IMMEDIATE_UPGRADE', true),
    'tier_downgrade_protection' => env('LOYALTY_DOWNGRADE_PROTECTION', 6), // months
    
    // Referral settings
    'enable_referrals' => env('LOYALTY_REFERRALS_ENABLED', true),
    'referrer_points' => env('LOYALTY_REFERRER_POINTS', 500),
    'referee_points' => env('LOYALTY_REFEREE_POINTS', 100),
    'referral_expiry_days' => env('LOYALTY_REFERRAL_EXPIRY', 30),
    
    // Campaign settings
    'enable_campaigns' => env('LOYALTY_CAMPAIGNS_ENABLED', true),
    'max_campaigns_per_customer' => env('LOYALTY_MAX_CAMPAIGNS', 3),
    
    // Notification settings
    'email_notifications' => env('LOYALTY_EMAIL_NOTIFICATIONS', true),
    'sms_notifications' => env('LOYALTY_SMS_NOTIFICATIONS', false),
    'expiry_notification_days' => env('LOYALTY_EXPIRY_NOTIFICATION', 30),
    
    // Integration settings
    'pos_points_multiplier' => env('LOYALTY_POS_MULTIPLIER', 1.0),
    'ecommerce_points_multiplier' => env('LOYALTY_ECOMMERCE_MULTIPLIER', 1.0),
    'birthday_bonus_points' => env('LOYALTY_BIRTHDAY_BONUS', 100),
    
    // Advanced settings
    'enable_point_pooling' => env('LOYALTY_POINT_POOLING', false), // Family accounts
    'enable_point_transfers' => env('LOYALTY_POINT_TRANSFERS', false),
    'transfer_fee_percentage' => env('LOYALTY_TRANSFER_FEE', 5.0),
];
