<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:55 PM
 */

class ChangeStatus extends LogitoAPI  {

	public function __construct( $params = array(), $service = "ChangeStatus" , $orderId = 0 ) {
		$this->setService( $service );
		$this->setData( $params );
		$this->setOrderId( $orderId );
		$this->doRequest();
	}
}