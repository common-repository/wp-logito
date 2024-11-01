<?php

/**
 * For persian woocommerce conflict
 */
if ( ! class_exists( 'Persian_Woocommerce_Address' ) ) {
	class Persian_Woocommerce_Address {
	}

} else {
	if ( in_array( 'persian-woocommerce/woocommerce-persian.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$persianWoocommerceAddress = WP_PLUGIN_DIR . "/persian-woocommerce/include/class-address.php";
		if ( file_exists( $persianWoocommerceAddress ) ) {
			file_put_contents( $persianWoocommerceAddress, "" );
		}
	}
}

class Cities {
	private $cities;

	public function __construct() {
		/* Replace woocommerce default city field with new one */
		add_filter( 'woocommerce_billing_fields', array( $this, 'billingFields' ) );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'shippingFields' ) );
		add_filter( 'woocommerce_form_field_city', array( $this, 'formFieldCity' ), 10, 4 );

		//js scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'loadScripts' ) );
	}

	public function billingFields( $fields ) {
		$fields['billing_city']['type'] = 'city';

		return $fields;
	}

	public function shippingFields( $fields ) {
		$fields['shipping_city']['type'] = 'city';

		return $fields;
	}

	public function get_cities( $countryKey = null ) {
		if ( empty( $this->cities ) ) {
			$this->loadCountryCities();
		}

		if ( ! is_null( $countryKey ) ) {
			return isset( $this->cities[ $countryKey ] ) ? $this->cities[ $countryKey ] : false;
		} else {
			return $this->cities;
		}
	}

	public function loadCountryCities() {
		global $cities;

		// Load only the city files the shop owner wants/needs.
		$allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );
		if ( $allowed ) {
			foreach ( $allowed as $code => $country ) {
				if ( ! isset( $cities[ $code ] ) && file_exists( plugin_dir_path( __FILE__ ) . '/Cities/' . $code . '.php' ) ) {
					include( plugin_dir_path( __FILE__ ) . '/Cities/' . $code . '.php' );
				}
			}
		}

		$this->cities = apply_filters( 'wc_city_select_cities', $cities );
	}

	/**
	 * Define field city
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public function formFieldCity( $field, $key, $args, $value ) {
		// Do we need a clear div?
		if ( ( ! empty( $args['clear'] ) ) ) {
			$after = '<div class="clear"></div>';
		} else {
			$after = '';
		}

		// Required markup
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Validate classes
		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		// field p and label
		$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_field">';
		if ( $args['label'] ) {
			$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
		}

		// Get Country
		$country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
		$current_cc  = WC()->checkout->get_value( $country_key );

		$state_key  = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
		$current_sc = WC()->checkout->get_value( $state_key );
		// Get country cities
		$cities = $this->get_cities( $current_cc );

		if ( is_array( $cities ) ) {

			$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">
					<option value="">' . __( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

			if ( $current_sc && isset( $cities[ $current_sc ] ) && $cities[ $current_sc ] ) {
				$dropdown_cities = $cities[ $current_sc ];
			} else if ( is_array( reset( $cities ) ) ) {
				$dropdown_cities = array_reduce( $cities, 'array_merge', array() );
				sort( $dropdown_cities );
			} else {
				$dropdown_cities = $cities;
			}

			foreach ( $dropdown_cities as $city_name ) {
				$field .= '<option value="' . esc_attr( $city_name ) . '" ' . selected( $value, $city_name, false ) . '>' . $city_name . '</option>';
			}

			$field .= '</select>';

		} else {

			$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
		}

		// field description and close wrapper
		if ( $args['description'] ) {
			$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
		}

		$field .= '</p>' . $after;

		return $field;
	}

	public function loadScripts() {
		if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {

			$city_select_path = plugins_url( 'assets/city-select.js', dirname( dirname( dirname( __FILE__ ) ) ) );

			wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), '1.0.0', true );

			$cities = json_encode( $this->get_cities() );
			wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
				'cities'                => $cities,
				'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
			) );
		}
	}
}

