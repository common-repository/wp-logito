<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/31/2017
 * Time: 9:29 PM
 */

class LogitoHooks {
	public function __construct() {

		add_action( 'woocommerce_payment_complete', array( &$this, 'completeOrder' ), 10, 1 );
	}

	public function completeOrder( $orderId ) {
		LogitoOrder::updatePaymentStatus( $orderId );
	}

}