<?php

class MembershipPro_CashonRail_Gateway {

	public $gateway_name = "cashonrail";

	function __construct() {
		// Disable billing and payment information fields
		add_filter('pmpro_include_billing_address_fields', '__return_false');
		add_filter('pmpro_include_payment_information_fields', '__return_false', 20);
		add_filter('pmpro_include_billing_address_fields', '__return_false');
		add_filter('pmpro_required_billing_fields', '__return_empty_array');

		add_action('pmpro_checkout_after_payment_information_fields', [$this, 'display_payment_fields']);
		add_action('pmpro_checkout_before_processing', [$this, 'process_payment']);
	}

	// Display Payment Fields at Checkout
	function display_payment_fields() {
		static $is_rendered = false;

		if ($is_rendered) {
			return; // Prevent duplicate rendering
		}

		// Ensure the selected gateway is "cashonrail"
		$selected_gateway = isset($_REQUEST['gateway']) ? sanitize_text_field($_REQUEST['gateway']) : '';

		if ($selected_gateway !== $this->gateway_name) {
			return; // Do not display fields if another gateway is selected
		}

		$is_rendered = true;
		?>
        <div id="pmpro_cashonrail_fields" class="pmpro_checkout">
            <h3>CashOnRail Payment Details</h3>

            <div class="pmpro_checkout-field">
                <label for="cashonrail_email">Email:</label>
                <input type="email" id="cashonrail_email" name="cashonrail_email" required />
            </div>

            <div class="pmpro_checkout-field">
                <label for="cashonrail_first_name">First Name:</label>
                <input type="text" id="cashonrail_first_name" name="cashonrail_first_name" required />
            </div>

            <div class="pmpro_checkout-field">
                <label for="cashonrail_last_name">Last Name:</label>
                <input type="text" id="cashonrail_last_name" name="cashonrail_last_name" required />
            </div>

            <input type="hidden" name="gateway" value="cashonrail" />
        </div>

        <style>
            #pmpro_cashonrail_fields {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .pmpro_checkout-field {
                margin-bottom: 10px;
            }
            .pmpro_checkout-field label {
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .pmpro_checkout-field input {
                width: 100%;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
        </style>
		<?php
	}

	// Process Payment
	function process_payment($user_id) {
		// Ensure PMPro functions are available
		if (!function_exists('pmpro_getTaxForPrice')) {
			require_once PMPRO_DIR . "/includes/tax.php";
		}

		if (!isset($_POST['cashonrail_email'], $_POST['cashonrail_first_name'], $_POST['cashonrail_last_name'])) {
			wp_die('All payment details are required.');
		}

		$email = sanitize_email($_POST['cashonrail_email']);
		$first_name = sanitize_text_field($_POST['cashonrail_first_name']);
		$last_name = sanitize_text_field($_POST['cashonrail_last_name']);

		if (!function_exists('pmpro_getMembershipLevelForUser')) {
			wp_die('Membership level retrieval failed.');
		}

		$membership_level = pmpro_getMembershipLevelForUser($user_id);

		if (!$membership_level || !isset($membership_level->billing_amount) || $membership_level->billing_amount <= 0) {
			wp_die('Invalid membership level. Please ensure your membership is active.');
		}

		// Corrected amount handling from Paystack logic
		$amount = $membership_level->billing_amount;
		$amount_tax = function_exists('pmpro_getTaxForPrice') ? pmpro_getTaxForPrice($amount) : 0;
		$amount = round((float)$amount + (float)$amount_tax, 2);

		// Convert to kobo (lowest currency unit)
		$amount = intval($amount * 100);

		$api_url = 'https://mainapi.cashonrails.com/api/v1/transaction/initialize';
		$api_key = get_option('cashonrail_api_key');

		if (!$api_key) {
			wp_die('API key is missing. Please configure it in settings.');
		}

		$reference = 'cashonrail_' . uniqid();

		$response = wp_remote_post($api_url, [
			'method'    => 'POST',
			'body'      => json_encode([
				'email'       => $email,
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'amount'      => $amount,
				'currency'    => isset($pmpro_currency) ? $pmpro_currency : 'NGN',
				'reference'   => $reference,
				'redirectUrl' => pmpro_url("confirmation", "?level=" . $membership_level->id),
				'logoUrl'     => 'https://example.com/logo.png'
			]),
			'headers'   => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key
			]
		]);

		if (is_wp_error($response)) {
			wp_die('Payment request failed: ' . $response->get_error_message());
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['success']) && !empty($body['data']['checkout_link'])) {
			wp_redirect($body['data']['checkout_link']);
			exit;
		} else {
			$error_message = !empty($body['message']) ? $body['message'] : 'Unknown error occurred.';
			wp_die('Payment initialization failed: ' . $error_message);
		}
	}


}

// Initialize the gateway
new MembershipPro_CashonRail_Gateway();
