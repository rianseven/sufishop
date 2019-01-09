<?php
/**
 *
 * @author eezhal
 * @package myongkir/class
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('Request') ) {	
	require_once 'request.php';
}

require_once 'helper-functions.php';

class Cl_Ongkir_Shipping {

	private $api_key = '';

	private static $request = null;

	protected static $instance;

	public function __construct() {

		if( strpos($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']  , 'section=cl_ongkir_shipping') ){
			if( false !== get_transient('cl-server-status-starter') && false !== get_transient('cl-server-status-pro') ){
				$this->pro_status = get_transient('cl-server-status-pro');
				$this->sta_status = get_transient('cl-server-status-starter');
			}
			else{
				$this->pro_status = $this->cl_check_connection('https://pro.rajaongkir.com/api');
				$this->sta_status = $this->cl_check_connection('http://api.rajaongkir.com/');
		
				set_transient('cl-server-status-pro', $this->pro_status, 30 * MINUTE_IN_SECONDS);
				set_transient('cl-server-status-starter', $this->sta_status, 30 * MINUTE_IN_SECONDS);
			}
		}
		
		$type = get_option('woocommerce_type_account');
		$this->set_server($type);

		return self::$request;
	}

	/**
	 * Get instance of this class.
	 *
	 * @return MyOngkir_Shipping
	 */
	public static function get_instance() {
		if(!static::$instance) {
			static ::$instance = new self;
		}

		return static::$instance;
	}

	/**
	 * Set rajaongkir api key for request.
	 *
	 * @param string $api_key
	 * @return void
	 */
	public function set_api_key($api_key) {
		$this->api_key = $api_key;
		// set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
	}

	public function set_server($type){
		if( $type == 'starter' ){
			$SERVER = 'http://api.rajaongkir.com/starter';
		}elseif( $type == 'basic' ){
			$SERVER = 'http://api.rajaongkir.com/basic';
		}elseif( $type == 'pro' ){
			$SERVER = 'https://pro.rajaongkir.com/api';
		}else{
			if ( isset($this->sta_status) && $this->sta_status == 0 ){
				$SERVER = 'http://api.rajaongkir.com/starter';
			}
			else {
				$SERVER = 'https://pro.rajaongkir.com/api';
			}
		}
		
		self::$request = new Request(array(
			'server' => $SERVER
		));
		$this->server = $SERVER;
		// set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
	}

	/**
	 * Get shipping costs.
	 *
	 * @access public
	 * @param integer $from
	 * @param integer $to
	 * @param float $weight
	 * @return array
	 */
	public function get_costs( $from, $to, $weight, $courier = 'jne') {
		if(empty($to))
				return;

		if( false !== get_transient('cl-ongkir-get_costs'.$from.'-'.$to.'-'.$weight.'-'.$courier) && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
		get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
			return get_transient('cl-ongkir-get_costs'.$from.'-'.$to.'-'.$weight.'-'.$courier);
		} else {
			$result = self::$request->post('/cost', array(
				'key'         		=> $this->api_key,
				'origin'      		=> $from,
				'destination' 		=> $to,
				'weight'      		=> $weight,
				'courier'     		=> $courier
			));

			try {
				if( $result) {
					if($result->rajaongkir->status->code != 400){
						$costs = object_to_array( $result->rajaongkir->results );
						$new_costs = object_to_array( $costs );
						set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-get_costs'.$from.'-'.$to.'-'.$weight.'-'.$courier, $new_costs, 12 * HOUR_IN_SECONDS);
					}else{
						$new_costs = '-';
					}
					return $new_costs;
				}else{
					return '-';
				}
			} catch ( Exception $e ) {
				var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
				return false;
			}
		}
	}

	/**
	 * Get shipping costs.
	 *
	 * @access public
	 * @param integer $from
	 * @param integer $to
	 * @param float $weight
	 * @return array
	 */
	public function get_costs_pro( $type_ongkir, $from, $to, $weight, $courier = 'jne', $height = '10',
		$width = '10', $length = '10' ) {
		if( empty( $to ) )
			return;
			
		if( false !== get_transient('cl-ongkir-get_costs_pro'.$type_ongkir.'-'.$from.'-'.$to.'-'.$weight.'-'.$courier.'-'.$height.'-'.$width.'-'.$length) && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
		get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
			return get_transient('cl-ongkir-get_costs_pro'.$type_ongkir.'-'.$from.'-'.$to.'-'.$weight.'-'.$courier.'-'.$height.'-'.$width.'-'.$length);
		} else {
			$result = self::$request->post('/cost', array(
				'key'         		=> $this->api_key,
				'origin'      		=> $from,
				"originType" 		=> 'city',
				'destination' 		=> $to,
				"destinationType" 	=> $type_ongkir,
				'weight'      		=> $weight,
				'courier'     		=> $courier,
				'height'			=> $height,
				'width'				=> $width,
				'length'			=> $length
			));

			try {
				if( $result) {
					if( $result->rajaongkir->status->code != 400 ) {
						// $costs = object_to_array( $result->rajaongkir->results );
						// $new_costs = object_to_array( $costs );
						$new_costs = object_to_array( $result->rajaongkir->results );
						set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-get_costs_pro'.$type_ongkir.'-'.$from.'-'.$to.'-'.$weight.'-'.$courier.'-'.$height.'-'.$width.'-'.$length, $new_costs, 12 * HOUR_IN_SECONDS);
					} else {
						$new_costs = '-';
					}
					return $new_costs;
				} else {
					return '-';
				}
			} catch ( Exception $e ) {
				var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
				return false;
			}
		}
	}

	/**
	 * Currency to IDR converter.
	 *
	 * @param string $woocommerce_currency, integer $amount
	 * @return integer
	 */
	public function convert_currency( $woocommerce_currency, $amount = 1 ) {
		if ( is_null($woocommerce_currency) || $woocommerce_currency == '' ) {
			try {
				$req = new Request(array(
					'server' => 'http://www.getexchangerates.com/api/convert/'
				));

				$uri = $amount . '/idr/'. strtolower( $woocommerce_currency ) ;

				$res = $req->get($uri, array());

				return $res->response;
			} catch (Exception $e) {
				return $amount;
			}
		}

		return false;
	}

	public function check_valid_apikey( $api_key ) {
		$result = self::$request->get('/city', array(
			'key' => $api_key,
			'province' =>15
		));

		try {
			if( isset($result->rajaongkir) && $result) {
				if($result->rajaongkir->status->code == 200){
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get list of city based on default country setting.
	 *
	 * @access public
	 * @param integer $woocommerce_default_country
	 * @return array
	 */
	public function get_cities( $woocommerce_default_country ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		if( empty( $this->api_key ) ) {
			return '-';
		} else {
			if( false !== get_transient('cl-ongkir-get_cities'.$woocommerce_default_country) && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
			get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
				return get_transient('cl-ongkir-get_cities'.$woocommerce_default_country);
			} else {
				$result = self::$request->get('/city', array(
					'key' => $this->api_key,
					'province' => $this->convert_to_province_id( $woocommerce_default_country )
				));
				
				try {
					if( isset($result->rajaongkir) && $result != null ) {
						if($result->rajaongkir->status->code != 400){
							$cities = object_to_array( $result->rajaongkir->results );
							$simple_cities = array();
							if($cities){
								foreach ($cities as $city) {
									$simple_cities[$city['city_id']] = $city['type'] . ' ' . $city['city_name'];
								}
							}
							set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-get_cities'.$woocommerce_default_country, $simple_cities, 12 * HOUR_IN_SECONDS);
						} else {
							$simple_cities = '-';
						}
						return $simple_cities;
					} else {
						return '-';
					}

				} catch ( Exception $e ) {
					var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
					return false;
				}
			}
		}
	}

	/**
	 * Get city detail based on province
	 *
	 * @param int $rajaongkir_city_id
	 * @param int $rajaongkir_province_id
	 * @return string
	 */
	public function get_city( $rajaongkir_city_id, $rajaongkir_province_id ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		if( false !== get_transient('cl-ongkir-get_city'.$rajaongkir_city_id.'-'.$rajaongkir_province_id) && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
		get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
			return get_transient('cl-ongkir-get_city'.$rajaongkir_city_id.'-'.$rajaongkir_province_id);
		} else {
			$result = self::$request->get('/city', array(
				'key'      => $this->api_key,
				'province' => $rajaongkir_province_id,
				'id'       => $rajaongkir_city_id
			));

			try {
				if( $result) {
					if( $result->rajaongkir->status->code == 200 ) {
					$city = object_to_array( $result->rajaongkir->results );
						if( $city ) {
							$city = $city['city_name'];
							set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-get_city'.$rajaongkir_city_id.'-'.$rajaongkir_province_id, $city, 12 * HOUR_IN_SECONDS);
						} else {
							$city = '-';
						}
					} else {
						$city = '-';
					}

					return $city;
				} else {
					return '-';
				}

			} catch ( Exception $e ) {
				var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
				return false;
			}
		}
	}

	/**
	 * Get province lists.
	 *
	 * @return array
	 */
	public function get_provinces() {
		// NOTE: check if province id have prefix ID:xx for the first install
		if( false !== get_transient('cl-ongkir-get_provinces') && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
		get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
				return get_transient('cl-ongkir-get_city'.$rajaongkir_city_id.'-'.$rajaongkir_province_id);
		} else {
			$result = self::$request->get('/province', array(
				'key' => $this->api_key,
			));
			try {
				if( $result) {
					if( $result->rajaongkir->status->code != 400 ) {
						$provinces = object_to_array( $result->rajaongkir->results );

						$simple_provice = array();

						foreach ($provinces as $province) {
							$simple_provice[$province['province_id']] = $province['province'];
						}
						set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
						set_transient('cl-ongkir-get_provinces', $simple_provice, 12 * HOUR_IN_SECONDS);
					} else {
						$simple_provice = '-';
					}

					return $simple_provice;
				} else {
					return '-';
				}

			} catch ( Exception $e ) {
				var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
				return false;
			}
		}
	}

	/**
	 * Get subdistrict lists. [PRO VERSION]
	 *
	 * @return array
	 */
	public function get_subdistricts( $rajaongkir_sub_id, $rajaongkir_city_id ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		if( false !== get_transient('cl-ongkir-get_subdistricts'.$rajaongkir_sub_id.'-'.$rajaongkir_city_id) && false !== get_transient('cl-ongkir-server-url') && false !== get_transient('cl-ongkir-apikey') &&
		get_transient('cl-ongkir-server-url') == $this->server && get_transient('cl-ongkir-apikey') == $this->api_key ){
			return get_transient('cl-ongkir-get_subdistricts'.$rajaongkir_sub_id.'-'.$rajaongkir_city_id);
		} else {
			$result = self::$request->get('/subdistrict', array(
				'key' => $this->api_key,
				'city' => $rajaongkir_city_id,
				'id'       => $rajaongkir_sub_id
			));

			try {
				if( $result) {
					if($result->rajaongkir->status->code != 400){
						if( get_option('woocommerce_type_account') == 'pro' ){
							$subdistricts = object_to_array( $result->rajaongkir->results );

							$simple_subdistrict = array();

							foreach ($subdistricts as $subdistrict) {
								if( isset($subdistrict['subdistrict_id']) && isset($subdistrict['subdistrict_name']) )
									$simple_subdistrict[$subdistrict['subdistrict_id']] = $subdistrict['subdistrict_name'];
							}
							set_transient('cl-ongkir-server-url', $this->server, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-apikey', $this->api_key, 12 * HOUR_IN_SECONDS);
							set_transient('cl-ongkir-get_subdistricts'.$rajaongkir_sub_id.'-'.$rajaongkir_city_id, $simple_subdistrict, 12 * HOUR_IN_SECONDS);
						}

					} else {
						$simple_subdistrict = '-';
					}

					return $simple_subdistrict;
				} else {
					return '-';
				}

			} catch ( Exception $e ) {
				var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
				return false;
			}
		}
	}

	public function get_sub( $woocommerce_city_id ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		$result = self::$request->get('/subdistrict', array(
			'key' => $this->api_key,
			'city' => $woocommerce_city_id
		));

		try {
			if( $result) {
				if( $result->rajaongkir->status->code == 200 ) {
					$subdistricts = object_to_array( $result->rajaongkir->results );
					$simple_subdistricts = array();

					foreach ($subdistricts as $sub) {
						$simple_subdistricts[$sub['subdistrict_id']] = $sub['subdistrict_name'];
					}
				// echo '<pre>sc';
				// print_r( $simple_subdistricts );
				} else {
					$simple_subdistricts = '-';
				}

				return $simple_subdistricts;
			} else {
				return '-';
			}

		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Convert woocommerce base_state to rajaongkir province_id
	 *
	 * @param  string $woocommerce_default_country
	 * @return integer
	 */
	public function convert_to_province_id( $woocommerce_base_state ) {
		$provinces = array(
			'AC' => 21,
			'SU' => 34,
			'SB' => 32,
			'RI' => 26,
			'KR' => 17,
			'JA' => 8,
			'SS' => 33,
			'BB' => 2,
			'BE' => 4,
			'LA' => 18,
			'JK' => 6,
			'JB' => 9,
			'BT' => 3,
			'JT' => 10,
			'JI' => 11,
			'YO' => 5,
			'BA' => 1,
			'NB' => 22,
			'NT' => 23,
			'KB' => 12,
			'KT' => 14,
			'KI' => 15,
			'KS' => 13,
			'KU' => 16,
			'SA' => 31,
			'ST' => 29,
			'SG' => 30,
			'SR' => 27,
			'SN' => 28,
			'GO' => 7,
			'MA' => 19,
			'MU' => 20,
			'PA' => 24,
			'PB' => 25
		);

		if( array_key_exists( $woocommerce_base_state, $provinces ) ) {
			return $provinces[$woocommerce_base_state];
		}
	}

	public function cl_check_connection($url){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		));

		$response = curl_exec($curl);
		$res = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ( $http_code == 0 ) {
			return 3; // code 3 for RTO
		}
		else if ( $http_code != 200 && !empty($res) ){
			return $http_code;
		}
		else {
			return 0;
		}
	}

	public function cl_ongkir_tracking( $courier = 'jne', $resi ) {
		$result = self::$request->post('/waybill', array(
			'key' => $this->api_key,
			'courier' => $courier,
			'waybill' => $resi,
		));
		
		try {
			if( $result) {
				if( $result->rajaongkir->status->code == 200 ) {
					$res = array();
					$datas = object_to_array( $result->rajaongkir->result );
					
					$res = $datas;
				} else {
					$res = 'error';
				}

				return $res;
			} else {
				return 'error';
			}

		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}
}