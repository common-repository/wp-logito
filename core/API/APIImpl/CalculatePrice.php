<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:55 PM
 */

class CalculatePrice extends LogitoAPI {

	public function __construct( $params = array(), $service = "CalculatePrice", $totalAmount = 0 ) {
		$this->setService( $service );
		$this->setData( $params );
		$this->setTotalAmount( $totalAmount );
		$this->doRequest();
	}
}