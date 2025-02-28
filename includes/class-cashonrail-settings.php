<?php

class MembershipPro_Cashonrail_Settings {

	function __construct() {
		if (is_admin()) {
			add_action('admin_menu', [$this, 'add_settings_page']);
			add_action('admin_init', [$this, 'register_settings']);
			add_filter('pmpro_payment_options', [$this, 'pmpro_payment_options']);
			add_action('pmpro_payment_option_fields', [$this, 'pmpro_payment_option_fields'], 10, 2);
		}
	}

	function add_settings_page() {
		add_options_page(
			'Cashonrail Payment Settings',
			'Cashonrail Payment',
			'manage_options',
			'cashonrail-payment-settings',
			[$this, 'settings_page']
		);
	}

	function register_settings() {
		register_setting('cashonrail_payment_options', 'cashonrail_api_key', ['sanitize_callback' => 'sanitize_text_field']);
		register_setting('cashonrail_payment_options', 'cashonrail_gateway_environment', ['sanitize_callback' => 'sanitize_text_field']);
		register_setting('cashonrail_payment_options', 'cashonrail_currency', ['sanitize_callback' => 'sanitize_text_field']);

		add_settings_section(
			'cashonrail_main_section',
			'API Configuration',
			null,
			'cashonrail-payment-options'
		);

		add_settings_field(
			'cashonrail_api_key',
			'API Key',
			[$this, 'api_key_field_callback'],
			'cashonrail-payment-options',
			'cashonrail_main_section'
		);

		add_settings_field(
			'cashonrail_currency',
			'Currency',
			[$this, 'currency_field_callback'],
			'cashonrail-payment-options',
			'cashonrail_main_section'
		);
	}

	function currency_field_callback() {
		$currency = get_option('cashonrail_currency', 'NGN'); // Default to NGN
		?>
        <select name="cashonrail_currency">
            <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar</option>
            <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro</option>
            <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP - British Pound</option>
            <option value="NGN" <?php selected($currency, 'NGN'); ?>>NGN - Nigerian Naira</option>
            <option value="KES" <?php selected($currency, 'KES'); ?>>KES - Kenyan Shilling</option>
        </select>
		<?php
	}

	function api_key_field_callback() {
		$api_key = get_option('cashonrail_api_key', '');
		echo "<input type='text' name='cashonrail_api_key' value='" . esc_attr($api_key) . "' class='regular-text'>";
	}

	function settings_page() {
		?>
        <div class="wrap">
            <h1>Cashonrail Payment Settings</h1>
            <form method="post" action="options.php">
				<?php
				settings_fields('cashonrail_payment_options');
				do_settings_sections('cashonrail-payment-options');
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	static function pmpro_payment_options($options) {
		$cashonrail_options = [
			'cashonrail_api_key',
			'cashonrail_gateway_environment',
			'cashonrail_currency'
		];
		return array_merge($options, $cashonrail_options);
	}

	static function pmpro_payment_option_fields($values, $gateway) {
		$currency = $values['cashonrail_currency'] ?? get_option('cashonrail_currency', 'NGN'); // Default to NGN
		?>
        <tr class="gateway gateway_cashonrail" <?php if($gateway != "cashonrail") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="cashonrail_api_key">Live Secret Key:</label>
            </th>
            <td>
                <input type="text" id="cashonrail_api_key" name="cashonrail_api_key" size="60" value="<?php echo esc_attr($values['cashonrail_api_key'] ?? ''); ?>" />
            </td>
        </tr>
        <tr class="gateway gateway_cashonrail" <?php if($gateway != "cashonrail") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="cashonrail_currency">Currency:</label>
            </th>
            <td>
                <select name="cashonrail_currency">
                    <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar</option>
                    <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro</option>
                    <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP - British Pound</option>
                    <option value="NGN" <?php selected($currency, 'NGN'); ?>>NGN - Nigerian Naira</option>
                    <option value="KES" <?php selected($currency, 'KES'); ?>>KES - Kenyan Shilling</option>
                </select>
            </td>
        </tr>
        <tr class="gateway gateway_cashonrail" <?php if($gateway != "cashonrail") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label>Webhook:</label>
            </th>
            <td>
                <p>To fully integrate with Cashonrail, use the following Webhook URL:<br/><code><?php echo admin_url("admin-ajax.php") . "?action=pmpro_cashonrail_ipn"; ?></code></p>
            </td>
        </tr>
		<?php
	}
}

new MembershipPro_Cashonrail_Settings();
?>
