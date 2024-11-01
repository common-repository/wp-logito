<?php

function LogitoExpressMail_method_init() {
	if ( ! class_exists( 'WC_LogitoExpressMail' ) ) {
		class WC_LogitoExpressMail extends WC_Shipping_Method {
			public $discountByCartCondition = '';
			public $discountByCartConditionProductNumber = 0;
			public $discountByCartConditionValue = 0;

			public $discountByPaymentType = '';
			public $discountByPaymentTypeValue = 0;

			public $discountByShipment = '';
			public $discountByShipmentValue = 0;

			public function __construct() {
				/* Shipment method definition */
				$this->id                 = 'logito_express_mail';
				$this->method_title       = __( 'پست پیشتاز', 'Logito' );
				$this->method_description = __( 'پست پیشتاز شرکت لجیتو', 'Logito' );

				$this->discountByShipment                   = $this->get_option( 'discountByShipment' );
				$this->discountByShipmentValue              = $this->get_option( 'discountByShipmentValue', 0 );
				$this->discountByPaymentType                = $this->get_option( 'discountByPaymentType' );
				$this->discountByPaymentTypeValue           = $this->get_option( 'discountByPaymentTypeValue', 0 );
				$this->discountByCartCondition              = $this->get_option( 'discountByCartCondition' );
				$this->discountByCartConditionProductNumber = $this->get_option( 'discountByCartConditionProductNumber', 0 );
				$this->discountByCartConditionValue         = $this->get_option( 'discountByCartConditionValue', 0 );

				$this->enabled = "yes";
				$this->init();

				$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
				$this->title   = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'پست پیشتاز', 'Logito' );

			}

			/**
			 * Init settings
			 *
			 * @access public
			 * @return void
			 */
			function init() {
				// Load the settings LogitoAPI
				$this->init_form_fields();
				$this->init_settings();

				// Save settings in admin
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array(
					$this,
					'process_admin_options'
				) );
			}

			/**
			 * Define settings field for this shipping
			 * @return void
			 */
			function init_form_fields() {
				$this->form_fields = array(

					'enabled' => array(
						'title'       => __( 'فعال', 'Logito' ),
						'type'        => 'checkbox',
						'description' => __( 'فعال کردن روش ارسال پست پیشتاز شرکت لجیتو', 'Logito' ),
						'default'     => 'yes'
					),

					'title'                                => array(
						'title'       => __( 'عنوان', 'Logito' ),
						'type'        => 'text',
						'description' => __( 'این عنوان در وبسایت نمایش داده می شود.', 'Logito' ),
						'default'     => __( 'پست پیشتاز', 'Logito' )
					),
					'discountByShipment'                   => array(
						'title'   => __( 'تخفیف براساس شیوه ارسال پست پیشتاز', 'Logito' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => '',
						'options' => array(
							''        => __( 'N/A', 'woocommerce' ),
							'free'    => __( 'رایگان', 'Logito' ),
							'percent' => __( 'تخفیف درصدی', 'Logito' ),
							'value'   => __( 'تخفیف مقداری', 'Logito' ),
						),
					),
					'discountByShipmentValue'              => array(
						'title'       => __( 'مقدار تخفیف براساس شیوه ارسال پست پیشتاز', 'Logito' ),
						'type'        => 'price',
						'placeholder' => wc_format_localized_price( 0 ),
						'default'     => '0',
						'description' => 'در صورتی در "تخفیف براساس شیوه ارسال پست پیشتاز" گزینه "رایگان" را انتخاب کرده اید لازم نیست این فیلد را ویرایش کنید، در صورتی که گزینه "تخفیف درصدی" را انتخاب کرده اید در صد تخفیف را وارد کنید و در صورتی که "تخفیف مقداری" را انتخاب کرده اید براساس ارز وبسایت مبلغ تخفیف را وارد نمایید.',
					),
					'discountByPaymentType'                => array(
						'title'   => __( 'تخفیف براساس شیوه پرداخت', 'Logito' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => '',
						'options' => array(
							''              => __( 'N/A', 'woocommerce' ),
							'cod'           => __( 'پرداخت در محل', 'Logito' ),
							'onlinePayment' => __( 'پرداخت آنلاین', 'Logito' ),
							'LogitoGateway' => __( 'پرداخت درگاه لجیتو', 'Logito' ),
						),
					),
					'discountByPaymentTypeValue'           => array(
						'title'       => __( 'مقدار تخفیف براساس شیوه پرداخت', 'Logito' ),
						'type'        => 'price',
						'placeholder' => wc_format_localized_price( 0 ),
						'default'     => '0',
						'description' => 'درصورتی که میخواهید مقدار تخفیف را بصورت درصد در نظر بگیرید، عددی بین 1 تا 100 را در مقدار وارد نمایید و در صورتی که میخواهید تخفیف را بصورت مقداری در نظر بگیرید مانند این نمونه(15000) مقدار را براساس ارز وبسایت وارد نمایید.',
					),
					'discountByCartCondition'              => array(
						'title'   => __( 'تخفیف براساس شرایط سبد خرید', 'Logito' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => '',
						'options' => array(
							''           => __( 'N/A', 'woocommerce' ),
							'cartCount'  => __( 'تعداد محصولات', 'Logito' ),
							'cartAmount' => __( 'مبلغ سبد', 'Logito' )
						),
					),
					'discountByCartConditionProductNumber' => array(
						'title'       => __( 'تعداد محصولات سبد خرید | مبلغ سبد خرید', 'Logito' ),
						'type'        => 'price',
						'placeholder' => wc_format_localized_price( 0 ),
						'default'     => '0',
						'description' => 'در صورتی که در "تخفیف براساس شرایط سبد خرید" گزینه "تعداد محصولات" را انتخاب کرده اید در این فیلد حداقل تعداد محصولاتی که سبد خرید برای افزودن تخفیف داشته باشد را وارد نمایید. و درصورتی که " مبلغ سبد" را انتخاب کرده اید، حداقل مبلغی که سفارش باید برای افزودن تخفیف داشته باشد را وارد نمایید.'
					),
					'discountByCartConditionValue'         => array(
						'title'       => __( 'مقدار تخفیف براساس شرایط سبد خرید', 'Logito' ),
						'type'        => 'price',
						'placeholder' => wc_format_localized_price( 0 ),
						'default'     => '0',
						'description' => 'درصورتی که میخواهید مقدار تخفیف را بصورت درصد در نظر بگیرید، عددی بین 1 تا 100 را در مقدار وارد نمایید و در صورتی که میخواهید تخفیف را بصورت مقداری در نظر بگیرید مانند این نمونه(15000) مقدار را براساس ارز وبسایت وارد نمایید.',
					),
				);
			}

			public function isFreeShippingOrDiscount( $package, $shipmentAmount ) {

				$isFreeShipping   = false;
				$amountOfDiscount = 0;
				$discountPercent  = 0;

				switch ( $this->discountByShipment ) {
					case "free":
						$isFreeShipping = true;
						break;

					case "percent":
						$discountPercent = $this->discountByShipmentValue;
						break;

					case "value":
						$amountOfDiscount = $this->discountByShipmentValue;
						break;
				}

				switch ( $this->discountByPaymentType ) {
					case "cod":
						if ( $this->discountByPaymentTypeValue >= 1 && $this->discountByPaymentTypeValue <= 100 ) {
							if ( $this->discountByPaymentTypeValue == 100 ) {
								$isFreeShipping = true;
							} else {
								$discountPercent += $this->discountByPaymentTypeValue;
							}
						} else {
							$amountOfDiscount += $this->discountByPaymentTypeValue;
						}
						break;

					case "onlinePayment":
						if ( $this->discountByPaymentTypeValue >= 1 && $this->discountByPaymentTypeValue <= 100 ) {
							if ( $this->discountByPaymentTypeValue == 100 ) {
								$isFreeShipping = true;
							} else {
								$discountPercent += $this->discountByPaymentTypeValue;
							}
						} else {
							$amountOfDiscount += $this->discountByPaymentTypeValue;
						}
						break;

					case "LogitoGateway":
						if ( $this->discountByPaymentTypeValue >= 1 && $this->discountByPaymentTypeValue <= 100 ) {
							if ( $this->discountByPaymentTypeValue == 100 ) {
								$isFreeShipping = true;
							} else {
								$discountPercent += $this->discountByPaymentTypeValue;
							}
						} else {
							$amountOfDiscount += $this->discountByPaymentTypeValue;
						}
						break;
				}

				$cartTotalAmount = WC()->cart->get_displayed_subtotal();

				if ( 'incl' === WC()->cart->tax_display_cart ) {
					$cartTotalAmount = $cartTotalAmount - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() );
				} else {
					$cartTotalAmount = $cartTotalAmount - WC()->cart->get_cart_discount_total();
				}

				switch ( $this->discountByCartCondition ) {
					case "cartCount":
						$requireCountToDiscount = $this->discountByCartConditionProductNumber;

						if ( WC()->cart->get_cart_contents_count() <= $requireCountToDiscount ) {
							if ( $requireCountToDiscount <= WC()->cart->get_cart_contents_count() ) {
								if ( $this->discountByCartConditionValue >= 1 && $this->discountByCartConditionValue <= 100 ) {
									if ( $this->discountByCartConditionValue == 100 ) {
										$isFreeShipping = true;
									} else {
										$discountPercent += $this->discountByCartConditionValue;
									}
								} else {
									$amountOfDiscount += $this->discountByCartConditionValue;
								}
							}
						}

						break;

					case "cartAmount":


						$requireAmountToDiscount = $this->discountByCartConditionProductNumber;

						if ( $requireAmountToDiscount <= $cartTotalAmount ) {
							if ( $this->discountByCartConditionValue >= 1 && $this->discountByCartConditionValue <= 100 ) {
								if ( $this->discountByCartConditionValue == 100 ) {
									$isFreeShipping = true;
								} else {
									$discountPercent += $this->discountByCartConditionValue;
								}
							} else {
								$amountOfDiscount += $this->discountByCartConditionValue;
							}
						}

						break;
				}

				if ( $discountPercent > 0 ) {
					$percentToValue   = ( $shipmentAmount * $discountPercent ) / 100;
					$amountOfDiscount += $percentToValue;
				}

				return array(
					"isFreeShipping"   => $isFreeShipping,
					"amountOfDiscount" => $amountOfDiscount
				);
			}

			/**
			 * calculate_shipping function.
			 *
			 * @access public
			 *
			 * @param mixed $package
			 *
			 * @return bool
			 */
			public function calculate_shipping( $package = array() ) {
				if ( $this->enabled != "yes" ) {
					return false;
				}

				// Set city id
				$cityID = intval( $package['destination']['city'] );

				// Stop operation in case of user didn't choose any city
				if ( ! is_numeric( $cityID ) || $cityID <= 0 ) {
					return false;
				}

				$weight = 0;

				$_product = null;

				// Loop through order items and get their weight
				foreach ( $package['contents'] as $item_id => $values ) {
					$_product = $values['data'];

					// Product object
					$productObject = new LogitoProduct( $_product->id );

					if ( isset( $_product->variation_id ) && $_product->variation_id > 0 ) {
						$variationProduct = new WC_Product_Variation( $_product->variation_id );
						$weight           = $weight + intval( $variationProduct->get_weight() ) * $values['quantity'];
					} else {
						$weight = $weight + $_product->get_weight() * $values['quantity'];
					}
				}

				// Convert weight to grams
				$weight = wc_get_weight( $weight, 'g' );

				// Get users payment method
				$paymentMethod = WC()->session->get( 'chosen_payment_method' );

				// Set payment method id for web service
				if ( $paymentMethod == 'cod' ) {
					$paymentMethod = LogitoCore::$LogitoCODCode;
				} else {
					$paymentMethod = LogitoCore::$LogitoPaymentCode;
				}

				// Common Toman abbreviations which used in woocommerce
				$TomanAbbreviations = array( "IRT", "IRHT" );

				if ( in_array( get_woocommerce_currency(), $TomanAbbreviations ) ) {
					$package['contents_cost'] = $package['contents_cost'] * 10;
				}

				// Collect data to an array to sent to server
				$cartData = array(
					"CityId"        => intval( $cityID ),
					"SendTypeId"    => intval( LogitoCore::$LogitoExpressMailCode ),
					"PaymentTypeId" => intval( $paymentMethod ),
					"TotalPrice"    => floatval( $package['contents_cost'] ),
					"Weight"        => intval( $weight ),
					"ExactPrice"    => false
				);

				// Check if shipment costs were in the session
				if ( WC()->session->get( 'ExpressMailCartShippingCostData' ) == $cartData ) {
					Debug::Log( WC()->session->get( 'ExpressMailCartShippingCostRate' ), "Shipment costs returns from session" );

					$rate = $this->calculateDiscount( $package, WC()->session->get( 'ExpressMailCartShippingCostRate' ) );

					// Set delivery amount
					$this->add_rate( $rate );

					return true;
				}

				// Send data to server
				$shippingCosts = new CalculatePrice( array( "calculation" => $cartData ), "CalculatePrice", $cartData['TotalPrice'] );

				if ( $shippingCosts->getResponseCode() == 0 ) {
					$result = $shippingCosts->getResponse();

					$totalPrice  = $result->ProductsPrice + ( $result->ServicePrice - 4360 ) + $result->TaxPrice;
					$factorPrice = $totalPrice + $result->PostPrice;
					$amount      = $factorPrice - $package['contents_cost'];

					if ( in_array( get_woocommerce_currency(), $TomanAbbreviations ) ) {
						$amount = $amount / 10;
					}
					// This is where you'll add your rates
					$rate = array(
						'label' => $this->title,
						'cost'  => $amount,
						'id'    => $this->id
					);

					$rate = $this->calculateDiscount( $package, $rate );

					// Set retrieved shipping data in users session
					WC()->session->set( 'ExpressMailCartShippingCostData', $cartData );
					WC()->session->set( 'ExpressMailCartShippingCostRate', $rate );

					// Set delivery amount
					$this->add_rate( $rate );

				} else {
					// Set shipping data to null in users session
					WC()->session->set( 'ExpressMailCartShippingCostData', null );

					WC()->session->set( 'LogitoCartDiscount', null );

				}

				return true;
			}

			/**
			 * @param $package
			 * @param $rate
			 *
			 * @return mixed
			 */
			private function calculateDiscount( $package, $rate ) {
				$freeShippingOrDiscount = $this->isFreeShippingOrDiscount( $package, $rate['amount'] );

				// Set cost to Zero in case of conditional free shipping
				if ( $freeShippingOrDiscount['isFreeShipping'] == true ) {
					$rate['label'] .= " " . __( "(ارسال رایگان)", "Logito" );
					$rate['cost']  = 0;
				} else {
					$discount = $freeShippingOrDiscount['amountOfDiscount'];
					if ( $discount > 0 ) {
						WC()->session->set( 'LogitoCartDiscount', - floatval( $discount ) );
					}
				}

				return $rate;
			}
		}
	}

	function add_LogitoExpressMail_shipping_method( $methods ) {
		// Move to top
		array_unshift( $methods, 'WC_LogitoExpressMail' );

		return $methods;
	}

// Define shipment method to woocommerce
	add_filter( 'woocommerce_shipping_methods', 'add_LogitoExpressMail_shipping_method' );
}

add_action( 'woocommerce_shipping_init', 'LogitoExpressMail_method_init' );