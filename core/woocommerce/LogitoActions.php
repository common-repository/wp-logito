<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/31/2017
 * Time: 10:28 AM
 */

class LogitoActions {
	public function __construct() {
		$submitOrders = get_option( 'submitOrderLogito' );

		if ( ! $submitOrders || $submitOrders == null ) {
			update_option( 'submitOrderLogito', 'submitAfterPayment' );
			$submitOrders = get_option( 'submitOrderLogito' );
		}

		if ( empty( $submitOrders ) || $submitOrders == "submitAfterPayment" ) {
			add_action( 'woocommerce_payment_complete', array( &$this, 'addOrder' ), 1, 1 );
		}

		add_action( 'woocommerce_checkout_order_processed', array( &$this, 'addOrderCOD' ), 10, 1 );
	}


	public function addOrder( $orderId ) {
		$order        = new WC_Order( $orderId );
		$submitOrders = get_option( 'submitOrderLogito' );

		if ( $submitOrders == "submitAfterPayment" && $order->is_paid() ) {
			LogitoOrder::addOrder( $orderId );
		}
	}

	public function addOrderCOD( $orderId ) {
		$order = new WC_Order( $orderId );

		if ( $order->get_payment_method() == "cod" ) {
			LogitoOrder::addOrder( $orderId );
		}
	}
}