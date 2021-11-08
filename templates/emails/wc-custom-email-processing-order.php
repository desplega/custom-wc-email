<?php

/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails\HTML
 * @version 3.7.0
 */

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>

<?php /* translators: %s: Customer billing full name */ ?>
<p><?php printf(esc_html__('You’ve received the following order from %s:', 'woocommerce'), $order->get_formatted_billing_full_name()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
    ?></p>

<?php
/*
 *-----------------------------------------------------------------------------------------------------
 * Following section is a customised version of "email-order-details" and "email-order-items" templates
 *-----------------------------------------------------------------------------------------------------
 */
?>

<h2>
	<?php
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $text_align  = is_rtl() ? 'right' : 'left';
            $margin_side = is_rtl() ? 'left' : 'right';

            foreach ($order->get_items() as $item_id => $item) :
                $product       = $item->get_product();
                $sku           = '';
                $purchase_note = '';
                $image         = '';

                if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
                    continue;
                }

                /**
                 * Skip if category is "Accesorios"
                 */
                $accessory = false;
                $terms = get_the_terms($product->id, 'product_cat');
                if ($terms) {
                    foreach ($terms as $term) {
                        if ($term->slug == 'accesorios') {
                            $accessory = true;
                            break;
                        }
                    }
                    if ($accessory)
                        continue;
                } else {
                    continue; // Unclassified product
                }

                if (is_object($product)) {
                    $sku           = $product->get_sku();
                    $purchase_note = $product->get_purchase_note();
                    $image         = $product->get_image($image_size);
                }
            ?>
                <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                        <?php

                        // Show title/image etc.
                        if ($show_image) {
                            echo wp_kses_post(apply_filters('woocommerce_order_item_thumbnail', $image, $item));
                        }

                        // Product name.
                        echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));

                        // SKU.
                        if ($show_sku && $sku) {
                            echo wp_kses_post(' (#' . $sku . ')');
                        }

                        // allow other plugins to add additional product information here.
                        do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text);

                        wc_display_item_meta(
                            $item,
                            array(
                                'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr($text_align) . '; margin-' . esc_attr($margin_side) . ': .25em; clear: both">',
                            )
                        );

                        // allow other plugins to add additional product information here.
                        do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text);

                        ?>
                    </td>
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                        <?php
                        $qty          = $item->get_quantity();
                        $refunded_qty = $order->get_qty_refunded_for_item($item_id);

                        if ($refunded_qty) {
                            $qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
                        } else {
                            $qty_display = esc_html($qty);
                        }
                        echo wp_kses_post(apply_filters('woocommerce_email_order_item_quantity', $qty_display, $item));
                        ?>
                    </td>
                </tr>
                <?php
                if ($show_purchase_note && $purchase_note) {
                ?>
                    <tr>
                        <td colspan="3" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <?php
                            echo wp_kses_post(wpautop(do_shortcode($purchase_note)));
                            ?>
                        </td>
                    </tr>
                <?php
                }
                ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php
            if ($order->get_customer_note()) {
            ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Note:', 'woocommerce'); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php echo wp_kses_post(nl2br(wptexturize($order->get_customer_note()))); ?></td>
                </tr>
            <?php
            }
            ?>
        </tfoot>
    </table>
</div>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email); ?>

<?php
/*
 *-----------------------------------------------------------------------------------------------
 * End of section (customised version of "email-order-details" and "email-order-items" templates)
 *----------------------------------------------------------------------------------------------
 */
?>

<?php
/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
