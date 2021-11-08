<?php

/**
 * Plugin Name: Custom WooCommerce Email
 * Description: Custom email to the Wine Celler
 * Author: desplega
 * Author URI: https://www.desplega.com
 * Text Domain: custom-wc-email
 * Domain Path: /languages/
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    return;
}

/**
 * Class Custom_WC_Email
 */
class Custom_WC_Email
{
    /**
     * Custom_WC_Email constructor.
     */
    public function __construct()
    {
        // Filtering the emails and adding our own email.
        add_filter('woocommerce_email_classes', array($this, 'register_email'), 90, 1);
        // Absolute path to the plugin folder.
        define('CUSTOM_WC_EMAIL_PATH', plugin_dir_path(__FILE__));
    }

    /**
     * @param array $emails
     *
     * @return array
     */
    public function register_email($emails)
    {
        require_once 'emails/class-wc-custom-email-processing-order.php';

        $emails['WC_Customer_Cancel_Order'] = new WC_Custom_Email_Processing_Order();

        return $emails;
    }
}

new Custom_WC_Email();
