<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:55 PM
 */

class GetCities extends LogitoAPI {

	public function __construct( $params = array(), $service = "GetCities" ) {
		$this->setService( $service );
		$this->setData( $params );
		$this->doRequest();
	}
}