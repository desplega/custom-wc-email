<?php
/**
 * Admin new order email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails\Plain
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer billing full name */
echo sprintf( esc_html__( 'You’ve received the following order from %s:', 'woocommerce' ), esc_html( $order->get_formatted_billing_full_name() ) ) . "\n\n";

/*
 *-----------------------------------------------------------------------------------------------------
 * Following section is a customised version of "email-order-details" and "email-order-items" templates
 *-----------------------------------------------------------------------------------------------------
 */
/* translators: %1$s: Order ID. %2$s: Order date */
echo wp_kses_post( wc_strtoupper( sprintf( esc_html__( '[Order #%1$s] (%2$s)', 'woocommerce' ), $order->get_order_number(), wc_format_datetime( $order->get_date_created() ) ) ) ) . "\n";

foreach ( $order->get_items() as $item_id => $item ) :
	if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		$product       = $item->get_product();
		$sku           = '';
		$purchase_note = '';

        /**
         * Skip if category is "Accesorios de vino"
         */
        $accessory = false;
        $terms = get_the_terms($product->id, 'product_cat');
        if ($terms) {
            foreach ($terms as $term) {
                if ($term->slug == 'accesorios-de-vino') {
                    $accessory = true;
                    break;
                }
            }
            if ($accessory)
                continue;
        } else {
            continue; // Unclassified product
        }

		if ( is_object( $product ) ) {
			$sku           = $product->get_sku();
			$purchase_note = $product->get_purchase_note();
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );
		if ( $show_sku && $sku ) {
			echo ' (#' . $sku . ')';
		}
		echo ' X ' . apply_filters( 'woocommerce_email_order_item_quantity', $item->get_quantity(), $item );
		echo "\n";
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		// allow other plugins to add additional product information here.
		do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo strip_tags(
			wc_display_item_meta(
				$item,
				array(
					'before'    => "\n- ",
					'separator' => "\n- ",
					'after'     => '',
					'echo'      => false,
					'autop'     => false,
				)
			)
		);

		// allow other plugins to add additional product information here.
		do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
	}
	// Note.
	if ( $show_purchase_note && $purchase_note ) {
		echo "\n" . do_shortcode( wp_kses_post( $purchase_note ) );
	}
	echo "\n\n";
endforeach;

echo "==========\n\n";

if ( $order->get_customer_note() ) {
	echo esc_html__( 'Note:', 'woocommerce' ) . "\t " . wp_kses_post( wptexturize( $order->get_customer_note() ) ) . "\n";
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
echo "\n";

/*
 *-----------------------------------------------------------------------------------------------
 * End of section (customised version of "email-order-details" and "email-order-items" templates)
 *----------------------------------------------------------------------------------------------
 */

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
