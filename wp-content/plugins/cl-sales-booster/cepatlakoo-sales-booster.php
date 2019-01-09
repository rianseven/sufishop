<?php
/*************************
	* Plugin Name: Cepatlakoo - Sales Booster for WooCommerce
	* Plugin URI: https://cepatlakoo.com
	* Description: This is an extension for WooCommerce plugin, It will promote your recent sales and notify visitors about it. Forked from the original Woo Sales Notify plugin from alisaleem252
	* Version: 1.2.2
	* Author: Cepatlakoo
	* Author URI: https://cepatlakoo.com
	* Text Domain: cl-sales-booster
	* Domain Path: /languages
*
 _ |. _ _ | _  _  _ _ '~)L~'~)
(_|||_\(_||(/_(/_| | | /__) /_

*************************/


if ( ! defined( 'ABSPATH' ) ) exit;
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	define('WOOBOASTERPATH', dirname(__FILE__) );
	require_once( WOOBOASTERPATH.'/inc/filejson.php' );
	require_once( WOOBOASTERPATH.'/init.php' );
		function cl_salesbooster_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=cl_sales_booster">'. esc_html__( 'Settings', 'cl-sales-booster' ) .'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		$plugin = plugin_basename(__FILE__);
		add_filter( 'plugin_action_links_$plugin', 'cl_salesbooster_settings_link' );

		/*Hook Some Action during Activation */
		register_activation_hook( __FILE__, 'cl_salesbooster_activation' );  //activation hook for Schedule hook

	    function cl_salesboosterer_load_plugin_textdomain() {
	    	$domain = 'cl-sales-booster';
	    	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	    	// wp-content/languages/plugin-name/plugin-name-de_DE.mo
	    	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    	// wp-content/plugins/plugin-name/languages/plugin-name-de_DE.mo
	    	load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	    }
		add_action( 'init', 'cl_salesboosterer_load_plugin_textdomain' );

    	function cl_salesbooster_activation() {
			// wp_schedule_event( time(), $schedule, 'prefix_'.$schedule.'_event_hook' );
			cl_create_salesbooster();
			add_option( 'cl_enable_salesbooster', 'yes', '', 'yes' );
			add_option( 'cl_enable_salesbooster_marketplace', 'yes', '', 'yes' );
		} //close function

		add_filter( 'cron_schedules', 'cl_salesbooster_check_interval' );
		function cl_salesbooster_check_interval( $schedules ) {
			$schedules['cl_3_hours'] = array(
					'interval'  => intval(3*60*60),
					'display'   => __( 'Every 3 Hours', 'cl_sales_booster' )
			);
			$schedules['cl_6_hours'] = array(
					'interval'  => intval(6*60*60),
					'display'   => __( 'Every 6 Hours', 'cl_sales_booster' )
			);
			$schedules['cl_weekly'] = array(
					'interval'  => intval(7*24*60*60),
					'display'   => __( 'Weekly', 'cl_sales_booster' )
			);
			return $schedules;
		}

		if ( ! wp_next_scheduled( 'cl_salesbooster_cron' ) ) {
			$opt		= get_option('cl_salesbooster_setting');
			$interval	= 'daily';
			if( isset($opt['auto-update']) && !empty($opt['auto-update']) ){
				$interval = $opt['auto-update'];
			}
			// var_dump( $interval );
			wp_schedule_event( time(), $interval, 'cl_salesbooster_cron' );
		}
		else{
			// var_dump(time());
			// var_dump(wp_next_scheduled( 'cl_salesbooster_cron' ));
			// var_dump(wp_next_scheduled( 'cl_salesbooster_cron' ) - time());
		}

		add_action( 'cl_salesbooster_cron', 'cl_salesbooster_do_cron' );
		function cl_salesbooster_do_cron() {
			cl_create_salesbooster();
		}

	} else {
		function cl_salesbooster_admin_notice() {
	    ?>
	    <div class="error">
	        <p><?php esc_html_e( 'Cepatlakoo Sales Booster is an Extension to WooCommerce. Please activate WooCommerce first!', 'cl-sales-booster' ); ?></p>
	    </div>
	    <?php
	}
	add_action( 'admin_notices', 'cl_salesbooster_admin_notice' );
}
?>