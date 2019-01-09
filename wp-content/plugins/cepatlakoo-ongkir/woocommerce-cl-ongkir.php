<?php
/**
 * Plugin Name: Cepatlakoo Ongkir
 * Plugin URI: https://cepatlakoo.com
 * Description: Plugin perhitungan ongkos kirim menggunakan API Raja Ongkir. Dibuat berdasarkan plugin MyOngkir dengan berbagai perbaikan.
 * Version: 1.4.2
 * Original Author: eezhal
 * Author: CepatLakoo
 * Author URI: hhttps://cepatlakoo.com
 * Requires at least: 3.x
 * @package cl-ongkir
 * @category Core
 * @author eezhal
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'cl_ongkir_setting_check' ) ) {
	function cl_ongkir_setting_check() {
		?>
		<div class="error notice">
			<p><?php _e("Seting plugin Cepatlakoo Ongkir masih belum lengkap. Harap dilengkapi terlebih dahulu.", 'cl-ongkir'); ?></p>
			<a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping&section=cl_ongkir_shipping') ?>" class="button-primary"><?php _e('Seting Sekarang', 'cl-ongkir') ?></a>
		</div>
		<?php
	}
}

if ( ! function_exists( 'cl_ongkir_myongkir_notice' ) ) {
	function cl_ongkir_myongkir_notice() {
		?>
		<div class="error notice">
			<p><?php _e("Segera non-aktifkan plugin Cepatlakoo MyOngkir, agar tidak terjadi konflik dengan plugin Cepatlakoo Ongkir", 'cl-ongkir'); ?></p>	
		</div>
		<?php
	}
}

if ( ! function_exists( 'cl_ongkir_error_notice' ) ) {
	function cl_ongkir_error_notice() {
		?>
		<div class="error notice">
			<p><?php _e('Indo Ongkir plugin is active, please deactivate it to prevent any conflict with Cepatlakoo Ongkir', 'cl-ongkir' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'cl_ongkir_plugin_init' ) ) {
	function cl_ongkir_plugin_init() {
		if( class_exists( 'WPBisnis_WC_Indo_Ongkir_Init' ) ) {
			add_action( 'admin_notices', 'cl_ongkir_error_notice' );
		}
		load_plugin_textdomain( 'cl-ongkir', FALSE, basename( dirname( __FILE__ ) ) . '/lang' );
	}
}
add_action( 'plugins_loaded', 'cl_ongkir_plugin_init' );

if ( ! function_exists( 'cl_ongkir_myongkir_check' ) ) {
	function cl_ongkir_myongkir_check() {
		if( is_plugin_active( 'cepatlakoo-myongkir/woocommerce-myongkir.php' ) ) {
			add_action( 'admin_notices', 'cl_ongkir_myongkir_notice' );
		}
	}
}
add_action( 'admin_init', 'cl_ongkir_myongkir_check' );


if ( ! function_exists( 'cl_ongkir_tracking' ) ) {
	function cl_ongkir_tracking() {
		global $cl_ongkir;

		if( !empty($_POST) && isset($_POST['action']) && $_POST['action'] == 'cl_do_tracking' ){
			$track = $cl_ongkir->cl_ongkir_tracking( $_POST['courier'], $_POST['code'] );
			
			ob_start();
			if($track != 'error') : ?>
			<?php require plugin_dir_path( __FILE__ ) . 'include/form-tracking.php'; ?>
				<div class="container" id="shipping-track">
					<h3><?php _e('Tracking Result', 'cl-ongkir'); ?></h3>
					<div class="row">
						<div class="col">
							<h4><?php _e('Shipping Details', 'cl-ongkir'); ?></h4>
							<table>
								<tr>
									<td><?php _e('Courier', 'cl-ongkir'); ?></td>
									<td><?php echo $track['summary']['courier_name'].' '.$track['summary']['service_code'] ?></td>
								</tr>
								<tr>
									<td><?php _e('Resi', 'cl-ongkir'); ?></td>
									<td><?php echo $track['summary']['waybill_number'] ?></td>
								</tr>
								<tr>
									<td><?php _e('Status', 'cl-ongkir'); ?></td>
									<td><?php echo $track['summary']['status'] ?></td>
								</tr>
							</table>
						</div>
						<div class="col">
							<h4><?php _e('Customer Details', 'cl-ongkir'); ?></h4>
							<table>
								<tr>
									<td><?php _e('Nama', 'cl-ongkir'); ?></td>
									<td><?php echo $track['details']['receiver_name']; ?></td>
								</tr>
								<tr>
									<td><?php _e('Alamat', 'cl-ongkir'); ?></td>
									<td><?php echo $track['details']['receiver_address1'].' '.$track['details']['receiver_address2'].' '.$track['details']['receiver_address3']; ?></td>
								</tr>
								<tr>
									<td><?php _e('Kota', 'cl-ongkir'); ?></td>
									<td><?php echo $track['details']['receiver_city'] ?></td>
								</tr>
							</table>
						</div>
					</div>
					<?php if(is_array($track['manifest']) && count($track['manifest']) > 0 ) : ?>
					<div class="row">
						<div class="col">
							<h4><?php _e('Shipping Progress', 'cl-ongkir'); ?></h4>
							<table>
								<thead>
									<tr>
										<td><?php _e('Tanggal', 'cl-ongkir'); ?></td>
										<td><?php _e('Keterangan', 'cl-ongkir'); ?></td>
										<td><?php _e('Kota', 'cl-ongkir'); ?></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach($track['manifest'] as $prog) : ?>
									<tr>
										<td><?php echo $prog['manifest_date'].' '.$prog['manifest_time'] ?></td>
										<td><?php echo $prog['manifest_description'] ?></td>
										<td><?php echo $prog['city_name'] ?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php endif; ?>
				</div>
			<?php else : ?>	
				<div class="container">
					<p class="warning"><?php _e('Please Check Your Tracking Code or Courier', 'cl-ongkir'); ?></p>
				</div>
			<?php require plugin_dir_path( __FILE__ ) . 'include/form-tracking.php'; ?>
			<?php endif;
			ob_end_flush();
		}
		else{
			require plugin_dir_path( __FILE__ ) . 'include/form-tracking.php';
		}
	}
}
add_shortcode('cepatlakoo-ongkir-tracking', 'cl_ongkir_tracking');

if ( ! function_exists( 'cl_get_checkout_post_data' ) ) {
	function cl_get_checkout_post_data( $itemdata ) {
		$post_data = isset($_POST['post_data']) ? $_POST['post_data'] : '';
		$postdata = explode('&',$post_data);
		$post_data_ret = '';

		foreach ($postdata as $value) {
			if (strpos($value,$itemdata) !== FALSE) {
					$post_data_ret = $value;
					$ar = explode('=',$post_data_ret);
					$post_data_ret = $ar[1];
					break;
			}
		}

		$post_data_ret = str_replace('+',' ',$post_data_ret);
		
		if( $post_data_ret == '' && isset($_POST[$itemdata]) ){
			$post_data_ret = $_POST[$itemdata];
		}
		
		return $post_data_ret;
	}
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	register_activation_hook( __FILE__, 'cl_check_base_country' );
	register_activation_hook( __FILE__, 'cl_check_currency' );

	add_action( 'init', 'cl_global_ongkir_init' ); //  try to change scope to more global
	add_action( 'init', 'cl_ongkir_shipping_method_init' );
	add_action( 'woocommerce_shipping_init', 'cl_ongkir_shipping_method_init' );
	add_filter( 'woocommerce_shipping_methods', 'cl_add_ongkir_shipping_method' );
	add_filter( 'woocommerce_general_settings', 'cl_add_api_key_setting' );
	add_filter( 'woocommerce_products_general_settings', 'cl_add_minimum_weight_setting' );

	add_filter( 'woocommerce_checkout_fields', 'cl_unpro_order_fields');
	add_filter( 'woocommerce_get_country_locale', 'cl_unpro_country_locale' );
	add_filter( 'woocommerce_billing_fields', 'cl_custom_woocommerce_billing_fields' );
	add_filter( 'woocommerce_shipping_fields', 'cl_custom_woocommerce_shipping_fields' );

	add_action( 'woocommerce_checkout_update_order_meta', 'cl_woocommerce_ongkir_checkout_field_cities_update_order_meta' ); //STARTER
	add_filter( 'woocommerce_my_account_my_address_formatted_address', 'cl_woocommerce_ongkir_my_account_my_address_formatted_address', 10, 2 );
	add_action( 'woocommerce_cart_totals_before_order_total', 'cl_add_weight_to_cart' );
	add_action( 'woocommerce_review_order_before_order_total', 'cl_add_weight_to_cart' );

	add_action( 'wp_footer', 'cl_get_cities_js' );
	add_action( 'wp_ajax_get_cities', 'cl_get_cities_cb' );
	add_action( 'wp_ajax_nopriv_get_cities', 'cl_get_cities_cb' );
	add_filter( 'woocommerce_cart_shipping_packages', 'cl_filter_woocommerce_cart_shipping_packages', 10, 1 );
	add_action('wp_head', 'cl_css_hide_country');
	add_filter( 'woocommerce_form_field_hidden', 'cl_ongkir_hidden_field_type', 5, 4 );
		add_filter( 'woocommerce_locate_template', 'cl_locate_template', 10, 3 );

	function cl_ongkir_hidden_field_type( $field, $key, $args, $value ) {
		return '<input type="hidden" name="'.esc_attr($key).'" id="'.esc_attr($args['id']).'" value="'.esc_attr($args['default']).'" />';
	}

	// define the woocommerce_cart_shipping_packages callback 
	function cl_filter_woocommerce_cart_shipping_packages( $array ) {
		$new_arr = array();
		
		foreach ( $array as $arr ) {
			if( get_option( 'woocommerce_type_account') == 'pro' ) {
				$arr['destination']['cl_ongkir_type'] = 'subdistrict';

				if ( cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$arr['destination']['cl_ongkir_code']	= cl_get_checkout_post_data( 'shipping_subdistrict' );
				} else {
					$arr['destination']['cl_ongkir_code']	= cl_get_checkout_post_data( 'billing_subdistrict' );
				}
			} else {
				$arr['destination']['cl_ongkir_type'] = 'city';

				if ( cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$arr['destination']['cl_ongkir_code']	= cl_get_checkout_post_data( 'shipping_city' );
				} else {
					$arr['destination']['cl_ongkir_code']	= cl_get_checkout_post_data( 'billing_city' );
				}
			}
			$new_arr[] = $arr;
		}
		return $new_arr;
	};

	function cl_ongkir_admin_js() {
		wp_enqueue_script( 'myongkir-admin', plugins_url( 'assets/js/cl-ongkir-admin.js', __FILE__ ), array('jquery') );
	}
	add_action( 'admin_enqueue_scripts','cl_ongkir_admin_js' );

	function cl_ongkir_queue_script() {
		wp_enqueue_style( 'cl-ongkir-css', plugin_dir_url( __FILE__ ) . 'assets/css/cl-ongkir.css' );
	}
	add_action( 'wp_enqueue_scripts','cl_ongkir_queue_script' );

	

	// Passed Was Check;
	if ( ! function_exists( 'weight_notice' ) ) {
		function weight_notice() {
		?>
			<div class="updated">
				<p><?php _e( 'Berat tidak boleh kosong.', 'cl-ongkir' ); ?></p>
			</div>
		<?php
		}
	}
	update_option( 'default_comment_status', 'closed' );

	// Passed Was Check;
	if ( ! function_exists( 'cl_check_base_country' ) ) {
		function cl_check_base_country() {
			global $woocommerce;

			if ( $woocommerce->countries->get_base_country() !== 'ID' ) {
				$exit_msg = esc_html__('Plugin Cepatlakoo Ongkir Shipping only based location must be Indonesia.', 'cl-ongkir');
				exit( $exit_msg );
			}
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_check_currency' ) ) {
		function cl_check_currency() {
			$current_currency = get_woocommerce_currency();

			if( $current_currency != 'IDR' ) {
				$exit_msg = esc_html__('Plugin Cepatlakoo Ongkir Shipping must use IDR currency.', 'cl-ongkir');
				exit( $exit_msg );
			}
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_global_ongkir_init' ) ) {
		function cl_global_ongkir_init() {
			require_once( plugin_dir_path( __FILE__ ) . 'include/cl-ongkir-shipping.php' );

			// define nonce constans
			define("CL_ONGKIR_NONCE", "ongkir-nonce");
			$ongkir_shipping = Cl_Ongkir_Shipping::get_instance();
			$rajaongkir_api_key = get_option( 'woocommerce_rajaongkir_api_key' );
			$ongkir_shipping->set_api_key( $rajaongkir_api_key );
			$GLOBALS['cl_ongkir'] = $ongkir_shipping;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_ongkir_shipping_method_init' ) ) {
		function cl_ongkir_shipping_method_init() {
			if ( ! class_exists( 'Cl_Ongkir_Shipping_Method' ) ) {
				require "cl-ongkir-shipping-method.php";
			}

			$cl_ongkir_shipping = new Cl_Ongkir_Shipping_Method;
			$newapi = $cl_ongkir_shipping->get_option('rajaongkir_apikey');
			$newtype = $cl_ongkir_shipping->get_option('type_ongkir');
			wp_cache_delete ( 'woocommerce_rajaongkir_api_key', 'options' );
			wp_cache_delete ( 'woocommerce_type_account', 'options' );
			update_option( 'woocommerce_type_account', $newtype );
			update_option( 'woocommerce_rajaongkir_api_key', $newapi );
			
			if ( isset($cl_ongkir_shipping->settings['enabled']) && $cl_ongkir_shipping->settings['enabled'] == 'yes' &&
			( empty( $cl_ongkir_shipping->settings['base_city'] ) || $cl_ongkir_shipping->settings['base_city'] == 0 || empty( $cl_ongkir_shipping->settings['rajaongkir_apikey'] ) )
			) {
				add_action( 'admin_notices', 'cl_ongkir_setting_check' );
			}
			
			if ( ! wp_next_scheduled ( 'cl_ongkir_delete_transient' ) ) {
				// $run = strtotime('11:55 PM');
				$run = strtotime( '04:55 PM' );
				wp_schedule_event( $run, 'daily', 'cl_ongkir_delete_transient' );
			}
		}
	}
	add_action( 'cl_ongkir_delete_transient', 'cl_ongkir_del_transient' );

	// function for delete expired transient
	if ( ! function_exists( 'cl_ongkir_del_transient' ) ) {
		function cl_ongkir_del_transient() {
			global $wpdb, $_wp_using_ext_object_cache;

			if ( $_wp_using_ext_object_cache )
				return;

			$time = time() ;
			$expired = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout%' AND option_value < {$time};" );

			foreach ( $expired as $transient ) {
				$key = str_replace('_transient_timeout_', '', $transient);
				delete_transient($key);
			}
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_add_ongkir_shipping_method' ) ) {
		function cl_add_ongkir_shipping_method( $methods ) {
			$methods[] = 'Cl_Ongkir_Shipping_Method';
			return $methods;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_add_api_key_setting' ) ) {
		function cl_add_api_key_setting( $settings ) {
		$updated_settings = array();

		foreach ( $settings as $section ) {
			// at the bottom of the General Options section
			if ( isset( $section['id'] ) && 'general_options' == $section['id'] &&
			isset( $section['type'] ) && 'sectionend' == $section['type'] ) {

			$updated_settings[] = array(
				'id'       => 'woocommerce_rajaongkir_api_key',
				'css'	   => 'width: 300px; display:none; margin-bottom:10px',
				'type'     => 'text',
			);
			$updated_settings[] = array(
				'id'       => 'woocommerce_type_account',
				'css'	   => 'width: 300px; display:none; margin-bottom:10px',
				'type'     => 'text',
			);
			}

			$updated_settings[] = $section;
		}

		return $updated_settings;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_add_minimum_weight_setting' ) ) {
		function cl_add_minimum_weight_setting( $settings ) {
			$updated_settings = array();

			foreach ( $settings as $section ) {
				if ( isset( $section['id'] ) &&
					'product_measurement_options' == $section['id'] &&
					isset( $section['type'] ) &&
					'sectionend' == $section['type'] ) {

					$updated_settings[] = array(
						'name'     => esc_html__( 'Berat Minimal', 'cl-ongkir' ),
						'id'       => 'woocommerce_cl_ongkir_minimum_weight',
						'css'	   => 'width: 100px;',
						'type'     => 'text',
						'desc'     => esc_html__( 'Default cart weight if cart doesn\'t meet, based on weight unit.', 'cl-ongkir' ),
						'default'  => 1
					);
				}

				$updated_settings[] = $section;
			}

			return $updated_settings;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_custom_woocommerce_billing_fields' ) ) {
		function cl_custom_woocommerce_billing_fields( $fields ) {
			$options = get_option( 'woocommerce_cl_ongkir_shipping_settings' );
			
			$fields['billing_city']					= create_city_field( 'billing' );
			$fields['cl_bill_city_name']			= array(
				'type' 			=> 'hidden',
				'priority'		=> 150
			);
			
			$fields['billing_phone']['class']		= array( 'form-row-first' );
			$fields['billing_email']['class']		= array( 'form-row-last' );
			if ( get_option( 'woocommerce_type_account') != 'pro' ) {
				$fields['billing_state']['class']	= array( 'form-row-first' );
			}

			if ( isset( $options ) && $options['enabled'] == 'yes' ) {
				if( isset( $options['cl_last_name'] ) && $options['cl_last_name'] == 'yes' ) {
					$fields['billing_first_name']['label']	= __( 'Nama Lengkap', 'cl-ongkir' );
					$fields['billing_first_name']['class']	= array( 'form-row-wide' );
					unset( $fields['billing_last_name'] );
				}
				
				if ( isset( $options['cl_company'] ) && $options['cl_company'] == 'yes' ) {
					unset( $fields['billing_company'] );
				}
				
				if ( isset( $options['cl_address1'] ) && $options['cl_address1'] == 'yes' ) {
					$fields['billing_address_1']['type'] = 'textarea';
				}
				
				if ( isset($options['cl_address2'] ) && $options['cl_address2'] == 'yes' ) {
					unset( $fields['billing_address_2'] );
				}
				
				if( isset( $options['cl_email_option'] ) && $options['cl_email_option'] == 'yes' ) {
					$fields['billing_email']['required'] = false;
				}

				if ( isset( $options['cl_notes'] ) && $options['cl_notes'] == 'yes' ) {
					add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
				}
			}
			
			return $fields;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_custom_woocommerce_shipping_fields' ) ) {
		function cl_custom_woocommerce_shipping_fields( $fields ) {
			$options = get_option( 'woocommerce_cl_ongkir_shipping_settings' );

			$fields['shipping_city'] = create_city_field( 'shipping' );
			$fields['cl_ship_city_name'] = array(
				'type' 			=> 'hidden',
				'priority'		=> 150
			);

			if ( get_option( 'woocommerce_type_account') != 'pro' ) {
				$fields['shipping_state']['class'] = array( 'form-row-first' );
			}

			if ( isset( $options ) && $options['enabled'] == 'yes' ) {
				if ( isset( $options['cl_last_name'] ) && $options['cl_last_name'] == 'yes' ) {
					$fields['shipping_first_name']['label']	= esc_html__( 'Nama Lengkap', 'cl-ongkir' );
					$fields['shipping_first_name']['class']	= array( 'form-row-wide' );
					unset( $fields['shipping_last_name'] );
				}

				if ( isset( $options['cl_company'] ) && $options['cl_company'] == 'yes' ) {
					unset( $fields['shipping_company'] );
				}
				
				if ( isset( $options['cl_address1'] ) && $options['cl_address1'] == 'yes' ) {
					$fields['shipping_address_1']['type'] = 'textarea';
				}

				if ( isset( $options['cl_address2'] ) && $options['cl_address2'] == 'yes' ) {
					unset( $fields['shipping_address_2'] );
				}
			}
			return $fields;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_css_hide_country' ) ) {
		function cl_css_hide_country() {
			$options = get_option( 'woocommerce_cl_ongkir_shipping_settings' );
			if ( isset( $options['cl_country'] ) && $options['cl_country'] == 'yes' ) : ?>
				<style>
					#billing_country_field, #shipping_country_field{
						display:none !important;
					}
				</style>
			<?php endif;
		}
	}
	
	if ( ! function_exists( 'cl_custom_woocommerce_billing_subdistrict_fields' ) ) {
		function cl_custom_woocommerce_billing_subdistrict_fields( $fields ) {
			$fields['billing_subdistrict'] = create_subdistrict_field( 'billing' );
			$fields['cl_bill_subdistrict_name'] = array(
				'type' => 'hidden',
				'priority'		=> 160
			);

			$fields["billing_first_name"]["priority"] = 10;
			$fields["billing_phone"]["priority"] = 30;
			$fields["billing_email"]["priority"] = 40;
			$fields["billing_address_1"]["priority"] = 60;
			$fields["billing_country"]["priority"] = 70;
			$fields["billing_state"]["priority"] = 90;
			$fields["billing_city"]["priority"] = 100;
			$fields["billing_subdistrict"]["priority"] = 110;
			$fields["cl_bill_subdistrict_name"]["priority"] = 120;
			$fields["billing_postcode"]["priority"] = 140;

			return $fields;
		}
	}

	// Passed Was Check;
	if ( ! function_exists( 'cl_custom_woocommerce_shipping_subdistrict_fields' ) ) {
		function cl_custom_woocommerce_shipping_subdistrict_fields( $fields ) {
			$fields['shipping_subdistrict'] = create_subdistrict_field( 'shipping' );
			$fields['cl_ship_subdistrict_name'] = array(
				'type' => 'hidden',
				'priority'		=> 160
			);

			$fields['shipping_first_name']['priority'] = 10;
			$fields['shipping_address_1']['priority'] = 60;
			$fields['shipping_country']['priority'] = 70;
			$fields['shipping_state']['priority'] = 90;
			$fields['shipping_city']['priority'] = 100;
			$fields['shipping_subdistrict']['priority'] = 110;
			$fields['shipping_postcode']['priority'] = 140;

			return $fields;
		}
	}

	// Passed Was Check; For Update Post Meta if Updated Data
	if ( ! function_exists( 'cl_woocommerce_ongkir_checkout_field_cities_update_order_meta' ) ) {
		function cl_woocommerce_ongkir_checkout_field_cities_update_order_meta( $order_id ) {
			global $cl_ongkir;

			if ( $_POST['billing_city'] ) {
				$city_name = $_POST['cl_bill_city_name'];
				update_post_meta( $order_id, '_billing_city', esc_attr( $city_name ) ); // works

				// check if ship to different address is checked
				if ( !empty( $_POST['shipping_city'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$city_name = $_POST['cl_ship_city_name'];
				} else {
					$city_name = $_POST['cl_bill_city_name'];
				}
				update_post_meta( $order_id, '_shipping_city', esc_attr( $city_name ) );
			}

			if ( isset($_POST['shipping_city'] ) ) { // works
				// check if ship to different address is checked
				if( !empty( $_POST['shipping_city'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$city_name = $_POST['cl_ship_city_name'];
				} else {
					$city_name = $_POST['cl_bill_city_name'];
				}
				update_post_meta( $order_id, '_shipping_city', esc_attr( $city_name ) );
			}
		}
	}

	// Passed Was Check; For Update Post Meta if Updated Data
	if ( ! function_exists( 'cl_woocommerce_ongkir_checkout_field_subdistrict_update_order_meta' ) ) {
		function cl_woocommerce_ongkir_checkout_field_subdistrict_update_order_meta( $order_id ) {
			global $cl_ongkir;

			if ( $_POST['billing_city'] ) {
				$city_name = $_POST['cl_bill_subdistrict_name'].', '.$_POST['cl_bill_city_name'];
				update_post_meta( $order_id, '_billing_city', esc_attr( $city_name )); // works

				if ( !empty( $_POST['shipping_city'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$city_name = $_POST['cl_ship_subdistrict_name'].', '.$_POST['cl_ship_city_name'];
				} else {
					$city_name = $_POST['cl_bill_subdistrict_name'].', '.$_POST['cl_bill_city_name'];
				}
				update_post_meta( $order_id, '_shipping_city', esc_attr( $city_name ) );
			}

			if ( isset( $_POST['shipping_city'] ) ) { // works
				// check if ship to different address is checked
				if ( !empty( $_POST['shipping_city'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$city_name = $_POST['cl_ship_subdistrict_name'].', '.$_POST['cl_ship_city_name'];
				} else {
					$city_name = $_POST['cl_bill_subdistrict_name'].', '.$_POST['cl_bill_city_name'];
				}
				update_post_meta( $order_id, '_shipping_city', esc_attr( $city_name ) );
			}

			if ( $_POST['billing_subdistrict'] ) {
				$subdistrict_name = $_POST['cl_bill_subdistrict_name'];

				// check if ship to different address is checked
				if ( !empty( $_POST['shipping_subdistrict'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$subdistrict_name = $_POST['cl_ship_subdistrict_name'];
				} else {
					$subdistrict_name = $_POST['cl_bill_subdistrict_name'];
				}
				update_post_meta( $order_id, '_billing_subdistrict', esc_attr( $subdistrict_name ) ); // works
			}

			if ( isset( $_POST['shipping_subdistrict'] ) ) { // works
				$subdistrict_name = $_POST['cl_ship_subdistrict_name'];

				if ( !empty( $_POST['shipping_subdistrict'] ) && cl_get_checkout_post_data( 'ship_to_different_address' ) == 1 ) {
					$subdistrict_name = $_POST['cl_ship_subdistrict_name'];
				} else {
					$subdistrict_name = $_POST['cl_bill_subdistrict_name'];
				}
				update_post_meta( $order_id, '_shipping_subdistrict', esc_attr( $subdistrict_name ) );
			}
		}
	}

	if ( ! function_exists( 'create_city_field' ) ) {
		function create_city_field( $type ) {
			global $current_user;
			$index_city = 0;

			if ( is_user_logged_in() ) {
				$user_id = $current_user->data->ID;
				$meta_key = ( $type == 'billing' ) ? 'billing_city' :  'shipping_city' ;
				$index_city = get_user_meta( $user_id, $meta_key, true );
			}

			if ( get_option( 'woocommerce_type_account') == 'pro' ) {
				$field = array(
					'type' 			=> 'select',
					'label' 		=> esc_html__( 'Kota/Kabupaten', 'cl-ongkir' ),
					'placeholder' 	=> esc_html__( 'Pilih Kota/Kabupaten', 'cl-ongkir' ),
					'required' 		=> true,
					'class' 		=> array('form-row-first'),
					'custom_attributes'	=> array('data-selected' => $index_city ),
					'clear' 		=> false,
					'options'		=> array(
						'' => esc_html__( 'Pilih Kota/Kabupaten', 'cl-ongkir' )
					)
				);
			} else {
				$field = array(
					'type' 			=> 'select',
					'label' 		=> esc_html__( 'Kota/Kabupaten', 'cl-ongkir' ),
					'placeholder' 	=> esc_html__( 'Pilih Kota/Kabupaten', 'cl-ongkir' ),
					'required' 		=> true,
					'class' 		=> array('form-row-last', 'update_totals_on_change'),
					'custom_attributes'	=> array('data-selected' => $index_city ),
					'clear' 		=> false,
					'options'		=> array(
						'' => esc_html__( 'Pilih Kota/Kabupaten', 'cl-ongkir' )
					)
				);
			}

			return $field;
		}
	}

	if ( ! function_exists( 'create_subdistrict_field' ) ) {
		function create_subdistrict_field( $type ) {
			global $current_user;
			$index_city = 0;
			
			if( is_user_logged_in() ) {
				$user_id = $current_user->data->ID;
				$meta_key = ( $type == 'billing' ) ? 'billing_subdistrict' :  'shipping_subdistrict' ;
				$index_city =  get_user_meta( $user_id, $meta_key, true );
			}

			$field = array(
				'type' 			=> 'select',
				'label' 		=> esc_html__( 'Kecamatan', 'cl-ongkir' ),
				'placeholder' 	=> esc_html__( 'Pilih Kecamatan', 'cl-ongkir' ),
				'required' 		=> true,
				'class' 		=> array('form-row-last', 'update_totals_on_change'),
				'clear' 		=> false,
				'custom_attributes'	=> array('data-selected' => $index_city ),
				'options'		=> array(
					'' => esc_html__( 'Pilih Kecamatan', 'cl-ongkir' ),
				)
			);

			return $field;
		}
	}

	if ( ! function_exists( 'cl_woocommerce_ongkir_my_account_my_address_formatted_address' ) ) {
		function cl_woocommerce_ongkir_my_account_my_address_formatted_address( $data, $customer_id ) {
			global $cl_ongkir;

			$province_id = $cl_ongkir->convert_to_province_id( $data['state'] );

			$data['city'] = $cl_ongkir->get_city($data['city'], $province_id);
			return $data;
		}
	}

	if ( ! function_exists( 'cl_get_cities_js' ) ) {
		function cl_get_cities_js( $hook ) {
			global $post;

			if($post){
				if ( has_shortcode( $post->post_content, 'woocommerce_my_account' ) || has_shortcode( $post->post_content, 'woocommerce_checkout' ) || has_shortcode( $post->post_content, 'woocommerce_cart' ) ) {
					if ( is_user_logged_in() ) {
						global $current_user;
						$user_id = $current_user->data->ID;

						$_SESSION['billing_city'] = get_user_meta( $user_id, 'billing_city', true );
						$_SESSION['shipping_city'] = get_user_meta( $user_id, 'shipping_city', true );
					}

					$is_permitted_pages = ( $post->post_content == '[woocommerce_my_account]' ||
											$post->post_content == '[woocommerce_checkout]' || $post->post_content == '[woocommerce_cart]');

					$settings = array(
						'ajax_url'  		 => admin_url( 'admin-ajax.php' ),
						'error_msg'  		 => esc_html__('Server error, Cant connect to Ongkir API', 'cl-ongkir'),
						'type'  		 	 => !empty(get_option( 'woocommerce_type_account')) ? get_option( 'woocommerce_type_account') : '',
						'nonce' 			 => wp_create_nonce( constant('CL_ONGKIR_NONCE') ),
						'is_permitted_pages' => $is_permitted_pages,
						'billing_city' 		 => isset( $_SESSION['billing_city'] ) ? $_SESSION['billing_city'] : null,
						'shipping_city' 	 => isset( $_SESSION['shipping_city'] ) ? $_SESSION['shipping_city'] : null
					);

					wp_enqueue_script( 'ajax-script', plugins_url( 'assets/js/cl-ongkir.js', __FILE__ ), array('jquery') );
					wp_localize_script( 'ajax-script', 'cl_ongkirAjax', $settings );
				}
			}
		}
	}
	
	if ( ! function_exists( 'cl_locate_template' ) ) {
		function cl_locate_template( $template, $template_name, $template_path ) {
			global $woocommerce;

			$_template = $template;

			if ( ! $template_path ) $template_path = $woocommerce->template_url;

			$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';

			// Look within passed path within the theme - this is priority
			$template = locate_template(	  
				array(
					$template_path . $template_name,
					$template_name
				)
			);

			// Modification: Get the template from this plugin, if it exists
			if ( ! $template && file_exists( $plugin_path . $template_name ) )
			$template = $plugin_path . $template_name;

			// Use default template
			if ( ! $template )
				$template = $_template;

			// Return what we found
			return $template;
		}
	}

	if ( ! function_exists( 'cl_get_cities_cb' ) ) {
		function cl_get_cities_cb() {
			$nonce = $_GET['nonce'];

			// if don't have nonce, set error
			if ( !wp_verify_nonce( $nonce, constant('CL_ONGKIR_NONCE' ) ) ) die( $nonce );

			if( get_option( 'woocommerce_rajaongkir_api_key' ) == null || get_option( 'woocommerce_rajaongkir_api_key' ) == '' ) {
				echo json_encode( array(' Please set RajaOngkir API Key First' ) );

				die();
			} else {
				$state_id = $_GET['state'];
				global $cl_ongkir;
				
				$cities = $cl_ongkir->get_cities( $state_id );

				echo json_encode( $cities );
				die(); // this is required to return a proper result
			}
		}
	}

	// OK SUBDISTRICT
	if ( ! function_exists( 'cl_get_subdistrict_js' ) ) {
		function cl_get_subdistrict_js( $hook ) {
			global $post;

			if ( $post ) {
				if ( $post->post_content == '[woocommerce_my_account]' || $post->post_content == '[woocommerce_checkout]' ) {
					if ( is_user_logged_in() ) {
						global $current_user;

						$user_id = $current_user->data->ID;
						$_SESSION['billing_subdistrict'] = get_user_meta( $user_id, 'billing_subdistrict', true );
						$_SESSION['shipping_subdistrict'] = get_user_meta( $user_id, 'shipping_subdistrict', true );
					}

					$is_permitted_pages = ( $post->post_content == '[woocommerce_my_account]' ||
											$post->post_content == '[woocommerce_checkout]' );

					$settings = array(
						'ajax_url'  		 => admin_url( 'admin-ajax.php' ),
						'error_msg'  		 => esc_html__('Server error, Cant connect to Ongkir API', 'cl-ongkir'),
						'type'  		 	 => !empty(get_option( 'woocommerce_type_account')) ? get_option( 'woocommerce_type_account') : '',
						'nonce' 			 => wp_create_nonce( constant('CL_ONGKIR_NONCE') ),
						'is_permitted_pages' => $is_permitted_pages,
						'billing_subdistrict'   => isset( $_SESSION['billing_subdistrict'] ) ? $_SESSION['billing_subdistrict'] : null,
						'shipping_subdistrict' 	 => isset( $_SESSION['shipping_subdistrict'] ) ? $_SESSION['shipping_subdistrict'] : null
					);

					wp_enqueue_script( 'ajax-script', plugins_url( 'assets/js/cl-ongkir.js', __FILE__ ), array('jquery') );
					wp_localize_script( 'ajax-script', 'cl_ongkirAjax', $settings );
				}
			}
		}
	}

	// OK SUBDISTRICT Callback
	if ( ! function_exists( 'cl_get_subdistrict_cb' ) ) {
		function cl_get_subdistrict_cb() {
			global $cl_ongkir;
			
			$nonce = $_GET['nonce'];

			// if don't have nonce, set error
			if ( !wp_verify_nonce( $nonce, constant('CL_ONGKIR_NONCE' ) ) ) die( $nonce );

			if ( get_option( 'woocommerce_rajaongkir_api_key' ) == null || get_option( 'woocommerce_rajaongkir_api_key' ) == '' ) {
				echo json_encode( array(' Please set RajaOngkir API Key First' ) );

				die();
			} else if ( !empty( $_GET['sdistrict'] ) ) {
				
				return true;
				die();
			} else {
				$state_id = $_GET['city'];
				
				$cities = $cl_ongkir->get_sub( $state_id );
				echo json_encode( $cities );
				die(); // this is required to return a proper result
			}
		}
	}

	// Order Fields
	if ( ! function_exists( 'cl_order_fields' ) ) {
		function cl_order_fields( $fields ) {
			$fields["billing"]["billing_first_name"]["priority"] = 10;
			$fields["billing"]["billing_phone"]["priority"] = 30;
			$fields["billing"]["billing_email"]["priority"] = 40;
			$fields["billing"]["billing_address_1"]["priority"] = 60;
			$fields["billing"]["billing_country"]["priority"] = 70;
			$fields["billing"]["billing_state"]["priority"] = 90;
			$fields["billing"]["billing_city"]["priority"] = 100;
			$fields["billing"]["billing_subdistrict"]["priority"] = 110;
			$fields["billing"]["cl_bill_subdistrict_name"]["priority"] = 120;
			$fields["billing"]["billing_postcode"]["priority"] = 140;
			// unset($fields['billing']['billing_company']);
			// unset($fields['billing']['billing_address_2']);

			$fields["shipping"]["shipping_first_name"]["priority"] = 10;
			$fields["shipping"]["shipping_address_1"]["priority"] = 60;
			$fields["shipping"]["shipping_country"]["priority"] = 70;
			$fields["shipping"]["shipping_state"]["priority"] = 90;
			$fields["shipping"]["shipping_city"]["priority"] = 100;
			$fields["shipping"]["shipping_subdistrict"]["priority"] = 110;
			$fields["shipping"]["shipping_postcode"]["priority"] = 140;
			// unset($fields['shipping']['shipping_company']);
			// unset($fields['shipping']['shipping_address_2']);

			return $fields;
		}
	}

	if ( ! function_exists( 'cl_unpro_order_fields' ) ) {
		function cl_unpro_order_fields( $fields ) {
			$fields["billing"]["billing_first_name"]["priority"] = 10;
			$fields["billing"]["billing_phone"]["priority"] = 30;
			$fields["billing"]["billing_email"]["priority"] = 40;
			$fields["billing"]["billing_address_1"]["priority"] = 60;
			$fields["billing"]["billing_country"]["priority"] = 70;
			$fields["billing"]["billing_state"]["priority"] = 90;
			$fields["billing"]["billing_city"]["priority"] = 100;
			$fields["billing"]["billing_postcode"]["priority"] = 140;
			// unset($fields['billing']['billing_company']);
			// unset($fields['billing']['billing_address_2']);

			$fields["shipping"]["shipping_first_name"]["priority"] = 10;
			$fields["shipping"]["shipping_address_1"]["priority"] = 60;
			$fields["shipping"]["shipping_country"]["priority"] = 70;
			$fields["shipping"]["shipping_state"]["priority"] = 90;
			$fields["shipping"]["shipping_city"]["priority"] = 100;
			$fields["shipping"]["shipping_postcode"]["priority"] = 140;
			// unset($fields['shipping']['shipping_company']);
			// unset($fields['shipping']['shipping_address_2']);

			return $fields;
		}
	}

	if ( ! function_exists( 'cl_pro_country_locale' ) ) {
		function cl_pro_country_locale( $locale ) {
			$locale['ID'] = array(
				'postcode_before_subdistrict' => false,
				'email' => array(
					'priority' => 65,
				),
				'phone' => array(
					'priority' => 66,
				),
				'company' => array(
					'priority' => 67,
				),
				'address_1' => array(
					'priority' => 68,
					'required' => true,
				),
				'address_2' => array(
					'priority' => 69,
					'required' => false,
				),
				'state' => array(
					'label'       => esc_html__( 'Provinsi', 'cl-ongkir' ),
					'placeholder' => esc_html__( 'Pilh Provinsi...', 'cl-ongkir' ),
					'priority' => 75,
				),
				'city' => array(
					'priority' => 76,
					'required' => true,
				),
				'subdistrict' => array(
					'priority' => 77,
					'required' => true,
				),
				'postcode' => array(
					'priority' => 150,
					'required' => false,
				),
			);
			return $locale;
		}
	}

	if ( ! function_exists( 'cl_unpro_country_locale' ) ) {
		function cl_unpro_country_locale( $locale ) {
			$locale['ID'] = array(
				'postcode_before_subdistrict' => false,
				'email' => array(
					'priority' => 65,
				),
				'phone' => array(
					'priority' => 66,
				),
				'company' => array(
					'priority' => 67,
				),
				'address_1' => array(
					'priority' => 68,
					'required' => true,
				),
				'address_2' => array(
					'priority' => 69,
					'required' => false,
				),
				'state' => array(
					'label'       => esc_html__( 'Provinsi', 'cl-ongkir' ),
					'placeholder' => esc_html__( 'Pilh Provinsi...', 'cl-ongkir' ),
					'priority' => 75,
				),
				'city' => array(
					'priority' => 76,
					'required' => true,
				),
				'postcode' => array(
					'priority' => 150,
					'required' => false,
				),
			);
			return $locale;
		}
	}

	// SWTICH PRO, BASIC, STARTER
	if ( get_option( 'woocommerce_type_account') == 'pro' ) {
		add_action( 'wp_footer', 'cl_get_subdistrict_js' );
		add_filter( 'woocommerce_checkout_fields', 'cl_order_fields');
		add_filter( 'woocommerce_get_country_locale', 'cl_pro_country_locale' );
		add_action( 'wp_ajax_get_subdistrict', 'cl_get_subdistrict_cb' );
		add_action( 'wp_ajax_nopriv_get_subdistrict', 'cl_get_subdistrict_cb' );
		remove_action( 'woocommerce_checkout_update_order_meta', 'cl_woocommerce_ongkir_checkout_field_cities_update_order_meta' ); //STARTER
		add_action( 'woocommerce_checkout_update_order_meta', 'cl_woocommerce_ongkir_checkout_field_subdistrict_update_order_meta' ); //PRO
		add_filter( 'woocommerce_billing_fields', 'cl_custom_woocommerce_billing_subdistrict_fields' );
		add_filter( 'woocommerce_shipping_fields', 'cl_custom_woocommerce_shipping_subdistrict_fields' );
			
	// add the filter 
	}

	/**
	 * Add cart weight total before shipping in chekout form
	 *
	 * @return void
	 */

	if ( ! function_exists( 'cl_add_weight_to_cart' ) ) {
		function cl_add_weight_to_cart() {
			global $cl_ongkir;

			if ( WC()->cart->needs_shipping() ) { ?>
				<tr class="weight">
					<th><?php _e( 'Total weight', 'cl-ongkir' ); ?></th>
					<td><?php echo ( WC()->cart->cart_contents_weight == 0 ) ? '1 ' . get_option( 'woocommerce_weight_unit' ) : WC()->cart->cart_contents_weight . ' ' . get_option( 'woocommerce_weight_unit' ); ?></td>
				</tr>
			<?php }
		}
	}
}