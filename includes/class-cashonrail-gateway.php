<?php

class MembershipPro_CashonRail_Gateway {

    public $gateway_name = "cashonrail";

    function __construct() {
        add_action('pmpro_checkout_after_pricing_fields', [$this, 'display_payment_fields']);
        add_action('pmpro_checkout_before_processing', [$this, 'process_payment']);
    }

    // Display Payment Fields at Checkout
    function display_payment_fields() {
        ?>
        <div>
            <label for="cashonrail_email">Email:</label>
            <input type="email" name="cashonrail_email" required />
        </div>
        <div>
            <label for="cashonrail_first_name">First Name:</label>
            <input type="text" name="cashonrail_first_name" required />
        </div>
        <div>
            <label for="cashonrail_last_name">Last Name:</label>
            <input type="text" name="cashonrail_last_name" required />
        </div>
        <?php
    }

    // Process Payment
    function process_payment($user_id) {
        if (!isset($_POST['cashonrail_email'], $_POST['cashonrail_first_name'], $_POST['cashonrail_last_name'])) {
            wp_die('All payment details are required.');
        }

        $email = sanitize_email($_POST['cashonrail_email']);
        $first_name = sanitize_text_field($_POST['cashonrail_first_name']);
        $last_name = sanitize_text_field($_POST['cashonrail_last_name']);

        // Check if PMPro function exists before calling it
        if (!function_exists('pmpro_getMembershipLevelForUser')) {
            wp_die('Membership level retrieval failed.');
        }

        $membership_level = pmpro_getMembershipLevelForUser($user_id);
        if (!$membership_level || !isset($membership_level->billing_amount)) {
            wp_die('Invalid membership level.');
        }

        $amount = strval($membership_level->billing_amount * 100); // Convert to kobo as string

        $api_url = 'https://mainapi.cashonrails.com/api/v1/transaction/initialize';
        $api_key = get_option('cashonrail_api_key'); // Retrieve API key from settings

        if (!$api_key) {
            wp_die('API key is missing. Please configure it in settings.');
        }

        $reference = 'cashonrail_' . uniqid(); // Generate unique reference

        $response = wp_remote_post($api_url, [
            'body' => json_encode([
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'amount' => $amount,
                'currency' => 'NGN',
                'reference' => $reference,
                'redirectUrl' => home_url('/payment-complete'),
                'logoUrl' => 'https://example.com/logo.png'
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ]
        ]);

        if (is_wp_error($response)) {
            wp_die('Payment request failed: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['success']) && $body['success'] === true && isset($body['data']['checkout_link'])) {
            wp_redirect($body['data']['checkout_link']);
            exit;
        } else {
            $error_message = isset($body['message']) ? $body['message'] : 'Unknown error occurred.';
            wp_die('Payment initialization failed: ' . $error_message);
        }
    }
}

// Initialize the gateway only if class doesn't already exist
if (!class_exists('MembershipPro_CashonRail_Gateway')) {
    new MembershipPro_CashonRail_Gateway();
}
