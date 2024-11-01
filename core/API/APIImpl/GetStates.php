<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:55 PM
 */

class GetStates extends LogitoAPI  {

	public function __construct( $params = array(), $service = "GetStates" ) {
		$this->setService( $service );
		$this->setData( $params );
		$this->doRequest();
	}
}