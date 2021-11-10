<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Email')) {
    return;
}

/**
 * Class WC_Custom_Email_Processing_Order
 */
class WC_Custom_Email_Processing_Order extends WC_Email
{
    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Email slug we can use to filter other data.
        $this->id = 'wc_custom_email_processing_order';
        $this->title = __('Processing Order to Wine Celler', 'custom-wc-email');
        $this->description = __('An email sent to the wine celler when an order needs to be processed.', 'custom-wc-email');
        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = false;
        $this->heading = __( 'New Order: #{order_number}', 'woocommerce' );
        // translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
        $this->subject = __( '[{site_title}]: New order #{order_number}', 'woocommerce' );

        // Template paths.
        $this->template_html = 'emails/wc-custom-email-processing-order.php';
        $this->template_plain = 'emails/plain/wc-custom-email-processing-order.php';
        $this->template_base = CUSTOM_WC_EMAIL_PATH . 'templates/';

        // Action to which we hook onto to send the email.
        add_action('woocommerce_order_status_pending_to_processing_notification', array($this, 'trigger'));

        parent::__construct();
    }

    /**
     * Trigger Function that will send this email to the customer.
     *
     * @access public
     * @return void
     */
    public function trigger($order_id)
    {
        $this->object = wc_get_order($order_id);
        $this->placeholders['{order_number}'] = $this->object->get_order_number();

        // *********************************************************
        // DO NOT SEND EMAIL TO WINE CELLAR IN TEST MODE (LOCALHOST)
        // *********************************************************
        // if ($_SERVER['HTTP_HOST'] === 'www.catasdevinoonline.com' || $_SERVER['HTTP_HOST'] === 'catasdevinoonline.com') {
        $email_recipient = '';
        if ($this->object) {
            $items = $this->object->get_items();
            foreach ($items as $item) {
                $product_id = $item->get_product_id();
                $categories = $this->get_category_slug($product_id);
                if (!in_array('accesorios-de-vino', $categories)) {
                    // There should be only one tasting item, so we need to find it.
                    $wine_cellar_emails = get_post_meta($product_id, '_wine_cellar_email', true);
                    if (!empty($wine_cellar_emails)) {
                        $emails = explode(',', $wine_cellar_emails);
                        $first = true;
                        foreach ($emails as $email) {
                            if ($first) {
                                $email_recipient .= $email;
                                $first = false;
                            } else {
                                $email_recipient .= ', ' . $email;
                            }
                        }
                    }
                    break; // Tasting product found
                }
            }
        }
        //}

        $this->recipient = $email_recipient;

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'            => $this
        ), '', $this->template_base);
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html($this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'            => $this
        ), '', $this->template_base);
    }

    /**
     * Get category(ies)
     */
    protected function get_category_slug($product_id): array
    {
        $terms = get_the_terms($product_id, 'product_cat');
        $categories = [];

        if ($terms) {
            foreach ($terms as $term) {
                array_push($categories, $term->slug);
            }
        }
        return $categories;
    }
}
