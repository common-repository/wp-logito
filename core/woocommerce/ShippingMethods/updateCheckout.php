<?php

class updateCheckout {
	function __construct() {
		// add javascript code
		add_action( 'wp_footer', array( &$this, 'updateCheckoutAfterChangePaymentMethod' ) );

		// Recalculate shipment in update checkout
		add_action( 'woocommerce_checkout_update_order_review', array(
			&$this,
			'woocommerceCheckoutUpdateOrderReview'
		) );

		add_action( 'woocommerce_cart_calculate_fees', array( &$this, 'LogitoAddDiscount' ) );
	}

	// Append discount to cart
	function LogitoAddDiscount() {
		if ( WC()->session->get( 'LogitoCartDiscount' ) != null && WC()->session->get( 'LogitoCartDiscount' ) < 0 ) {
			global $woocommerce;

			$woocommerce->cart->add_fee( __( 'تخفیف', 'Logito' ), WC()->session->get( 'LogitoCartDiscount' ) );
		}
	}


	/**
	 * This method will be called when customer
	 * changes payment type for updating shipping
	 * costs.
	 */
	function woocommerceCheckoutUpdateOrderReview() {
		// Recalculate shipping
		WC()->cart->calculate_shipping();

		return;
	}

	/**
	 * javascript code detect payment method changing
	 * and fires update_checkout
	 */
	function updateCheckoutAfterChangePaymentMethod() {
		?>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('body')
                    .on('updated_checkout', function () {
                        jQuery('input[name="payment_method"]').change(function () {
                            updateCheckoutAfterChangePaymentMethod();
                        });
                    });
            });

            function updateCheckoutAfterChangePaymentMethod() {
                jQuery('body').trigger('update_checkout');
            }
        </script>
		<?php
	}
}