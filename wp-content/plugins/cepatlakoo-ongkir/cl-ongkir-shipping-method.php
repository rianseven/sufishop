<?php
/**
 * @package cl_ongkir
 * @category Core
 * @author eezhal
 */

 if ( !class_exists('CartWeight') ) {
	require_once 'include/cart-weight.php';
 }

class Cl_Ongkir_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Constructor for shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'cl_ongkir_shipping'; // Id for your shipping method. Should be uunique.
		$this->method_title       = esc_html__( 'Cepatlakoo Ongkir', 'cl-ongkir' );  // Title shown in admin
		$this->method_description = esc_html__( 'WooCommerce add-on for indonesian local shipping.', 'cl-ongkir' ); // Description shown in admin

		$this->init();
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		global $woocommerce;
		global $cl_ongkir;
		
		
		// This is part of the settings API. Loads settings you previously init.
		$this->init_settings();

		// This is part of the settings API. Override the method to add your own settings
		if ( strpos( $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']  , 'section=cl_ongkir_shipping' ) ) {

			$this->pro_error = $cl_ongkir->pro_status;
			$this->sta_error = $cl_ongkir->sta_status;
			$type = get_option('woocommerce_type_account');
			
			if ( ( $type == 'starter' || $type == 'basic') && $this->sta_error != 0 ) {
				$this->valid = false;
			} else if ( $type == 'pro' && $this->pro_error != 0 ) {
				$this->valid = false;
			} else if ( $type != 'pro' && $type != 'starter' && $type != 'basic') {
				$this->valid = false;
			} else {
				$this->valid = true;
			}
			
			// Load the settings API
			$this->init_form_fields();
		}

		$this->enabled = !isset($this->settings['enabled']) ? 'no' : $this->settings['enabled'] ;
		$this->api_key = !isset($this->settings['rajaongkir_apikey']) ? null : $this->settings['rajaongkir_apikey'] ;
		$this->base_city = !isset($this->settings['base_city']) ? null : $this->settings['base_city'];
		$this->cl_last_name = !isset($this->settings['cl_last_name']) ? 'no' : $this->settings['cl_last_name'];
		$this->cl_company = !isset($this->settings['cl_company']) ? 'no' : $this->settings['cl_company'];
		$this->cl_address1 = !isset($this->settings['cl_address1']) ? 'no' : $this->settings['cl_address1'];
		$this->cl_address2 = !isset($this->settings['cl_address2']) ? 'no' : $this->settings['cl_address2'];
		$this->cl_email_option = !isset($this->settings['cl_email_option']) ? 'no' : $this->settings['cl_email_option'];
		

		// This can be added as an setting but for this example its forced.
		$this->title = esc_html__( 'Cepatlakoo Ongkir', 'cl-ongkir' );
		// if api key not setted, enabled = false
		// $this->enabled = !isset($this->settings['enabled']) ? true : $this->settings['enabled'] ;
		
		// handle frontend cost request
		add_action( 'wp_ajax_get_costs', 'calculate_shipping' );
		add_action( 'wp_ajax_get_costs_pro', 'calculate_shipping' );
		
		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'cl_process_admin_options' ) );

	}

	public function cl_process_admin_options(){
		if( $this->settings['enabled'] == 'yes' && ( empty($this->settings['base_city']) || $this->settings['base_city'] == 0 || empty( $this->settings['rajaongkir_apikey'] ) ) ){
			add_action( 'admin_notices', 'cl_ongkir_setting_check' );
		}
		else{
			remove_action( 'admin_notices', 'cl_ongkir_setting_check' );
		}
		
		wp_cache_delete ( 'woocommerce_rajaongkir_api_key', 'options' );
		wp_cache_delete ( 'woocommerce_cl_ongkir_base_subdistrict', 'options' );
		wp_cache_delete ( 'woocommerce_type_account', 'options' );
		update_option( 'woocommerce_type_account', $this->settings['type_ongkir'] );
		update_option( 'woocommerce_cl_ongkir_base_subdistrict', $this->settings['base_city'] );
		update_option( 'woocommerce_rajaongkir_api_key', $this->settings['rajaongkir_apikey'] );

		update_option( 'cl_ongkir_services', $_POST['cl_ongkir_service'] );

		require_once( plugin_dir_path( __FILE__ ) . 'include/cl-ongkir-shipping.php' );

		$cl_ongkir_shipping = Cl_Ongkir_Shipping::get_instance();
		$cl_ongkir_shipping->set_api_key( $this->settings['rajaongkir_apikey'] );
		$cl_ongkir_shipping->set_server( $this->settings['type_ongkir'] );
		$GLOBALS['cl_ongkir'] = $cl_ongkir_shipping;

		$type = $this->settings['type_ongkir'];
		if( ( $type == 'starter' || $type == 'basic') && $this->sta_error != 0 ){
			$this->valid = false;
		}		
		else if( $type == 'pro' && $this->pro_error != 0 ){
			$this->valid = false;
		}
		else if( $type != 'pro' && $type != 'starter' && $type != 'basic'){
			$this->valid = false;
		}
		else{
			$this->valid = true;
		}
	}

	/**
	 * Generate html settings form
	 *
	 * @return void
	 */
	public function admin_options() {
		global $cl_ongkir;

		$pro_color = 'color: #ff0000';
		$pro_status = esc_html__( 'Server offline', 'cl-ongkir' );
		$pro_class = 'dashicons-no';
		$sta_color = 'color: #ff0000';
		$sta_status = esc_html__( 'Server offline', 'cl-ongkir' );
		$sta_class = 'dashicons-no';
		
		if( isset($this->pro_error) ){
			if ( $this->pro_error == 0 ) {
				$pro_status = esc_html__( 'Server online', 'cl-ongkir' );
				$pro_color = 'color: #149911';
				$pro_class = 'dashicons-yes';
			}
			else if ( $this->pro_error == 3 ) {
				$pro_status = esc_html__( 'IP Website Anda Sepertinya Diblokir Oleh RajaOngkir', 'cl-ongkir' );
			}
			else{
				$pro_status = esc_html__( 'Server offline', 'cl-ongkir' );
			}
		}

		if( isset($this->sta_error) ){
			if ( $this->sta_error == 0 ) {
				$sta_status = esc_html__( 'Server online', 'cl-ongkir' );
				$sta_color = 'color: #149911';
				$sta_class = 'dashicons-yes';
			}
			else if ( $this->sta_error == 3 ) {
				$sta_status = esc_html__( 'IP Website Anda Sepertinya Diblokir Oleh RajaOngkir', 'cl-ongkir' );
			}
			else{
				$sta_status = esc_html__( 'Server offline', 'cl-ongkir' );
			}
		}
	?>
		<h2><?php esc_html_e('Cepatlakoo Ongkir Shipping', 'cepatlakoo'); ?></h2>

		<div class="cl-server-status">
			<table class="form-table">
				<tr valign="top">
					<th scope="row" class="titledesc"><label><?php esc_html_e( 'Status Server RajaOngkir', 'cl-ongkir' ); ?></label></td>
					<td class="forminp">
						<?php esc_html_e( 'Starter / Basic API:', 'cl-ongkir' ); ?>
						<span style="<?php echo $sta_color; ?>">
							<i class="dashicons <?php echo $sta_class; ?>"></i> <?php echo $sta_status; ?>
						</span>
						
						&nbsp; | &nbsp;

						<?php esc_html_e( 'Pro API:', 'cl-ongkir' ); ?>
						<span style="<?php echo $pro_color; ?>">
							<i class="dashicons <?php echo $pro_class; ?>"></i> <?php echo $pro_status; ?>
						</span>
					</td>
				</tr>
			</table>
		</div>

		<table class="form-table">
			<?php
			$rajaongkir_api = get_option('woocommerce_rajaongkir_api_key');
			$rajaongkir_courrier = $this->settings['courier'];
			$cl_base_city = $this->settings['base_city'];
			$rajaongkir_type = get_option('woocommerce_type_account');
			$type = 'Starter / Basic';
			?>
			
			<?php if ( $this->enabled == 'yes' && ! $rajaongkir_courrier ) : ?>
				<div id="message" class="error fade">
					<p><strong>
					<?php esc_html_e( 'Harap memilih jasa kurir terlebih dahulu.', 'cl-ongkir' ); ?>
					</strong></p>
				</div>
			<?php endif; ?>

			<?php if ( $this->valid == false ) :
				if ( $rajaongkir_type == 'pro'){
					$type = 'Pro';
				}
			?>
				<div id="message" class="error fade">
					<p><strong>
					<?php esc_html_e( 'Tidak bisa terkoneksi dengan server '.$type.' RajaOngkir', 'cl-ongkir' ); ?>
					</strong></p>
				</div>
			<?php endif; ?>

			<?php if ( ! $rajaongkir_api ) : ?>
				<div id="message" class="error fade">
					<p><strong>
						<?php esc_html_e( 'Harap masukkan API Key Raja Ongkir terlebih dahulu.', 'cl-ongkir' ); ?>
					</strong></p>
				</div>
			<?php endif; ?>

			<?php if ( $this->enabled == 'yes' && $this->valid && empty($cl_base_city) && $cl_base_city == 0 ) : ?>
				<div id="message" class="error fade">
					<p><strong>
					<?php esc_html_e( 'Harap memilih kota pengiriman terlebih dahulu.', 'cl-ongkir' ); ?>
					</strong></p>
				</div>
			<?php endif; ?>

			<?php if ( $this->valid == true && ( !$cl_ongkir->check_valid_apikey($rajaongkir_api)) ) : ?>
				<div id="message" class="error fade">
					<p><strong>
						<?php esc_html_e( 'ERROR: API key tidak ditemukan di database Raja Ongkir.', 'cl-ongkir' );?></strong>
					</p>
				</div>
			<?php endif;?>

			<?php $this->generate_settings_html(); ?>
		</table>
	<?php
	}

	/**
	 * Form for shipping method settings in admin
	 *
	 * @return void
	 */
	public function init_form_fields() {
		global $woocommerce;
		global $cl_ongkir;
		$req_city = '-';

		if( $this->valid && strpos($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']  , 'section=cl_ongkir_shipping') ){
			$req_city = $cl_ongkir->get_cities( $woocommerce->countries->get_base_state() );
			if($req_city == '-'){
				$req_city = esc_html__('Pastikan API Key dan tipe akun yang diinput sudah benar', 'cl-ongkir');
			}
		}
		else{
			$req_city = esc_html__('Pastikan API Key dan tipe akun yang diinput sudah benar', 'cl-ongkir');
		}

		$allowed_html = array(
			'a' => array(
		        'href' => array(),
		        'target' => array()
		    ),
		);

	    $this->form_fields = array(
	    	'enabled' => array(
			    'title'   => esc_html__( 'Aktifkan Ongkir', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Aktifkan metode pengiriman dengan Cepatlakoo Ongkir.', 'cl-ongkir' ),
			    'description' => esc_html__( 'Refresh halaman ini untuk menghapus cache.', 'cl-ongkir' ),
			),

			'rajaongkir_apikey' => array(
		        'title'       => esc_html__( 'API Key Raja Ongkir', 'cl-ongkir' ),
		        'type'        => 'text',
		        'id'          => 'woocommerce_rajaongkir_api_key',
		        'description' => wp_kses( __(
			        				'Pastikan API Key yang diinput benar dan Simpan perubahan setelah memasukkan API agar Anda dapat memiliki basis kota pengiriman. <br>Tidak punya API Key Raja Ongkir? <a href="'. esc_url('http://rajaongkir.com/akun/daftar') .'" target="_blank">Daftar Sekarang</a>. ', 'cl-ongkir' ), $allowed_html
				),
			),

			'type_ongkir' => array(
		        'title'       => esc_html__( 'Tipe Akun RajaOngkir', 'cl-ongkir' ),
		        'type'        => 'select',
		        'id'          => 'woocommerce_rajaongkir_type_ongkir',
		        'class'       => 'chosen_select',
		        'description' => esc_html__( 'Tipe akun RajaOngkir yang Anda gunakan.', 'cl-ongkir' ),
		        'options'     => array(
					'starter'  => esc_html__( 'Starter', 'cl-ongkir' ),
					'basic'    => esc_html__( 'Basic', 'cl-ongkir' ),
					'pro'  	 => __( 'Pro', 'cl-ongkir' ),
				),
		    ),

			'base_city' => array(
		        'title'       => esc_html__( 'Lokasi Gudang Pengiriman Produk', 'cl-ongkir' ),
		        'type'        => 'select',
		        'id'          => 'woocommerce_cl_ongkir_base_city',
		        'class'       => 'chosen_select',
		        'description' => esc_html__( 'Lokasi gudang tempat mengirim produk. Pastikan API Key sudah dimasukkan agar daftar kota dapat muncul.', 'cl-ongkir' ),
		        'options'     => $req_city,
		        'default'	  => esc_html__( 'Pilih Kota Pengiriman', 'cl-ongkir' )
		    ),

			'courier' => array(
			    'title'       => esc_html__( 'Jasa Kurir', 'cl-ongkir' ),
			    'type'        => 'multiselect',
			    'id'          => 'woocommerce_cl_ongkir_couriers',
			    'class'       => 'chosen_select',
			    'multiple'	  =>  true,
			    'description' => esc_html__( 'Jasa kurir yang ingin digunakan.', 'cl-ongkir' ),
			    'options' => $this->get_courrier( $this->get_option( 'type_ongkir' ) )
			),

			'cl_enable_service' => array(
				'type' => 'cl_enable_service',
			),

			'show_weight' => array(
		        'title'       => esc_html__( 'Berat Minimum Produk ('.get_option('woocommerce_weight_unit').')', 'cl-ongkir' ),
		        'label'       => esc_html__( 'Berat minimum produk agar dapat melakukan perhitungan ongkir.', 'cl-ongkir' ),
		        'type'        => 'text',
		        'id'          => 'woocommerce_cl_ongkir_minimum_weight',
		        'description' => get_option(''),
		        'default'     => '1'
			),
			
			'cl_hide_estimate'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Estimasi Waktu Pengantaran', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan estimasi waktu pengantaran pada halaman checkout.', 'cl-ongkir' ),
			),
			
			'cl_last_name'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Field Last Name', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
				'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan field Last Name pada halaman checkout dan jadikan first name sebagai Full Name.', 'cl-ongkir' ),
			),
			
			'cl_company'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Field Company Name', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan field Company Name pada halaman checkout.', 'cl-ongkir' ),
			),
			
			'cl_country'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Field Country', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan field Country pada halaman checkout.', 'cl-ongkir' ),
			),
			
			'cl_address1'	=> array(
			    'title'   => esc_html__( 'Field Address 1 Menggunakan textarea', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Membuat field Address Line 1 menggunakan textarea yang memiliki jumlah baris lebih banyak.', 'cl-ongkir' ),
			),
			
			'cl_address2'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Field Address Line 2', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan field Address Baris Kedua pada halaman checkout.', 'cl-ongkir' ),
			),
			
			'cl_email_option'	=> array(
			    'title'   => esc_html__( 'Matikan required di field email', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Matikan required di field email pada halaman checkout. Jadi pengunjung bisa order tanpa harus mengisi email', 'cl-ongkir' ),
			),
			
			'cl_notes'	=> array(
			    'title'   => esc_html__( 'Sembunyikan Field Order Notes', 'cl-ongkir' ),
			    'type'    => 'checkbox',
			    'default' => 'no',
			    'label'   => esc_html__( 'Ya', 'cl-ongkir' ),
			    'description' => esc_html__( 'Sembunyikan Field Order Notes pada halaman checkout.', 'cl-ongkir' ),
			),
	    );
	}

	/**
	 * Calculate shipping cost in checkout form.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		global $woocommerce;
		global $cl_ongkir;
		
		$current_shipping_city = $package['destination']['city'];
		$current_shipping_subdistrict = '';
		if ( ! is_cart() ) {
			if ( !empty($package['destination']['cl_ongkir_code']) && !empty($package['destination']['cl_ongkir_type']) ) {
				$type_ongkir = $package['destination']['cl_ongkir_type'];
				$current_shipping_subdistrict = $package['destination']['cl_ongkir_code'];
			} else {
				$type_ongkir = 'city';
				$current_shipping_subdistrict = $package['destination']['city'];
			}
		} else {
			$type_ongkir = 'city';
		}

		$current_cart_weight = $this->get_cart_weight(true); // in gram
		$height = 10;
		$width = 10;
		$length = 10;
		$couriers = $this->settings['courier'];
		$shipping_couriers = array(); // results
		$type_acc = get_option('woocommerce_type_account');

		if ( $couriers == null || ( $type_acc == 'starter' && in_array( "all", $couriers ) ) ) {
			$couriers = ['jne', 'tiki', 'pos'];
		} elseif ( in_array( "all", $couriers ) && $type_acc == 'basic' ) {
			$couriers = ['jne', 'tiki', 'pos', 'pcp', 'esl', 'rpx'];
		} elseif ( in_array( "all", $couriers ) && $type_acc == 'pro' ) {
			// $couriers = ['jne', 'tiki', 'pos', 'pcp', 'esl', 'rpx', 'pandu', 'wahana', 'sicepat', 'jnt', 'pahala', 'sap', 'jet', 'indah', 'slis', 'expedito', 'first' ];
			$couriers = 'jne:pos:tiki:rpx:esl:pcp:pandu:wahana:sicepat:jnt:pahala:cahaya:sap:jet:indah:dse:slis:ncs:star:nss:first:ninja:lion:idl';
		} elseif ( $type_acc == 'pro' ) {
			$couriers = implode( ':', $couriers );
			// $couriers = ['jne:pos:tiki:rpx:esl:pcp:pandu:wahana:sicepat:jnt:pahala:cahaya:sap:jet:indah:dse:slis:ncs:star:nss' ];
		}

		if($current_cart_weight > 50000){			
			if($type_acc == 'pro'){
				$couriers = explode(":",$couriers);
				$couriers = array_diff($couriers, ['pos']);
				$couriers = implode( ':', $couriers );
			}
			elseif(is_array($couriers) && in_array('pos', $couriers )){
				$couriers = array_diff($couriers, ['pos']);
			}
		}

		if ( get_option('woocommerce_type_account') == 'pro' ) {
			$result = $this->get_available_shippings_pro(
				$type_ongkir,
				$current_shipping_subdistrict,
				$current_cart_weight,
				$couriers,
				$height,
				$width,
				$length
			);
			
			$shipping_couriers = $result;
		} else {
			foreach ( $couriers as $courier ) {
				$result = $this->get_available_shippings_starter_basic(
					$current_shipping_city,
					$current_cart_weight,
					$courier
				);
				$shipping_couriers[] = $result[0];
			}
		}
		
		// if( ! is_cart() ) {
			if ( $shipping_couriers != '-' && !empty($shipping_couriers) ) {
				$servs = array();
				$opts = get_option('cl_ongkir_services');
				
				foreach ( @$shipping_couriers as $courier ) {
					if ( isset( $courier['code'] ) ) {
						$servs[$courier['code']] = ( isset($opts[$courier['code']]) && !empty($opts[$courier['code']] )) ? $opts[$courier['code']] : false;
					}
					
					if ( is_array( $courier ) || is_object( $courier ) ) {
						foreach ( $courier['costs'] as $item ) {
							foreach ( $item['cost'] as $cost ) {
								if ( ( isset($servs[$courier['code']][$item['service']] ) && $servs[$courier['code']][$item['service']] === 'on') || ( isset($servs[$courier['code']]) && $servs[$courier['code']] === 'all' ) ) {
									if ( $cost['value'] ) {
										if( isset($this->settings['cl_hide_estimate']) && $this->settings['cl_hide_estimate'] == 'yes' ){
											$label = $courier['name'] . " " . $item['service'];
										}
										else{
											$label = $courier['name'] . " " . $item['service'] . " " . __( 'Estimasi:', 'cl-ongkir' ) .  $cost['etd'] . " " . __( 'hari', 'cl-ongkir' );
										}
										$this->add_rate(
											array(
												'id' => $this->id . "_" . $courier['name'] . "_" . $item['service'] ,
												'label' => $label,
												'cost' => $cost['value'],
												'calc_tax' => 'per_item',
											)
										);
									}
								}
							}
						}
					}
				}
			}
		// }
	}

	/**
	 * Get current cart weight.
	 *
	 * @param bool $to_gram
	 * @return int
	 */
	private function get_cart_weight($to_gram = false) {
		global $woocommerce;

		$current_cart_weight = $woocommerce->cart->cart_contents_weight;
		$minimum_weight = get_option('woocommerce_cl_ongkir_minimum_weight');

		// if woocommerce_cl_ongkir_minimum_weight value not set
		if ( ! $minimum_weight ) {
			$minimum_weight = 1;
		}

		if ( $current_cart_weight < $minimum_weight ) {
			$current_cart_weight = $minimum_weight;
		}

		// if flag is true, convert to gram and return it
		if ( $to_gram ) {
			$weight_unit = get_option('woocommerce_weight_unit');

			return CartWeight::toGram($current_cart_weight, $weight_unit);
		}

		return $current_cart_weight;
	}

	/**
	 * Get available shippings based on store city and customer city
	 *
	 * @param int $shipping_city
	 * @param int $cart_weight
	 * @param string $courier
	 * @return array
	 */
	private function get_available_shippings_starter_basic( $shipping_city, $cart_weight, $courier ) {
		if ( $origin_city = $this->settings['base_city'] ) {
			global $cl_ongkir;

			return $cl_ongkir->get_costs(
				$origin_city,
				$shipping_city,
				$cart_weight,
				$courier
			);
		}
	}

	/**
	 * Get available shippings based on store city and customer city
	 *
	 * @param int $shipping_city
	 * @param int $cart_weight
	 * @param string $courier
	 * @return array
	 */
	private function get_available_shippings_pro( $type_ongkir, $shipping_subdistrict, $cart_weight, $courier, $height, $width, $length ) {
		$origin_city = null;

		if ( ! $origin_city ) {
			$origin_city = $this->settings['base_city'];
			global $cl_ongkir;

			return $cl_ongkir->get_costs_pro(
				$type_ongkir,
				$origin_city,
				$shipping_subdistrict,
				$cart_weight,
				$courier,
				$height,
      			$width,
      			$length
			);
		} else {
			echo esc_html( 'Harap cek kembali pengaturan Cepatlakoo Ongkir Anda', 'cl-ongkir' );
		}

	}

	/**
	 * Get Courrier
	 *
	 * @param string $type_ongkir
	 * @return array
	 */
	private function get_courrier( $type_ongkir ) {
		$newapi = $this->get_option('rajaongkir_apikey');
		$newtype = $this->get_option('type_ongkir');
		wp_cache_delete ( 'woocommerce_rajaongkir_api_key', 'options' );
		wp_cache_delete ( 'woocommerce_type_account', 'options' );
		update_option( 'woocommerce_type_account', $newtype );
		update_option( 'woocommerce_rajaongkir_api_key', $newapi );

		$list_ekspedisi = array(
	    	'all' => 'All',
	    	'jne' => 'Jalur Nugraha Express (JNE)',
	    	'pos' => 'POS Indonesia (POS)',
	    	'tiki'=> 'Citra Van Titipan Kilat (TIKI)',
	    	'pcp' => 'Priority Cargo and Package (PCP)',
	    	'esl' => 'Eka Sari Lorena (ESL)',
	    	'rpx' => 'RPX Holding (RPX)',
	    	'pandu'=> 'Pandu Logistics (PANDU)',
	    	'wahana'=> 'Wahana Prestasi Logistik (WAHANA)',
	    	'sicepat'=> 'SiCepat Express (SICEPAT)',
	    	'jnt'=> 'J&T Express (J&T)',
	    	'pahala'=> 'Pahala Kencana Express (PAHALA)',
	    	'cahaya'=> 'Cahaya Logistik (CAHAYA)',
	    	'sap'=> 'SAP Express (SAP)',
	    	'jet'=> 'JET Express (JET)',
	    	'indah'=> 'Indah Logistic (INDAH)',
	    	'slis'=> 'Solusi Ekspres (SLIS)',
			// 'expedito'=> 'Expedito*',
	    	'dse'=> '21 Express (DSE)',
			'first'=> 'First Logistics (FIRST)',
			'ncs' => 'Nusantara Card Semesta (NCS)',
			'star' => 'Star Cargo (STAR)',
			'nss' => 'Nusantara Surya Sakti Express (NSS)',
			'ninja' => 'Ninja Xpress',
			'lion' => 'Lion Parcel',
			'idl' => 'IDL Cargo',
	    );

		if ( $type_ongkir == 'starter' ) {
			$list = array_slice($list_ekspedisi, 0, 4);
		} elseif ( $type_ongkir == 'basic' ) {
			$list = array_slice($list_ekspedisi, 0, 7);
		} elseif ( $type_ongkir == 'pro' ) {
			$list = $list_ekspedisi;
		} else {
			$list = array_slice($list_ekspedisi, 0, 3);
		}
		return $list;
	}
	
	/**
	 * Form for service settings in admin
	 *
	 * @return void
	 */
	public function generate_cl_enable_service_html() {
		ob_start();
		?>
		<tr class="cl-service-parent">
		<th scope="row" class="titledesc"><?php esc_html_e( 'Pilih Service Kurir', 'cl-ongkir' ); ?></th>	
		<td style="height: 100px;">
			<?php $cl_services = get_option('cl_ongkir_services'); ?>
			<input type="hidden" name="cl_ongkir_service[esl]" value="all">
			<input type="hidden" name="cl_ongkir_service[pandu]" value="all">
			<input type="hidden" name="cl_ongkir_service[wahana]" value="all">
			<input type="hidden" name="cl_ongkir_service[J&T]" value="all">
			<input type="hidden" name="cl_ongkir_service[cahaya]" value="all">
			<input type="hidden" name="cl_ongkir_service[indah]" value="all">
			<input type="hidden" name="cl_ongkir_service[first]" value="all">
			<input type="hidden" name="cl_ongkir_service[ninja]" value="all">

			<table class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr>
						<td width="200px"><b><?php esc_html_e( 'Nama Kurir', 'cl-ongkir' ); ?></b></td>
						<td><b><?php esc_html_e( 'Jenis Layanan', 'cl-ongkir' ); ?></b></td>
					</tr>
				</thead>
				<tbody>
					<tr class="cl-service-row jne">
						<td>JNE</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[jne][OKE]" id = "cl-ongkir-jne-oke" type="checkbox" <?php echo (isset($cl_services['jne']['OKE']) && $cl_services['jne']['OKE'] === "on") ? "checked" : ''; ?>> <label for="cl-ongkir-jne-oke">OKE</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jne][REG]" id = "cl-ongkir-jne-reg" type="checkbox" <?php echo (isset($cl_services['jne']['REG']) && $cl_services['jne']['REG'] === "on") ? "checked" : ''; ?>> <label for="cl-ongkir-jne-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jne][YES]" id = "cl-ongkir-jne-yes" type="checkbox" <?php echo (isset($cl_services['jne']['YES']) && $cl_services['jne']['YES'] === "on") ? "checked" : ''; ?>> <label for="cl-ongkir-jne-yes">YES</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jne][CTC]" id = "cl-ongkir-jne-ctc" type="checkbox" <?php echo (isset($cl_services['jne']['CTC']) && $cl_services['jne']['CTC'] === "on") ? "checked" : ''; ?>> <label for="cl-ongkir-jne-ctc">City To City</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jne][CTCYES]" id = "cl-ongkir-jne-ctcyes" type="checkbox" <?php echo (isset($cl_services['jne']['CTCYES']) && $cl_services['jne']['CTCYES'] === "on") ? "checked" : ''; ?>> <label for="cl-ongkir-jne-ctcyes">City To City YES</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row pos">
						<td>POS Indonesia</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[pos][Paket Kilat Khusus]" id="cl-ongkir-pos-kilatkhusus" type="checkbox" <?php echo (isset($cl_services['pos']['Paket Kilat Khusus']) && $cl_services['pos']['Paket Kilat Khusus'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pos-kilatkhusus">Kilat Khusus</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pos][Express Next Day Barang]" id="cl-ongkir-pos-expressnextday" type="checkbox" <?php echo (isset($cl_services['pos']['Express Next Day Barang']) && $cl_services['pos']['Express Next Day Barang'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pos-expressnextday">Express Next Day</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pos][Paketpos Dangerous Goods]" id="cl-ongkir-pos-danggoods" type="checkbox" <?php echo (isset($cl_services['pos']['Paketpos Dangerous Goods']) && $cl_services['pos']['Paketpos Dangerous Goods'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pos-danggoods">Dangerous Goods</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pos][Paketpos Valuable Goods]" id="cl-ongkir-pos-valgoods" type="checkbox" <?php echo (isset($cl_services['pos']['Paketpos Valuable Goods']) && $cl_services['pos']['Paketpos Valuable Goods'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pos-valgoods">Valuable Goods</label></input></div> 
						</td>
					</tr>

					<tr class="cl-service-row tiki">
						<td>TIKI</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][TRC]" id="cl-ongkir-tiki-trc" type="checkbox" <?php echo (isset($cl_services['tiki']['TRC']) && $cl_services['tiki']['TRC'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-trc">Trucking Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][REG]" id="cl-ongkir-tiki-reg" type="checkbox" <?php echo (isset($cl_services['tiki']['REG']) && $cl_services['tiki']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][ECO]" id="cl-ongkir-tiki-eco" type="checkbox" <?php echo (isset($cl_services['tiki']['ECO']) && $cl_services['tiki']['ECO'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-eco">Economy</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][ONS]" id="cl-ongkir-tiki-ons" type="checkbox" <?php echo (isset($cl_services['tiki']['ONS']) && $cl_services['tiki']['ONS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-ons">Over Night Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][SDS]" id="cl-ongkir-tiki-sds" type="checkbox" <?php echo (isset($cl_services['tiki']['SDS']) && $cl_services['tiki']['SDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-sds">Sameday Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[tiki][HDS]" id="cl-ongkir-tiki-hds" type="checkbox" <?php echo (isset($cl_services['tiki']['HDS']) && $cl_services['tiki']['HDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-tiki-hds">Holiday Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row pcp">
						<td>PCP</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[pcp][ONS]" id="cl-ongkir-pcp-ons" type="checkbox" <?php echo (isset($cl_services['pcp']['ONS']) && $cl_services['pcp']['ONS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pcp-ons">Overnight Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pcp][NFS]" id="cl-ongkir-pcp-nfs" type="checkbox" <?php echo (isset($cl_services['pcp']['NFS']) && $cl_services['pcp']['NFS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pcp-nfs">Next Flight Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pcp][REG]" id="cl-ongkir-pcp-reg" type="checkbox" <?php echo (isset($cl_services['pcp']['REG']) && $cl_services['pcp']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pcp-reg">Regular Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row esl">
						<td>ESL</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[esl][RPX\/RDX]" id="cl-ongkir-esl-reg" type="checkbox" checked disabled ><label for="cl-ongkir-esl-reg">Paket Dokumen / Barang</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row rpx">
						<td>RPX</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[rpx][SDP]" id="cl-ongkir-rpx-sdp" type="checkbox" <?php echo (isset($cl_services['rpx']['SDP']) && $cl_services['rpx']['SDP'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-rpx-sdp">SameDay Package</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[rpx][MDP]" id="cl-ongkir-rpx-mdp" type="checkbox" <?php echo (isset($cl_services['rpx']['MDP']) && $cl_services['rpx']['MDP'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-rpx-mdp">MidDay Package</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[rpx][NDP]" id="cl-ongkir-rpx-ndp" type="checkbox" <?php echo (isset($cl_services['rpx']['NDP']) && $cl_services['rpx']['NDP'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-rpx-ndp">NextDay Package</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[rpx][RGP]" id="cl-ongkir-rpx-rgp" type="checkbox" <?php echo (isset($cl_services['rpx']['RGP']) && $cl_services['rpx']['RGP'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-rpx-rgp">Regular Package</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row pandu">
						<td>Pandu Express</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[pandu][REG]" id="cl-ongkir-pandu-reg" type="checkbox" checked disabled ><label for="cl-ongkir-pandu-reg">Regular</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row wahana">
						<td>Wahana</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[wahana][REG]" id="cl-ongkir-wahana-reg" type="checkbox" checked disabled ><label for="cl-ongkir-wahana-reg">Regular</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row sicepat">
						<td>Si Cepat</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[sicepat][REG]" id="cl-ongkir-sicepat-reg" type="checkbox" <?php echo (isset($cl_services['sicepat']['REG']) && $cl_services['sicepat']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sicepat-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[sicepat][BEST]" id="cl-ongkir-sicepat-best" type="checkbox" <?php echo (isset($cl_services['sicepat']['BEST']) && $cl_services['sicepat']['BEST'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sicepat-best">Besok Sampai Tujuan</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[sicepat][Priority]" id="cl-ongkir-sicepat-priority" type="checkbox" <?php echo (isset($cl_services['sicepat']['Priority']) && $cl_services['sicepat']['Priority'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sicepat-priority">Priority</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row jnt">
						<td>J&T</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[J&T][EZ]" id="cl-ongkir-jnt-ez" type="checkbox" checked disabled ><label for="cl-ongkir-jnt-ez">Regular Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row pahala">
						<td>Pahala</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[pahala][REGULER]" id="cl-ongkir-pahala-reg" type="checkbox" <?php echo (isset($cl_services['pahala']['REGULER']) && $cl_services['pahala']['REGULER'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pahala-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pahala][PRIMA EXPRESS]" id="cl-ongkir-pahala-prima" type="checkbox" <?php echo (isset($cl_services['pahala']['PRIMA EXPRESS']) && $cl_services['pahala']['PRIMA EXPRESS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pahala-prima">Prima Express</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[pahala][SUPER EXPRESS]" id="cl-ongkir-pahala-super" type="checkbox" <?php echo (isset($cl_services['pahala']['SUPER EXPRESS']) && $cl_services['pahala']['SUPER EXPRESS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-pahala-super">Super Express</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row cahaya">
						<td>Cahaya</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[cahaya][REG]" id="cl-ongkir-cahaya-reg" type="checkbox" checked disabled ><label for="cl-ongkir-cahaya-reg">Regular</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row sap">
						<td>SAP Express</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[sap][REG]" id="cl-ongkir-sap-reg" type="checkbox" <?php echo (isset($cl_services['sap']['REG']) && $cl_services['sap']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sap-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[sap][sds]" id="cl-ongkir-sap-sds" type="checkbox" <?php echo (isset($cl_services['sap']['sds']) && $cl_services['sap']['sds'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sap-sds">Same Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[sap][ODS]" id="cl-ongkir-sap-ods" type="checkbox" <?php echo (isset($cl_services['sap']['ODS']) && $cl_services['sap']['ODS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-sap-ods">One Day Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row jet">
						<td>JET Express</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[jet][REG]" id="cl-ongkir-jet-reg" type="checkbox" <?php echo (isset($cl_services['jet']['REG']) && $cl_services['jet']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-jet-reg">Regular</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jet][CRG]" id="cl-ongkir-jet-crg" type="checkbox" <?php echo (isset($cl_services['jet']['CRG']) && $cl_services['jet']['CRG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-jet-crg">Cargo</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[jet][PRI]" id="cl-ongkir-jet-pri" type="checkbox" <?php echo (isset($cl_services['jet']['PRI']) && $cl_services['jet']['PRI'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-jet-pri">Priority</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row indah">
						<td>Indah Logistic</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[indah][REG]" id="cl-ongkir-indah-reg" type="checkbox" checked disabled ><label for="cl-ongkir-indah-reg">Regular</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row slis">
						<td>Solusi Express</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[slis][REGULAR]" id="cl-ongkir-slis-reg" type="checkbox" <?php echo (isset($cl_services['slis']['REGULAR']) && $cl_services['slis']['REGULAR'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-slis-reg">Regular Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[slis][EXPRESS]" id="cl-ongkir-slis-exp" type="checkbox" <?php echo (isset($cl_services['slis']['EXPRESS']) && $cl_services['slis']['EXPRESS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-slis-exp">Express Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row dse">
						<td>21 Express</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[dse][SDS]" id="cl-ongkir-dse-sds" type="checkbox" <?php echo (isset($cl_services['dse']['SDS']) && $cl_services['dse']['SDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-dse-sds">Same Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[dse][ONS]" id="cl-ongkir-dse-ons" type="checkbox" <?php echo (isset($cl_services['dse']['ONS']) && $cl_services['dse']['ONS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-dse-ons">Over Night Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[dse][ECO]" id="cl-ongkir-dse-eco" type="checkbox" <?php echo (isset($cl_services['dse']['ECO']) && $cl_services['dse']['ECO'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-dse-eco">Regular Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row first">
						<td>First Logistics</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[first][REG]" id="cl-ongkir-first-reg" type="checkbox" checked disabled ><label for="cl-ongkir-first-reg">Regular</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row ncs">
						<td>NCS</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[ncs][NRS]" id="cl-ongkir-ncs-nrs" type="checkbox" <?php echo (isset($cl_services['ncs']['NRS']) && $cl_services['ncs']['NRS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-ncs-nrs">Regular Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[ncs][ONS]" id="cl-ongkir-ncs-ons" type="checkbox" <?php echo (isset($cl_services['ncs']['ONS']) && $cl_services['ncs']['ONS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-ncs-ons">Overnight Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[ncs][SDS]" id="cl-ongkir-ncs-sds" type="checkbox" <?php echo (isset($cl_services['ncs']['SDS']) && $cl_services['ncs']['SDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-ncs-sds">Same Day Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row star">
						<td>STAR Cargo</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[star][Reguler]" id="cl-ongkir-star-reg" type="checkbox" <?php echo (isset($cl_services['star']['Reguler']) && $cl_services['star']['Reguler'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-star-reg">Reguler</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[star][Express]" id="cl-ongkir-star-exp" type="checkbox" <?php echo (isset($cl_services['star']['Express']) && $cl_services['star']['Express'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-star-exp">Express</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[star][Dokumen]" id="cl-ongkir-star-doc" type="checkbox" <?php echo (isset($cl_services['star']['Dokumen']) && $cl_services['star']['Dokumen'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-star-doc">Dokumen Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[star][MOTOR]" id="cl-ongkir-star-mot" type="checkbox" <?php echo (isset($cl_services['star']['MOTOR']) && $cl_services['star']['MOTOR'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-star-mot">Motor Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[star][MOTOR 150 - 250 CC]" id="cl-ongkir-star-mot2" type="checkbox" <?php echo (isset($cl_services['star']['MOTOR 150 - 250 CC']) && $cl_services['star']['MOTOR 150 - 250 CC'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-star-mot2">Motor 150 - 250 CC Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row nss">
						<td>Nusantara Surya Sakti</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[nss][REG]" id="cl-ongkir-nss-reg" type="checkbox" <?php echo (isset($cl_services['nss']['REG']) && $cl_services['nss']['REG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-nss-reg">Regular Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[nss][ODS]" id="cl-ongkir-nss-ods" type="checkbox" <?php echo (isset($cl_services['nss']['ODS']) && $cl_services['nss']['ODS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-nss-ods">One Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[nss][SDS]" id="cl-ongkir-nss-sds" type="checkbox" <?php echo (isset($cl_services['nss']['SDS']) && $cl_services['nss']['SDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-nss-sds">Same Day Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row ninja">
						<td>Ninja Xpress</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[ninja][STANDARD]" id="cl-ongkir-ninja-standard" type="checkbox" <?php echo (isset($cl_services['ninja']['STANDARD']) && $cl_services['ninja']['STANDARD'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-ninja-standard">Standard Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row lion">
						<td>Lion Parcel</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[lion][ONEPACK]" id="cl-ongkir-lion-ods" type="checkbox" <?php echo (isset($cl_services['lion']['ONEPACK']) && $cl_services['lion']['ONEPACK'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-lion-ods">One Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[lion][REGPACK]" id="cl-ongkir-lion-reg" type="checkbox" <?php echo (isset($cl_services['lion']['REGPACK']) && $cl_services['lion']['REGPACK'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-lion-reg">Regular Service</label></input></div>
						</td>
					</tr>

					<tr class="cl-service-row idl">
						<td>IDL Cargo</td>
						<td>
							<div class="courier-choise"><input name="cl_ongkir_service[idl][iCon]" id="cl-ongkir-idl-con" type="checkbox" <?php echo (isset($cl_services['idl']['iCon']) && $cl_services['idl']['iCon'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-idl-con">Same Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[idl][iREG]" id="cl-ongkir-idl-reg" type="checkbox" <?php echo (isset($cl_services['idl']['iREG']) && $cl_services['idl']['iREG'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-idl-reg">Regular Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[idl][iSDS]" id="cl-ongkir-idl-sds" type="checkbox" <?php echo (isset($cl_services['idl']['iSDS']) && $cl_services['idl']['iSDS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-idl-sds">Same Day Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[idl][iONS]" id="cl-ongkir-idl-ons" type="checkbox" <?php echo (isset($cl_services['idl']['iONS']) && $cl_services['idl']['iONS'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-idl-ons">Overnight Service</label></input></div>
							<div class="courier-choise"><input name="cl_ongkir_service[idl][iSCF]" id="cl-ongkir-idl-scf" type="checkbox" <?php echo (isset($cl_services['idl']['iSCF']) && $cl_services['idl']['iSCF'] === "on") ? "checked" : ''; ?> ><label for="cl-ongkir-idl-scf">Special Fleet</label></input></div>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		</tr>
		<style>
		.courier-choise {
			position: relative;
			float: left;
			padding: 5px;
		}
		.courier-label {
			min-width: 150px;
		}
		</style>
		 <?php
		return ob_get_clean();
	}
}
?>