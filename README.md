# CashonRail Payment Gateway for Membership Pro

## 📌 Overview
CashonRail is a custom WordPress plugin that integrates with Membership Pro to provide seamless payment processing. This plugin enables users to make transactions securely and efficiently.

## 🚀 Features
- Supports secure payment transactions via CashonRail.
- Seamless integration with **Membership Pro**.
- Admin settings page for API key configuration.
- Webhook support for handling payment responses.

## 🔧 Installation
1. **Upload Plugin**:
   - Download the plugin as a `.zip` file.
   - Go to `WordPress Admin > Plugins > Add New > Upload Plugin`.
   - Select the `.zip` file and click **Install Now**.
   - Activate the plugin.

2. **Manual Installation**:
   - Upload the plugin folder to `/wp-content/plugins/` directory.
   - Activate it from `WordPress Admin > Plugins`.

## ⚙️ Configuration
1. Navigate to `Settings > CashonRail Payment`.
2. Enter your Secret Key as the **API Key**.
3. Save changes.

## 📜 Webhook Setup
- Configure your webhook to:  

- Ensure the webhook is correctly set in your **CashonRail Dashboard**.

## 🖥️ Usage
After successfully installing and configuring the plugin, users can make payments as follows:

1. **Customer Registration & Checkout**
- A user selects a membership plan and proceeds to checkout.
- On the payment page, they select **CashonRail** as their payment method.
- Clicking **Pay Now** redirects them to the CashonRail payment page.

2. **Processing Payment**
- The customer enters their payment details and completes the transaction.
- If successful, they are redirected back to your site with a confirmation message.

3. **Payment Confirmation**
- Membership Pro updates the user’s membership status upon successful payment.
- Admins can verify payments from the **Membership Pro > Orders** section.

## 🛠 Troubleshooting
- **Payment Not Redirecting?**
- Ensure that the API Key is correctly set.
- Check error logs (`wp-content/debug.log` if `WP_DEBUG` is enabled).

- **Webhook Not Triggering?**
- Verify that your webhook URL is correctly configured in your **CashonRail Dashboard**.
- Ensure the webhook URL is publicly accessible.

## 📝 License
This plugin is licensed under the **MIT License**.

## 👨‍💻 Author
- **Henry** - Backend Developer.

## 🤝 Contributions
Feel free to open issues and submit pull requests!




