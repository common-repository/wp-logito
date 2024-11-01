<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:35 PM
 */

class LogitoAPI {
	private $APIURL = "http://ws.logito.ir/cod.svc?wsdl";

	private $username;

	private $password;

	private $service;

	private $data;

	private $totalAmount;

	private $orderId;

	private $responseCode;

	private $responseMessage;

	private $response;

	protected function doRequest() {
		$this->beforeRequest();

		set_time_limit( 0 );

		Debug::Log( $this->getData(), "Current request's[" . $this->getService() . "] parameters", 'info' );

		$client = new SoapClient( $this->getAPIURL(), array(
			'trace'      => 1,
			'exceptions' => 0,
			'wsdl_cache' => 0
		) );


		$data = array();

		foreach ( $this->getData() as $param ) {
			foreach ( $param as $key => $item ) {
				$data[ $key ] = $item;
			}
		}


		$response = $client->__soapCall( $this->getService(), array(
				$this->getService() => $this->getData()
			)
		);

		$this->setResponseCode( $response->{$this->getService() . "Result"}->ResultCode );
		$this->setResponseMessage( $response->{$this->getService() . "Result"}->ResultMessage );
		$this->setResponse( $response->{$this->getService() . "Result"} );

		Debug::Log( $response, "Current request's[" . $this->getService() . "] response", 'info' );
	}

	protected function beforeRequest() {
		if ( ! array_key_exists( "credential", $this->getData() ) ) {

			$authentication = LogitoOptionPage::getLogitoAuthenticationData();

			$data = $this->getData();

			Debug::Log( $this->getAccount(), "account number" );

			if ( $this->getAccount() == 1 || ! isset( $authentication['LogitoUsername2'] ) || empty($authentication['LogitoUsername2']) ) {
				$credential = array(
					'credential' => array(
						"Username" => $authentication['LogitoUsername'],
						"Password" => $authentication['LogitoPassword']
					)
				);
			} else {
				$credential = array(
					'credential' => array(
						"Username" => $authentication['LogitoUsername2'],
						"Password" => $authentication['LogitoPassword2']
					)
				);
			}

			$data = $credential + $data;

			$this->setData( $data );
		}
	}

	protected function getAccount() {
		$authentication = LogitoOptionPage::getLogitoAuthenticationData();

		$account = 1;

		switch ( $this->getService() ) {
			case "CalculatePrice":

				$totalAmount = $this->getTotalAmount();

				$TomanAbbreviations = array( "IRT", "IRHT" );

				if ( in_array( get_woocommerce_currency(), $TomanAbbreviations ) ) {
					$totalAmount = $totalAmount / 10;
				}

				if ( $totalAmount >= $authentication['LogitoAccount2Condition'] ) {
					$account = 2;
				}

				break;
			case "CreateOrder":
				$order = new WC_Order( $this->getOrderId() );

				if ( $order->get_total() >= $authentication['LogitoAccount2Condition'] ) {
					$account = 2;
					update_post_meta( $order->get_id(), "LogitoAccountNumber", $account );
					update_post_meta( $order->get_id(), "LogitoAccountUsername", $authentication['LogitoUsername2'] );
				} else {
					update_post_meta( $order->get_id(), "LogitoAccountNumber", $account );
					update_post_meta( $order->get_id(), "LogitoAccountUsername", $authentication['LogitoUsername'] );
				}
				break;
			case "ChangeStatus":
			case "FillOnlinePayment":
			case "GetStatus":
				$account = get_post_meta( $this->getOrderId(), "LogitoAccountNumber", true );
				break;
		}

		return $account;
	}

	protected function afterRequest() {

	}

	/**
	 * @return string
	 */
	public function getAPIURL() {
		return $this->APIURL;
	}

	/**
	 * @param string $APIURL
	 */
	public function setAPIURL( $APIURL ) {
		$this->APIURL = $APIURL;
	}

	/**
	 * @return mixed
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param mixed $username
	 */
	public function setUsername( $username ) {
		$this->username = $username;
	}

	/**
	 * @return mixed
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword( $password ) {
		$this->password = $password;
	}

	/**
	 * @return mixed
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * @param mixed $service
	 */
	public function setService( $service ) {
		$this->service = $service;
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param mixed $data
	 */
	public function setData( $data ) {
		$this->data = $data;
	}

	/**
	 * @return mixed
	 */
	public function getTotalAmount() {
		return $this->totalAmount;
	}

	/**
	 * @param mixed $totalAmount
	 */
	public function setTotalAmount( $totalAmount ) {
		$this->totalAmount = $totalAmount;
	}

	/**
	 * @return mixed
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @param mixed $orderId
	 */
	public function setOrderId( $orderId ) {
		$this->orderId = $orderId;
	}

	/**
	 * @return mixed
	 */
	public function getResponseCode() {
		return $this->responseCode;
	}

	/**
	 * @param mixed $responseCode
	 */
	public function setResponseCode( $responseCode ) {
		$this->responseCode = $responseCode;
	}

	/**
	 * @return mixed
	 */
	public function getResponseMessage() {
		return $this->responseMessage;
	}

	/**
	 * @param mixed $responseMessage
	 */
	public function setResponseMessage( $responseMessage ) {
		$this->responseMessage = $responseMessage;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param mixed $response
	 */
	public function setResponse( $response ) {
		$this->response = $response;
	}
}