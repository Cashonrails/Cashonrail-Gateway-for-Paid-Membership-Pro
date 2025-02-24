<?php
/**
 * Plugin Name: Membership Pro BudPay Gateway
 * Plugin URI: https://yourwebsite.com
 * Description: BudPay Payment Gateway for Membership Pro.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Include required files only if they exist
$gateway_path = plugin_dir_path(__FILE__) . 'includes/class-cashonrail-gateway.php';
$settings_path = plugin_dir_path(__FILE__) . 'includes/class-cashonrail-settings.php';

if (file_exists($gateway_path)) {
    include_once $gateway_path;
} else {
    error_log("CashOnRail Gateway file not found: $gateway_path");
}

if (file_exists($settings_path)) {
    include_once $settings_path;
} else {
    error_log("CashOnRail Settings file not found: $settings_path");
}

// Register BudPay Gateway
function register_budpay_gateway($gateways) {
    if (class_exists('MembershipPro_CashonRail_Gateway')) {
        $gateways['cashonrail'] = 'MembershipPro_CashonRail_Gateway';
    }
    return $gateways;
}

if (function_exists('add_filter')) {
    add_filter('pmpro_gateways', 'register_budpay_gateway');
}
