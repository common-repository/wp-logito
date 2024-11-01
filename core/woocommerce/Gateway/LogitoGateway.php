<?php
function LogitoGatewayInit() {
	function add_Logito_gateway_class( $methods ) {
		array_unshift( $methods, 'WC_Gateway_Logito' );

		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_Logito_gateway_class' );

	class WC_Gateway_Logito extends WC_Payment_Gateway {
		public function __construct() {

			/* Gateway definitions */
			$this->id                 = 'logito_gateway';
			$this->method_title       = __( 'درگاه لجیتو', 'Logito' );
			$this->method_description = __( 'تنظیمات درگاه شرکت لجیتو', 'Logito' );

			$this->has_fields = false;

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			// Define gateway options
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );

			/* register payment request and verify methods */
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'BpPayRequest' ) );
			add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ) . '', array( $this, 'BpVerifyRequest' ) );
		}

		/**
		 * Set option fields
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => __( 'فعال/غیرفعال', 'Logito' ),
					'type'    => 'checkbox',
					'label'   => __( 'فعال کردن درگاه لجیتو', 'Logito' ),
					'default' => 'yes'
				),
				'title'       => array(
					'title'       => __( 'عنوان', 'Logito' ),
					'type'        => 'text',
					'description' => __( 'این عنوان هنگام مشاهده صفحه پرداخت توسط مشتری دیده می شود.', 'Logito' ),
					'default'     => __( 'لجیتو', 'Logito' ),
				),
				'description' => array(
					'title'   => __( 'توضیحات درگاه', 'Logito' ),
					'type'    => 'textarea',
					'default' => ''
				)
			);
		}

		public function process_payment( $order_id ) {
			$order = new WC_Order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);
		}


		public function BpPayRequest( $order_id ) {
			// Get woocommerce order
			$order = new WC_Order( $order_id );
		}


		public function BpVerifyRequest() {
			global $woocommerce;

			$order_id = $_GET['wc_order'];

			// Get woocommerce order
			$order = new WC_Order( $order_id );

		}
	}
}

add_action( 'plugins_loaded', 'LogitoGatewayInit', 0 );