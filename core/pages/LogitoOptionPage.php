<?php
/**
 * Created by PhpStorm.
 * User: Zanyar Abdolahzadeh
 * Date: 10/22/2017
 * Time: 8:05 PM
 */

class LogitoOptionPage {

	private $options;
	private static $optionName = "logitoOptions";

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'adminOptionsSubMenu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );

		add_action( 'admin_footer', array( $this, 'LogitoAuthenticationJavascript' ) );
		add_action( 'wp_ajax_LogitoAuthentication', array( $this, 'LogitoAuthentication' ) );

		if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'Logito-options' ) ) {

			if ( isset( $_GET['delete'] ) && $_GET['delete'] == "successful" ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'حساب کاربری 2 باموفقیت حذف شد.', 'Logito' ); ?></p>
                    </div>
					<?php
				} );
			}
			if ( isset( $_GET['submitOrders'] ) && $_GET['submitOrders'] == "successful" ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'زمان ثبت سفارشات باموفقیت بروزرسانی شد.', 'Logito' ); ?></p>
                    </div>
					<?php
				} );
			}

			if ( ! isset( $_GET['tab'] ) ) {
				$_GET['tab'] = 'account1';
			}
		}
	}

	public function adminOptionsSubMenu() {
		add_options_page(
			__( 'پیکربندی لجیتو', 'Logito' ),
			__( 'پیکربندی افزونه لجیتو', 'Logito' ),
			'manage_options',
			'Logito-options',
			array( $this, 'logitoOptionsPage' )
		);
	}

	public function logitoOptionsPage() {

		$this->deleteAccount();
		$this->submitOrder();

		$this->options = get_option( self::$optionName );

		?>
        <div class="wrap">
            <a href="http://logito.ir" target="_blank">
                <img
                        src="<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/logito.png'; ?>"
                        width="80px"
                        alt="<?php _e( 'لجیتو', 'Logito' ); ?>">
            </a>
            <h1><?php _e( 'پیکربندی لجیتو', 'Logito' ); ?></h1>
            <div class="notice notice-info">
                <p class="main">
                    <strong><?php
						$register = sprintf( "<a href='http://logito.ir/Register' target='_blank'>%s</a>", __( "عضو", "Logito" ) );
						printf( __( 'برای استفاده از این افزونه باید در وبسایت لجیتو %s باشید.', 'Logito' ), $register ); ?></strong>
                </p>
            </div>

            <div id="wrongLoginData" class="update-nag notice" style="display: none">
                <p><?php _e( "نام کاربری یا رمز عبور اشتباه است.", "Logito" ); ?></p>
            </div>
            <div id="successLogin" class="notice notice-success" style="display: none">
                <p><?php _e( "اعتبار سازی با موفقیت انجام شد.", "Logito" ); ?></p>
            </div>
            <ul class="subsubsub">
                <li>
                    <a href="<?php echo admin_url( "options-general.php?page=Logito-options&tab=account1" ); ?>"
                       class="<?php echo ( ( isset( $_GET['tab'] ) && $_GET['tab'] == "account1" ) || ! isset( $_GET['tab'] ) ) ? "current" : null; ?>"><?php _e( "حساب کاربری 1", "Logito" ); ?></a>
                    |
                </li>
                <li>
                    <a href="<?php echo admin_url( "options-general.php?page=Logito-options&tab=account2" ); ?>"
                       class="<?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] == "account2" ) ? "current" : null; ?>"><?php _e( "حساب کاربری 2", "Logito" ); ?></a>
                    |
                </li>
                <li>
                    <a href="<?php echo admin_url( "options-general.php?page=Logito-options&tab=submitOrders" ); ?>"
                       class="<?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] == "submitOrders" ) ? "current" : null; ?>"><?php _e( "ثبت سفارشات", "Logito" ); ?></a>
                </li>
            </ul>
            <br>

			<?php
			if ( isset( $_GET['tab'] ) && $_GET['tab'] != "submitOrders" ) {
				echo '<form method="post" action="options.php">';
				settings_fields( 'logitoOptionsGroup' );
				do_settings_sections( 'Logito-options' );

				if ( isset( $_GET['tab'] ) && $_GET['tab'] == "account2" ) {
					$deleteAccountUrl = admin_url( "options-general.php?page=Logito-options&tab=account2&delete=true" );

					echo "&nbsp;<a class=\"button\" href='$deleteAccountUrl'>" . __( 'حذف حساب', 'Logito' ) . "</a> ";
				}

				echo "&nbsp;<button class=\"button\" id=\"checkAuthentication\">" . __( 'بررسی اتصال', 'Logito' ) . "</button> ";
				submit_button( __( 'ذخیره', 'Logito' ), 'primary', 'submit-form', false );
				echo '</form>';
			} else {
				$submitOrders = get_option( 'submitOrderLogito' );
				?>
                <form action="" method="post">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row"><?php _e( "زمان ثبت سفارشات در سرور لجیتو", "Logito" ); ?></th>
                            <td>
								<?php _e( "میخواهم سفارشات وبسایت من", "Logito" ); ?>
                                <select name="submitOrderLogito" id="submitOrderLogito">
                                    <option value="submitAfterPayment" <?php echo( $submitOrders == "submitAfterPayment" ? 'selected' : null ); ?>><?php _e( "بعد", "Logito" ) ?></option>
                                    <option value="submitBeforePayment" <?php echo( $submitOrders == "submitBeforePayment" ? 'selected' : null ); ?>><?php _e( "قبل", "Logito" ) ?></option>
                                </select> <?php _e( "از اینکه کاربر مبلغ آن را پرداخت کرد در وبسایت لجیتو ثبت شود.", "Logito" ); ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <button class="button button-primary"
                            type="submit"><?php _e( "ذخیره تغییرات", "Logito" ) ?></button>
                </form>
				<?php
			}
			?>

        </div>
		<?php
	}

	private function deleteAccount() {
		if ( ! ( isset( $_GET['tab'] ) && $_GET['tab'] == "account2" && isset( $_GET['delete'] ) ) ) {
			return;
		}

		if ( $_GET['delete'] == "true" ) {
			$option = get_option( "logitoOptions" );

			unset( $option['LogitoUsername2'] );
			unset( $option['LogitoPassword2'] );
			unset( $option['LogitoAccount2Condition'] );

			update_option( "logitoOptions", $option );

			$redirect = admin_url( "options-general.php?page=Logito-options&tab=account2&delete=successful" );

			wp_redirect( "$redirect" );
			exit;
		}
	}

	private function submitOrder() {
		if ( ! ( isset( $_GET['tab'] ) && $_GET['tab'] == "submitOrders" && isset( $_POST['submitOrderLogito'] ) ) ) {
			return;
		}

		update_option( "submitOrderLogito", $_POST['submitOrderLogito'] );
		$redirect = admin_url( "options-general.php?page=Logito-options&tab=submitOrders&submitOrders=successful" );

		wp_redirect( "$redirect" );
		exit;
	}

	public function settings() {
		register_setting(
			'logitoOptionsGroup', // Option group
			self::$optionName, // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'authentication', // ID
			__( "اعتبار سازی", "Logito" ), // Title
			array( $this, 'print_section_info' ), // Callback
			'Logito-options' // Page
		);

		if ( isset( $_GET['tab'] ) && $_GET['tab'] == "account1" ) {
			add_settings_field(
				'LogitoUsername', // ID
				__( "نام کاربری", "Logito" ), // Title
				array( $this, 'usernameCallBack' ), // Callback
				'Logito-options', // Page
				'authentication' // Section
			);

			add_settings_field(
				'LogitoPassword',
				__( "کلمه عبور", "Logito" ),
				array( $this, 'passwordCallback' ),
				'Logito-options',
				'authentication'
			);
		} else if ( isset( $_GET['tab'] ) && $_GET['tab'] == "account2" ) {
			add_settings_field(
				'LogitoUsername2', // ID
				__( "نام کاربری", "Logito" ), // Title
				array( $this, 'usernameCallBack2' ), // Callback
				'Logito-options', // Page
				'authentication' // Section
			);

			add_settings_field(
				'LogitoPassword2',
				__( "کلمه عبور", "Logito" ),
				array( $this, 'passwordCallback2' ),
				'Logito-options',
				'authentication'
			);

			add_settings_field(
				'LogitoAccount2Condition',
				__( "حداقل مبلغ سفارش برای استفاده از این حساب کاربری", "Logito" ),
				array( $this, 'LogitoAccount2ConditionCallback' ),
				'Logito-options',
				'authentication'
			);
		}
	}


	public function sanitize( $input ) {
		$new_input = array();
		$data      = self::getLogitoAuthenticationData();

		if ( ! isset( $input['LogitoUsername'] ) ) {
			$input['LogitoUsername'] = $data['LogitoUsername'];
			$input['LogitoPassword'] = $data['LogitoPassword'];
		} else if ( ! isset( $input['LogitoUsername2'] ) && isset( $data['LogitoUsername2'] ) && ! empty( $data['LogitoUsername2'] ) ) {
			$input['LogitoUsername2']         = $data['LogitoUsername2'];
			$input['LogitoPassword2']         = $data['LogitoPassword2'];
			$input['LogitoAccount2Condition'] = $data['LogitoAccount2Condition'];
		}

		if ( isset( $input['LogitoUsername'] ) ) {
			$new_input['LogitoUsername'] = sanitize_text_field( $input['LogitoUsername'] );
		}
		if ( isset( $input['LogitoUsername2'] ) ) {
			$new_input['LogitoUsername2'] = sanitize_text_field( $input['LogitoUsername2'] );
		}
		if ( isset( $input['LogitoAccount2Condition'] ) ) {
			$new_input['LogitoAccount2Condition'] = sanitize_text_field( $input['LogitoAccount2Condition'] );
		}

		if ( isset( $input['LogitoPassword'] ) && $input['LogitoPassword'] == '######' ) {
			$savedAuthenticationData = self::getLogitoAuthenticationData();

			$new_input['LogitoPassword'] = sanitize_text_field( $savedAuthenticationData['LogitoPassword'] );
		} else {
			$new_input['LogitoPassword'] = sanitize_text_field( $input['LogitoPassword'] );
		}

		if ( isset( $input['LogitoPassword2'] ) && $input['LogitoPassword2'] == '######' ) {
			$savedAuthenticationData = self::getLogitoAuthenticationData();

			$new_input['LogitoPassword2'] = sanitize_text_field( $savedAuthenticationData['LogitoPassword2'] );
		} else {
			$new_input['LogitoPassword2'] = sanitize_text_field( $input['LogitoPassword2'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		_e( 'مشخصات حساب کاربری خود را وارد نمایید:', "Logito" );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function usernameCallback() {
		printf(
			'<input type="text" id="LogitoUsername" name="' . self::$optionName . '[LogitoUsername]" value="%s" />',
			isset( $this->options['LogitoUsername'] ) ? esc_attr( $this->options['LogitoUsername'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function usernameCallback2() {
		printf(
			'<input type="text" id="LogitoUsername2" name="' . self::$optionName . '[LogitoUsername2]" value="%s" />',
			isset( $this->options['LogitoUsername2'] ) ? esc_attr( $this->options['LogitoUsername2'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function LogitoAccount2ConditionCallback() {
		printf(
			'<input type="text" id="LogitoAccount2Condition" name="' . self::$optionName . '[LogitoAccount2Condition]" value="%s" /><p class="description">%s</p>',
			isset( $this->options['LogitoAccount2Condition'] ) ? esc_attr( $this->options['LogitoAccount2Condition'] ) : '', __( "مبلغ را براساس ارز وبسایت وارد نمایید", "Logito" )
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function passwordCallback() {
		printf(
			'<input type="password" id="LogitoPassword" name="' . self::$optionName . '[LogitoPassword]" value="%s" />',
			isset( $this->options['LogitoPassword'] ) ? esc_attr( "######" ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function passwordCallback2() {
		printf(
			'<input type="password" id="LogitoPassword2" name="' . self::$optionName . '[LogitoPassword2]" value="%s" />',
			isset( $this->options['LogitoPassword2'] ) ? esc_attr( "######" ) : ''
		);
	}


	function LogitoAuthenticationJavascript() { ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#submit-form").attr("disabled", "disabled");

                $("#checkAuthentication").click(function (event) {

                    event.preventDefault();

                    var self = $(this);

                    self.attr("disabled", "disabled");

                    self.text("");

                    var loaderContainer = $('<span/>', {
                        'class': 'loader-image-container'
                    }).appendTo(self);

                    var loader = $('<img/>', {
                        src: '<?php echo site_url(); ?>' + '/wp-admin/images/spinner.gif',
                        'class': 'loader-image',
                        'style': 'margin-bottom: -5px;'
                    }).appendTo(loaderContainer);
                    self.append(" <?php _e( 'درحال اعتبار سازی...', 'Logito' ); ?>");

                    var data;

                    if ($("#LogitoUsername").length) {
                        data = {
                            'action': 'LogitoAuthentication',
                            'username': $("#LogitoUsername").val(),
                            'password': $("#LogitoPassword").val()
                        };
                    } else {
                        data = {
                            'action': 'LogitoAuthentication',
                            'username': $("#LogitoUsername2").val(),
                            'password': $("#LogitoPassword2").val(),
                            'account': 2
                        };
                    }

                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    jQuery.post(ajaxurl, data, function (response) {
                        response = jQuery.parseJSON(response);
                        switch (response['status']) {
                            case "true":
                                $("#submit-form").removeAttr("disabled");

                                $("#successLogin").fadeIn("slow");
                                setTimeout(function () {
                                    $("#successLogin").hide();
                                    $("form").submit();
                                }, 5000);
                                break;

                            case "false":
                                $("#wrongLoginData").html(response['errorMessage']);
                                $("#wrongLoginData").fadeIn("slow");

                                setTimeout(function () {
                                    $("#wrongLoginData").hide();
                                }, 15000);
                                self.text("<?php _e( 'بررسی اتصال', 'Logito' ); ?>");
                                self.removeAttr("disabled");
                                break;
                        }
                    });
                });
            });
        </script> <?php
	}

	/**
	 * Server side authentication that receives Javascript Ajax requests
	 */
	function LogitoAuthentication() {
		$savedAuthenticationData = self::getLogitoAuthenticationData();

		if ( $_POST['password'] == '######' && ( isset( $savedAuthenticationData['LogitoUsername'] ) || isset( $savedAuthenticationData['LogitoUsername2'] ) ) ) {
			// Array of account data
			if ( isset( $_POST['account'] ) ) {
				$data = array(
					'credential' => array(
						"Username" => $savedAuthenticationData['LogitoUsername2'],
						"Password" => $savedAuthenticationData['LogitoPassword2']
					)
				);
			} else {
				$data = array(
					'credential' => array(
						"Username" => $savedAuthenticationData['LogitoUsername'],
						"Password" => $savedAuthenticationData['LogitoPassword']
					)
				);
			}
		} else {
			// Array of account data
			$data = array(
				'credential' => array(
					"Username" => trim( $_POST['username'] ),
					"Password" => trim( $_POST['password'] )
				)
			);
		}

		$authenticating = new GetStates( $data );

		// Error message
		$errorMessage = $authenticating->getResponseMessage();

		if ( $authenticating->getResponseCode() == "0" ) {
			$status = "true";
		} else {
			$status = "false";
		}


		// array of response
		$result = array( 'status' => $status, 'errorMessage' => $errorMessage );
		Debug::Log( $result, 'result' );

		// Print out response array
		echo json_encode( $result );

		wp_die(); // this is required to terminate immediately and return a proper response*/
	}

	public static function getLogitoAuthenticationData() {
		$optionName = self::$optionName;

		$authenticationData = maybe_unserialize( get_option( $optionName ) );

		return $authenticationData;
	}
}