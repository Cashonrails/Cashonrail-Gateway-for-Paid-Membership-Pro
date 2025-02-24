<?php
/**
 * Plugin Name: CashOnRail Payment Gateway
 * Plugin URI: https://example.com
 * Description: A custom payment gateway for Membership Pro using CashOnRail.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin directory path
$plugin_includes_path = plugin_dir_path(__FILE__) . 'includes/';

// Include required files only if they exist
$required_files = [
    'class-cashonrail-gateway.php',
    'class-cashonrail-settings.php',
    'membershippro-cashonrail.php',
];

foreach ($required_files as $file) {
    $file_path = $plugin_includes_path . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("CashOnRail Plugin Error: Missing required file - " . $file_path);
    }
}

// Initialize the gateway only if the class exists
if (!function_exists('cashonrail_init')) {
    function cashonrail_init() {
        if (class_exists('MembershipPro_Cashonrail_Gateway')) {
            new MembershipPro_Cashonrail_Gateway();
        } else {
            error_log("CashOnRail Plugin Error: MembershipPro_Cashonrail_Gateway class not found.");
        }
    }
    add_action('plugins_loaded', 'cashonrail_init');
}
