<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:59 PM
 */

class AdminSections {
	public function __construct() {
		add_action( 'manage_shop_order_posts_custom_column', array( &$this, 'WoocommerceShopOrderColumnData' ), 10, 2 );
		add_filter( 'manage_edit-shop_order_columns', array( &$this, 'WoocommerceShopOrderColumn' ) );


		add_action( 'add_meta_boxes', array( &$this, 'AdminMetaBox' ), 1 );
		add_action( 'save_post', array( &$this, 'OrderSaveMetaBox' ), 1, 2 );
	}

	function WoocommerceShopOrderColumn( $columns ) {
		$new_columns = array(
			'orderAccount' => __( 'حساب کاربری', 'Logito' ),
			'orderShippingCode' => __( 'شماره رهگیری', 'Logito' ),
			'orderStatus'       => __( 'وضعیت سفارش', 'Logito' ),
		);

		return array_merge( $columns, $new_columns );
	}

	function WoocommerceShopOrderColumnData( $column, $post_id ) {
		switch ( $column ) {
			case 'orderAccount':
				echo LogitoOrder::getOrderAccountUsername( $post_id );
				break;
			case 'orderShippingCode':
				echo LogitoOrder::getOrderShippingCode( $post_id );
				break;
			case 'orderStatus':
				echo LogitoOrder::getOrderStatus( $post_id );
				break;
		}
	}


	/**
	 * Register meta box(es).
	 */
	function AdminMetaBox() {
		add_meta_box( 'meta-box-id', __( 'لجیتو', 'Logito' ), array(
			&$this,
			'OrderMetaBoxRender'
		), 'shop_order', 'side' );
	}


	function OrderMetaBoxRender( $post ) {
		global $post;

		$shippingCode = LogitoOrder::getOrderShippingCode( $post->ID );

		if ( ! is_numeric( $shippingCode ) || empty( $shippingCode ) ) {

			echo '<span id="post-status-display">' . __( 'این سفارش دارای کد پیگیری نیست. در نتیجه امکان ویرایش آن وجود ندارد.', 'Logito' ) . '</span>';

			return true;
		}

		echo '<input type="hidden" name="Logito_Order_attributes_nonce" id="Logito_Order_attributes_nonce" value="' .
		     wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';

		if ( ! metadata_exists( 'post', $post->ID, 'LogitoOrderStatus' ) ) {

			LogitoOrder::getOrderStatus( $post->ID );
		}

		$LogitoOrderStatus = LogitoOrder::getOrderStatus( $post->ID );

		$LogitoOrderAccount = LogitoOrder::getOrderAccountUsername( $post->ID );

		$LogitoShippingCode = LogitoOrder::getOrderShippingCode( $post->ID );

		$LogtioOrderStatus = array(
			1 => __( "تحت بررسی", "Logito" ),
			2 => __( "آماده ارسال", "Logito" ),
		);
		?>
        <p><label for="LogitoOrderStatus"><?php _e( "وضعیت سفارش", "Logito" ); ?></label><br>
            <select name="LogitoOrderStatus" id="LogitoOrderStatus">
				<?php foreach ( $LogtioOrderStatus as $key => $status ) { ?>
                    <option
                            value="<?php echo $key; ?>" <?php echo ( trim( $status ) == trim( $LogitoOrderStatus ) ) ? 'selected' : ''; ?>>
						<?php echo $status; ?>
                    </option>
				<?php } ?>
            </select></p>
        <p><label for="LogitoShippingCode"><?php _e( "کد رهگیری", "Logito" ); ?></label><br>
			<?php
			echo '<input class="input" id="LogitoShippingCode" name="LogitoShippingCode" value="' . $LogitoShippingCode . '" type="text" disabled/><br>';
			?>
        </p>
        <p><label for="LogitoShippingCode"><?php _e( "حساب کاربری", "Logito" ); ?></label><br>
			<?php
			echo '<input class="input" id="LogitoOrderAccount" name="LogitoOrderAccount" value="' . $LogitoOrderAccount . '" type="text" disabled/><br>';
			?>
        </p>
		<?php
	}


	public function OrderSaveMetaBox( $post_id, $post ) {
		if ( ! isset( $_POST['Logito_Order_attributes_nonce'] ) ) {
			return false;
		}
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( ! wp_verify_nonce( $_POST['Logito_Order_attributes_nonce'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}

		// Is the user allowed to edit the post or page?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		$newStatus = $_POST['LogitoOrderStatus'];

		if ( $newStatus != LogitoOrder::getOrderStatusId( $post->id ) ) {
			LogitoOrder::changeOrderStatus( $post->ID, $newStatus );
		}

		return true;
	}
}