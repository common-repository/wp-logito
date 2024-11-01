<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:55 PM
 */

class CreateOrder extends LogitoAPI  {

	public function __construct( $params = array(), $service = "CreateOrder", $orderId = 0 ) {
		$this->setService( $service );
		$this->setData( $params );
		$this->setOrderId( $orderId );
		$this->doRequest();
	}
}