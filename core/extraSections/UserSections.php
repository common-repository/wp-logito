<?php

class UserSections {
	function __construct() {
		add_action( 'woocommerce_my_account_my_orders_column_LogitoShippingCode', array(
			&$this,
			'LogitoShippingCode'
		) );
		add_filter( 'woocommerce_my_account_my_orders_columns', array(
			&$this,
			'LogitoShippingCodeUserOrdersColumn'
		), 10, 1 );


		add_action( 'woocommerce_thankyou', array( &$this, "AddShippingCodeToThankYou" ), 1 );

		add_filter( 'woocommerce_default_address_fields', array( &$this, "reOrderCheckOutFields" ) );
	}

	function reOrderCheckOutFields( $fields ) {

		$fields['city']['priority']  = 80;
		$fields['state']['priority'] = 70;

		return $fields;
	}

	function AddShippingCodeToThankYou( $order_id ) {
		?>
        <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

            <li class="woocommerce-order-overview__order shippingCode"><?php _e( "کد رهگیری پستی:", "Logito" ); ?>
                <strong><?php echo LogitoOrder::getOrderShippingCode( $order_id ); ?></strong>
            </li>
        </ul>
		<?php
	}

	function LogitoShippingCodeUserOrdersColumn( $columns ) {
		$new_columns = array(
			'LogitoShippingCode' => __( 'شماره رهگیری', 'Logito' ),
		);

		return array_merge( $columns, $new_columns );
	}


	function LogitoShippingCode( $order ) {
		echo LogitoOrder::getOrderShippingCode( $order->id );
	}

}