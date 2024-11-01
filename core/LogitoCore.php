<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/20/2017
 * Time: 7:33 PM
 */

class LogitoCore {

	public static $LogitoCertifiedMailCode = 1;
	public static $LogitoExpressMailCode = 2;
	public static $LogitoDeliveryManCode = 3;
	public static $LogitoCODCode = 1;
	public static $LogitoPaymentCode = 2;

	public function __construct() {
		// Debug class
		include( "Debug.php" );

		// Check if woocommerce plugin enabled
		if ( $this->isWoocommerceInstalled() ) {
			add_action( 'admin_notices', array( &$this, 'woocommerceIssueNotice' ) );

			return false;
		}


		// Plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( __FILE__ ) ) . 'wp-logito.php' ), array(
			&$this,
			'adminPluginSettingsLink'
		) );

		$this->API();
		$this->extraSections();
		$this->model();
		$this->pages();
		$this->systemHooks();
		$this->woocommerce();
	}

	public function API() {
		include_once( 'API/LogitoAPI.php' );

		foreach ( glob( trailingslashit( dirname( __FILE__ ) ) . '/API/APIImpl/*.php' ) as $service ) {
			include_once( $service );
		}
	}

	public function extraSections() {
		include_once( 'extraSections/AdminSections.php' );
		new AdminSections();
		include_once( 'extraSections/UserSections.php' );
		new UserSections();

	}

	public function model() {
		include_once( 'model/LogitoProduct.php' );
		include_once( 'model/LogitoOrder.php' );

	}

	public function pages() {
		include_once( 'pages/LogitoOptionPage.php' );
		new LogitoOptionPage();
	}

	public function systemHooks() {
		include( "systemHooks/LogitoHooks.php" );
		new LogitoHooks();
	}

	public function woocommerce() {
		include( "woocommerce/LogitoActions.php" );
		new LogitoActions();

		include( "woocommerce/CitiesAndStates/States/IR.php" );

		include( "woocommerce/CitiesAndStates/Cities.php" );
		new Cities();

		include( "woocommerce/Gateway/LogitoGateway.php" );

		include( "woocommerce/ShippingMethods/CertifiedMail.php" );
		include( "woocommerce/ShippingMethods/DeliveryMan.php" );
		include( "woocommerce/ShippingMethods/ExpressMail.php" );
		include( "woocommerce/ShippingMethods/updateCheckout.php" );
		new updateCheckout();
	}

	private function isWoocommerceInstalled() {
		return ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) );
	}

	public function woocommerceIssueNotice() {
		?>
        <div class="notice notice-info is-dismissible">
            <p><?php _e( 'برای استفاده از افزونه لجیتو باید افزونه ووکامرس را نصب و فعال کنید.', 'Logito' ); ?></p>
        </div>
		<?php
	}

	public function adminPluginSettingsLink( $links ) {
		$settings_link = '<a href="' . admin_url( "/options-general.php?page=Logito-options" ) . '">' . __( 'پیکربندی', 'Logito' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}