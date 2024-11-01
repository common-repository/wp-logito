<?php


class LogitoOrder {

	private $orderID;
	private $destinationCityId;
	private $sendTypeId;
	private $paymentTypeId;
	private $orderDetails;
	private $firstName;
	private $lastName;
	private $email;
	private $mobile;
	private $telephone;
	private $postalCode;
	private $message;
	private $address;
	private $ipAddress;
	private $productDetails;

	function __construct( $orderId = null ) {
		if ( $orderId == null ) {
			return false;
		}

		// Get woocommerce order
		$order = new WC_Order( $orderId );

		$this->setOrderID( $orderId );
		$this->setDestinationCityId( $order->shipping_city );
		$this->setSendTypeId( $order->get_shipping_method(), $order );
		$this->setPaymentTypeId( $order->payment_method );
		$this->setOrderDetails( $order->get_items() );
		$this->setFirstName( $order->shipping_first_name );
		$this->setLastName( $order->shipping_last_name );
		$this->setEmail( $order->billing_email );
		$this->setMobile( $order->billing_phone );
		$this->setTelephone( $order->billing_phone );
		$this->setPostalCode( $order->shipping_postcode );
		$this->setAddress( $order->shipping_address_1 . " " . $order->shipping_address_2 );
		$this->setIpAddress( $order->customer_ip_address );
		$this->setProductDetails( $order->get_items() );

		return true;
	}

	public static function addOrder( $orderId ) {
		$order = new LogitoOrder( $orderId );

		$orderData = array(
			"order"    => array(
				"DestinationCityId" => intval( $order->getDestinationCityId() ),
				"SendTypeId"        => intval( $order->getSendTypeId() ),
				"PaymentTypeId"     => intval( $order->getPaymentTypeId() ),
				"OrderDetails"      => $order->getOrderDetails(),
				"FirstName"         => $order->getFirstName(),
				"LastName"          => $order->getLastName(),
				"Email"             => $order->getEmail(),
				"Mobile"            => $order->getMobile(),
				"Telephone"         => $order->getTelephone(),
				"PostalCode"        => $order->getPostalCode(),
				"Address"           => $order->getAddress(),
				"MarketPartnerId"   => null,
				"Message"           => null,
				"Description"       => "",
				"IpAddress"         => $order->getIpAddress()
			),
			"products" => array(
				"ProductDetails" => $order->getProductDetails()
			)
		);

		$createOrder = new CreateOrder( $orderData, "CreateOrder", $orderId );

		if ( $createOrder->getResponseCode() == 0 ) {
			$response = $createOrder->getResponse();

			update_post_meta( $orderId, "LogitoShippingCode", $response->ShippingCode );
		}

		self::setOrderCity( $orderId );
	}

	public static function setOrderCity( $orderId ) {
		include_once( plugin_dir_path( __FILE__ ) . "/../woocommerce/CitiesAndStates/Cities/IR.php" );

		global $cities;

		$cityMetas = array( "_billing_city", "_shipping_city" );

		foreach ( $cityMetas as $meta ) {
			$metaValue = get_post_meta( $orderId, $meta, true );

			if ( ! is_numeric( $metaValue ) ) {
				continue;
			}

			$citiesFiltered = wp_list_pluck( $cities['IR'], $metaValue );

			$city = $metaValue;

			if ( $citiesFiltered[1] == null ) {
				foreach ( $citiesFiltered as $c ) {
					if ( ! empty( $c ) ) {
						$city = $c;
						break;
					}
				}
			} else {
				$city = $citiesFiltered[1];
			}

			if ( strlen( $city ) > 0 ) {
				update_post_meta( $orderId, $meta, $city );
			}
		}
	}

	public static function getOrderAccountUsername( $orderId ) {
		$orderAccount = get_post_meta( $orderId, "LogitoAccountUsername", true );

		return ( $orderAccount == null ) ? "-" : $orderAccount;
	}

	public static function getOrderShippingCode( $orderId ) {
		$shippingCode = get_post_meta( $orderId, "LogitoShippingCode", true );

		return ( $shippingCode == null ) ? 0 : $shippingCode;
	}

	public static function getOrderStatus( $orderId ) {
		$shippingCode = get_post_meta( $orderId, "LogitoShippingCode", true );

		if ( empty( $shippingCode ) ) {
			return "-";
		}

		$savedStatus = get_post_meta( $orderId, "LogitoOrderStatus", true );

		if ( $savedStatus ) {
			return $savedStatus;
		}

		$status = new GetStatus( array( "status" => array( "ShippingCode" => $shippingCode ) ), "GetStatus", $orderId );

		$response = $status->getResponse();

		$orderStatus = update_post_meta( $orderId, "LogitoOrderStatus", $response->OrderStatusTitle );
		update_post_meta( $orderId, "LogitoOrderStatusId", $response->OrderStatusId );

		return ( $status->getResponseCode() == 0 ) ? $response->OrderStatusTitle : "-";
	}

	public static function getOrderStatusId( $orderId ) {
		return get_post_meta( $orderId, "LogitoOrderStatusId", $orderId );

	}

	public static function changeOrderStatus( $orderId, $newStatusId ) {
		$changeStatus = new ChangeStatus( array(
			"status" => array(
				"ShippingCode" => LogitoOrder::getOrderShippingCode( $orderId ),
				"Status"       => $newStatusId
			)
		), "ChangeStatus", $orderId );

		$LogtioOrderStatus = array(
			1 => __( "تحت بررسی", "Logito" ),
			2 => __( "آماده ارسال", "Logito" ),
		);

		if ( $changeStatus->getResponseCode() == 0 ) {
			update_post_meta( $orderId, "LogitoOrderStatus", $LogtioOrderStatus[ $newStatusId ] );
			update_post_meta( $orderId, "LogitoOrderStatusId", $newStatusId );
		}

	}

	public static function updatePaymentStatus( $orderId ) {
		$orderShippingCode = LogitoOrder::getOrderShippingCode( $orderId );

		if ( $orderShippingCode <= 0 ) {
			return false;
		}

		$order = new WC_Order( $orderId );

		if ( $order->get_payment_method() == "cod" ) {
			return;
		}

		if ( ! $order->is_paid() ) {
			return false;
		}

		$amount = $order->get_total();

		// Common Toman abbreviations which used in woocommerce
		$TomanAbbreviations = array( "IRT", "IRHT" );

		if ( in_array( get_woocommerce_currency(), $TomanAbbreviations ) ) {
			$amount = $amount * 10;
		}

		$fillPaymentData = array(
			"fillOnlinePayment" => array(
				"ShippingCode" => $orderShippingCode,
				"Paid"         => $order->is_paid(),
				"ReferralId"   => "1",
				"AuthorityId"  => "1",
				"OnlineAmount" => $amount
			)
		);

		$fillOnlinePayment = new FillOnlinePayment( $fillPaymentData, "FillOnlinePayment", $orderId );

		return ( $fillOnlinePayment->getResponseCode() == 0 ) ? true : false;
	}

	/**
	 * @return mixed
	 */
	public function getOrderID() {
		return $this->orderID;
	}

	/**
	 * @param mixed $orderID
	 */
	public function setOrderID( $orderID ) {
		$this->orderID = $orderID;
	}

	/**
	 * @return mixed
	 */
	public function getDestinationCityId() {
		return $this->destinationCityId;
	}

	/**
	 * @param mixed $destinationCityId
	 */
	public function setDestinationCityId( $destinationCityId ) {
		$this->destinationCityId = $destinationCityId;
	}

	/**
	 * @return mixed
	 */
	public function getSendTypeId() {
		return $this->sendTypeId;
	}

	/**
	 * @param $sendType
	 * @param $order WC_Order
	 */
	public function setSendTypeId( $sendType, $order ) {
		$shipping_items = $order->get_items( 'shipping' );

		$order_shipping_method_id = "";

		foreach ( $shipping_items as $el ) {
			$order_shipping_method_id = $el['method_id'];
		}

		Debug::Log( $order->calculate_shipping(), "Order shipping cost" );

		switch ( $order_shipping_method_id ) {
			case 'logito_certified_mail':
				$customerDelivery = LogitoCore::$LogitoCertifiedMailCode;
				break;
			case 'logito_express_mail':
				$customerDelivery = LogitoCore::$LogitoExpressMailCode;
				break;
			case 'logito_delivery_man':
				$customerDelivery = LogitoCore::$LogitoDeliveryManCode;
				break;
		}

		$this->sendTypeId = $customerDelivery;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentTypeId() {
		return $this->paymentTypeId;
	}

	/**
	 * @param mixed $paymentType
	 */
	public function setPaymentTypeId( $paymentType ) {
		switch ( $paymentType ) {
			case 'cod':
				$paymentType = LogitoCore::$LogitoCODCode;
				break;
			default:
				$paymentType = LogitoCore::$LogitoPaymentCode;
		}

		$this->paymentTypeId = $paymentType;
	}

	/**
	 * @return mixed
	 */
	public function getOrderDetails() {
		return $this->orderDetails;
	}

	/**
	 * @param mixed $orderProducts
	 */
	public function setOrderDetails( $orderProducts ) {
		// Loop through order items
		foreach ( $orderProducts as $orderItem ) {
			// Get product info
			$productObject = new LogitoProduct( $orderItem['product_id'] );

			if ( isset( $orderItem['variation_id'] ) && $orderItem['variation_id'] > 0 ) {
				$variationProduct = new WC_Product_Variation( $orderItem['variation_id'] );

				$productObject->setProductPrice( ( $variationProduct->is_on_sale() ) ? $variationProduct->get_sale_price() : $variationProduct->get_regular_price() );
				$productObject->setProductWeight( $variationProduct->get_weight() );

				// Add product to order items array
				$this->orderDetails[] = array(
					"ProductId" => $productObject->getProductID() . "" . $orderItem['variation_id'],
					"Amount"    => intval( $orderItem['qty'] ),
				);
			} else {
				// Add product to order items array
				$this->orderDetails[] = array(
					"ProductId" => $productObject->getProductID(),
					"Amount"    => intval( $orderItem['qty'] )
				);
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param mixed $firstName
	 */
	public function setFirstName( $firstName ) {
		$this->firstName = $firstName;
	}

	/**
	 * @return mixed
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @param mixed $lastName
	 */
	public function setLastName( $lastName ) {
		$this->lastName = $lastName;
	}

	/**
	 * @return mixed
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail( $email ) {
		$this->email = $email;
	}

	/**
	 * @return mixed
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @param mixed $mobile
	 */
	public function setMobile( $mobile ) {
		$this->mobile = $mobile;
	}

	/**
	 * @return mixed
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * @param mixed $telephone
	 */
	public function setTelephone( $telephone ) {
		$this->telephone = $telephone;
	}

	/**
	 * @return mixed
	 */
	public function getPostalCode() {
		return $this->postalCode;
	}

	/**
	 * @param mixed $postalCode
	 */
	public function setPostalCode( $postalCode ) {
		$this->postalCode = $postalCode;
	}

	/**
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param mixed $message
	 */
	public function setMessage( $message ) {
		$this->message = $message;
	}

	/**
	 * @return mixed
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param mixed $address
	 */
	public function setAddress( $address ) {
		$this->address = $address;
	}

	/**
	 * @return mixed
	 */
	public function getIpAddress() {
		return $this->ipAddress;
	}

	/**
	 * @param mixed $ipAddress
	 */
	public function setIpAddress( $ipAddress ) {
		$this->ipAddress = $ipAddress;
	}

	/**
	 * @return mixed
	 */
	public function getProductDetails() {
		return $this->productDetails;
	}

	/**
	 * @param mixed $productDetails
	 */
	public function setProductDetails( $productDetails ) {
		// Loop through order items
		foreach ( $productDetails as $orderItem ) {
			// Get product info
			$productObject = new LogitoProduct( $orderItem['product_id'] );

			if ( isset( $orderItem['variation_id'] ) && $orderItem['variation_id'] > 0 ) {
				$variationProduct = new WC_Product_Variation( $orderItem['variation_id'] );

				$productObject->setProductPrice( ( $variationProduct->is_on_sale() ) ? $variationProduct->get_sale_price() : $variationProduct->get_regular_price() );
				$productObject->setProductWeight( $variationProduct->get_weight() );

				// Add product to order items array
				$this->productDetails[] = array(
					"Price"     => floatval( $productObject->getProductPrice() ),
					"ProductId" => $productObject->getProductID() . "" . $orderItem['variation_id'],
					"Title"     => $productObject->getProductTitle() . " (" . urldecode( key( $variationProduct->get_attributes() ) ) . ")",
					"Weight"    => floatval( $productObject->getProductWeight() )
				);
			} else {
				// Add product to order items array
				$this->productDetails[] = array(
					"Price"     => floatval( $productObject->getProductPrice() ),
					"ProductId" => $productObject->getProductID(),
					"Title"     => $productObject->getProductTitle(),
					"Weight"    => floatval( $productObject->getProductWeight() )
				);
			}
		}
	}

}