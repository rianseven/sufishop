<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/***************
*
* Initiliaze Our Plugin Here

 _ |. _ _ | _  _  _ _ '~)L~'~)
(_|||_\(_||(/_(/_| | | /__) /_

****************/

/* Add Menu and Stuff */
add_action( 'admin_menu','cl_salesbooster_options' );
function cl_salesbooster_options() {
	add_submenu_page( 'woocommerce', 'CL Sales Booster Options', 'CL Sales Booster', 'manage_options', 'cl_sales_booster', 'cl_sales_booster_manage_options' );
} //close function


/**
 * Add new setting group
 */
add_action( 'admin_init', 'cl_salesbooster_init' );
function cl_salesbooster_init() {
	// register_setting( 'cl_salesbooster', 'cl_salesbooster_setting' );
	add_option( 'cl_salesbooster_setting', array() );
	@$check = file_get_contents($filename);
	if($check == false){
		cl_create_salesbooster();
	}
}

/**
 * Validation
 */

function cl_salesbooster_input_validation( $input ) {
	return $input;
}

function cl_sales_booster_options_success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e( 'Setting Saved.', 'cl-sales-booster' ) ?></p>
    </div>
    <?php
}
// add_action( 'admin_notices', 'cl_sales_booster_options_success' );

function cl_sales_booster_manage_options() {
	if ( isset($_POST['wooboaster_submit'] ) ) {
		$enable = isset( $_POST['cl_enable_salesbooster'] ) ? ( $_POST['cl_enable_salesbooster'] == 'yes' ? 'yes' : '' ) : '';
		if ( isset( $_POST['cl_enable_salesbooster'] ) ) {
			update_option( 'cl_enable_salesbooster', $enable );
		}

		$enable_marketplace = isset( $_POST['cl_enable_marketplace'] ) ? ( $_POST['cl_enable_marketplace'] == 'yes' ? 'yes' : '' ) : '';
		if ( isset( $_POST['cl_enable_marketplace'] ) ) {
			update_option( 'cl_enable_salesbooster_marketplace', $enable_marketplace );
		}
		$old_opt = get_option('cl_salesbooster_setting');
		// Or generate the Original Json File

		$option_array = array();
		$cl_marketplace = array();
		$option_array['display-name'] = '0';
		$option_array['sales-limit'] = 15;
		$option_array['sales-min-delay'] = 5;
		$option_array['sales-max-delay'] = 10;
		$option_array['auto-update'] = 'daily';
		$option_array['display-random'] = '0';
		$option_array['purchase-date'] = '1';
		$option_array['product-name-limit'] = 'unlimited';
		// var_dump($old_opt);
		// var_dump($_POST); exit();

		if ( isset( $_POST['cl_salesbooster_setting']['display-name'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
			$option_array['display-name'] = '1';
		} else {
			if ( isset( $_POST['cl_enable_salesbooster'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
				$option_array['display-name'] = 0;
			} elseif ( isset( $old_opt['display-name'] ) ) {
				$option_array['display-name'] = $old_opt['display-name'];
			}
		}

		if ( isset( $_POST['cl_salesbooster_setting']['sales-limit'] ) ) {
        	$option_array['sales-limit'] = $_POST['cl_salesbooster_setting']['sales-limit'];
		} else {
			if ( isset( $old_opt['sales-limit'] ) )
				$option_array['sales-limit'] = $old_opt['sales-limit'];
		}

		if ( isset( $_POST['cl_salesbooster_setting']['sales-min-delay'] ) ) {
        	$option_array['sales-min-delay'] = $_POST['cl_salesbooster_setting']['sales-min-delay'];
		} else {
			if ( isset( $old_opt['sales-min-delay'] ) )
				$option_array['sales-min-delay'] = $old_opt['sales-min-delay'];
		}

		if ( isset( $_POST['cl_salesbooster_setting']['sales-max-delay'] ) ) {
        	$option_array['sales-max-delay'] = $_POST['cl_salesbooster_setting']['sales-max-delay'];
		} else {
			if ( isset( $old_opt['sales-max-delay'] ) )
				$option_array['sales-max-delay'] = $old_opt['sales-max-delay'];
		}

		if ( isset( $_POST['cl_salesbooster_setting']['auto-update'] ) ) {
			$option_array['auto-update'] = $_POST['cl_salesbooster_setting']['auto-update'];
			if ( $time = wp_next_scheduled( 'cl_salesbooster_cron' ) )
				wp_unschedule_event( $time, 'cl_salesbooster_cron' );

			wp_schedule_event( time(), $_POST['cl_salesbooster_setting']['auto-update'], 'cl_salesbooster_cron' );
		} else {
			if ( isset( $old_opt['auto-update'] ) )
				$option_array['auto-update'] = $old_opt['auto-update'];
		}

		if ( isset( $_POST['cl_salesbooster_setting']['display-random'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
        	$option_array['display-random'] = $_POST['cl_salesbooster_setting']['display-random'];
		} else {
			if ( isset( $_POST['cl_enable_salesbooster'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
				$option_array['display-random'] = 0;
			} elseif ( isset( $old_opt['display-random'] ) ) {
				$option_array['display-random'] = $old_opt['display-random'];
			}
		}
		
		if ( isset( $_POST['cl_salesbooster_setting']['purchase-date'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
        	$option_array['purchase-date'] = $_POST['cl_salesbooster_setting']['purchase-date'];
		} else {
			if ( isset( $_POST['cl_enable_salesbooster'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
				$option_array['purchase-date'] = 0;
			} elseif ( isset( $old_opt['purchase-date'] ) ) {
				$option_array['purchase-date'] = $old_opt['purchase-date'];
			}
		}
		
		if ( isset( $_POST['cl_salesbooster_setting']['sales-same-product'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
        	$option_array['sales-same-product'] = $_POST['cl_salesbooster_setting']['sales-same-product'];
		} else {
			if ( isset( $_POST['cl_enable_salesbooster'] ) && $_POST['cl_enable_salesbooster'] == 'yes' ) {
				$option_array['sales-same-product'] = 0;
			} elseif ( isset( $old_opt['sales-same-product'] ) ) {
				$option_array['sales-same-product'] = $old_opt['sales-same-product'];
			}
		}
		
		if ( isset( $_POST['cl_salesbooster_setting']['product-name-limit'] ) ) {
        	$option_array['product-name-limit'] = $_POST['cl_salesbooster_setting']['product-name-limit'];
		} else {
			if ( isset( $old_opt['product-name-limit'] ) )
				$option_array['product-name-limit'] = $old_opt['product-name-limit'];
		}
		
		if ( isset( $_POST['cl_salesbooster_setting']['cl-marketplace-info'] ) ) {
        	$option_array['cl-marketplace-info'] = $_POST['cl_salesbooster_setting']['cl-marketplace-info'];
		} else {
			if ( isset( $old_opt['cl-marketplace-info'] ) )
				$option_array['cl-marketplace-info'] = $old_opt['cl-marketplace-info'];
		}
		
		if ( isset( $_POST['cl_salesbooster_setting']['cl-marketplace-fb-pixel'] ) ) {
			$option_array['cl-marketplace-fb-pixel'] = $_POST['cl_salesbooster_setting']['cl-marketplace-fb-pixel'];
			if ( $_POST['cl_salesbooster_setting']['cl-marketplace-fb-pixel'] == 'Custom'){
				$option_array['cl-marketplace-fb-pixel-custom'] = $_POST['cl_salesbooster_setting']['cl-marketplace-fb-pixel-custom'];
			} else {
				$option_array['cl-marketplace-fb-pixel-custom'] = '';
			}
		} else {
			if ( isset( $old_opt['cl-marketplace-fb-pixel'] ) )
				$option_array['cl-marketplace-fb-pixel'] = $old_opt['cl-marketplace-fb-pixel'];
			
			if ( isset( $old_opt['cl-marketplace-fb-pixel-custom'] ) )
				$option_array['cl-marketplace-fb-pixel-custom'] = $old_opt['cl-marketplace-fb-pixel-custom'];
		}

		if ( isset( $_POST['cl_salesbooster_setting']['cl-marketplace-item-name'] ) ) {
			foreach ( $_POST['cl_salesbooster_setting']['cl-marketplace-item-name'] as $ind => $name ) {
				if ( !empty($name) ) {
					$id = preg_replace('/\s*/', '', $name);
					$id = strtolower($id);

					$cl_marketplace[$id] = array(
						'id'	=> $id,
						'logo'	=> $_POST['cl_salesbooster_setting']['cl-marketplace-item-logo'][$ind],
						'name'	=> $name,
						// 'color'	=> $_POST['cl_salesbooster_setting']['cl-marketplace-item-color'][$ind]
					);
				}
			}

			$option_array['cl-marketplace'] = $cl_marketplace;
		} else {
			if ( isset( $old_opt['cl-marketplace'] ) )
				$option_array['cl-marketplace'] = $old_opt['cl-marketplace'];
		}
      	
		update_option('cl_salesbooster_setting', $option_array);
		cl_sales_booster_options_success();
		cl_create_salesbooster();
	}
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'booster';
	?>

	<div id="wrap">
		<h2><?php esc_html_e( 'Cepatlakoo Sales Booster for WooCommerce', 'cl-sales-booster' ) ?></h2>
		
		<h2 class="nav-tab-wrapper">
			<a href="?page=cl_sales_booster&tab=booster" class="nav-tab <?php echo $active_tab == 'booster' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Sales Booster', 'cl-sales-booster' ) ?></a>
			<a href="?page=cl_sales_booster&tab=marketplace" class="nav-tab <?php echo $active_tab == 'marketplace' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Marketplace', 'cl-sales-booster' ) ?></a>
		</h2>

		<form method="post" action="">
			<?php
			settings_fields( 'cl_salesbooster' );
			global $cl_options;
			$options = get_option('cl_salesbooster_setting');

			if ( !isset( $options['cl-marketplace'] ) ) {
				$options['cl-marketplace'] = array(
					array(
						'logo'	=> plugin_dir_url( __FILE__ ) . 'images/tokopedia.png',
						'name'	=> 'Tokopedia',
						// 'color'	=> '#f4f4f4'
					),
					array(
						'logo'	=> plugin_dir_url( __FILE__ ) . 'images/bukalapak.png',
						'name'	=> 'Bukalapak',
						// 'color'	=> '#c30f42'
					),
					array(
						'logo'	=> plugin_dir_url( __FILE__ ) . 'images/lazada.png',
						'name'	=> 'Lazada',
						// 'color'	=> '#003e52'
					),
					array(
						'logo'	=> plugin_dir_url( __FILE__ ) . 'images/shopee.png',
						'name'	=> 'Shopee',
						// 'color'	=> '#ff5722'
					),
					array(
						'logo'	=> plugin_dir_url( __FILE__ ) . 'images/blibli.png',
						'name'	=> 'Blibli',
						// 'color'	=> '#0096d9'
					)
				);
			}
			?>

			<table class="form-table">
			<?php do_settings_sections( 'cl_booster' ); ?>
			
			<?php if ( $active_tab == 'booster' ) : ?> 
				<tr>
                	<th scope="row"><label for="blogname"><?php esc_html_e( 'Enable Sales Booster', 'cl-sales-booster' ) ?></label>
	                </th>
	                <td>
	                	<input type="radio" name="cl_enable_salesbooster" id="salesbooster_yes" value="yes" <?php $option = get_option( 'cl_enable_salesbooster' ); echo ( $option == 'yes' ? 'checked' : '' );?>/>
	                	<label for="salesbooster_yes"><?php esc_html_e( 'Yes','cl-sales-booster' ) ?></label>
	                    <input type="radio" name="cl_enable_salesbooster" id="salesbooster_no" value="no" <?php $option = get_option( 'cl_enable_salesbooster' ); echo ( $option == '' ? 'checked' : '' );?>/>
	                    <label for="salesbooster_no"><?php esc_html_e( 'No', 'cl-sales-booster' ) ?></label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Display buyer\'s name?', 'cl-sales-booster' ); ?></th>
					<td>
						<label for="display-name">
							<input type="checkbox" id="display-name" name="cl_salesbooster_setting[display-name]" value="1" <?php checked( '1', @$options['display-name'] ); ?> />
							<?php esc_html_e( 'Yes', 'cl-sales-booster' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales-limit"><?php esc_html_e( 'Number of sales displayed:', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<?php
						$limit = 15;
						if ( isset( $options['sales-limit'] ) ) {
							$limit = $options['sales-limit'];
						}
						?>
						<input type="number" id="sales-limit" name="cl_salesbooster_setting[sales-limit]" value="<?php echo esc_attr( $limit ); ?>" step="1" min="1" style="width:80px;" />
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales-min-delay"><?php esc_html_e( 'Set minimal delay', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<?php
						$min_delay = 5;
						if ( isset( $options['sales-min-delay'] ) ) {
							$min_delay = $options['sales-min-delay'];
						}
						?>
						<input type="number" id="sales-min-delay" name="cl_salesbooster_setting[sales-min-delay]" value="<?php echo esc_attr( $min_delay ); ?>" step="1" min="3" max="10" style="width:80px;" />
						<?php esc_html_e( 'seconds', 'cl-sales-booster' ); ?>
						<p class="description"><?php esc_html_e( 'Minimum delay time between each notification.', 'cl-sales-booster' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales-max-delay"><?php esc_html_e( 'Set maxmimum delay', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<?php
						$max_delay = 10;
						if ( isset( $options['sales-max-delay'] ) ) {
							$max_delay = $options['sales-max-delay'];
						}
						?>
						<input type="number" id="sales-max-delay" name="cl_salesbooster_setting[sales-max-delay]" value="<?php echo esc_attr( $max_delay ); ?>" step="1" min="4" max="20" style="width:80px;" />
						<?php esc_html_e( 'seconds', 'cl-sales-booster' ); ?>
						<span class='cl-sales-delay-error' style="display:none; color:red"> <br><?php esc_html_e( 'Max value must greater than min value', 'cl-sales-booster' ); ?> </span>
						<p class="description"><?php esc_html_e( 'Maximum delay time between each notification.', 'cl-sales-booster' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e('Display randomly?', 'cl-sales-booster'); ?></th>
					<td>
						<label for="display-random">
							<input type="checkbox" id="display-random" name="cl_salesbooster_setting[display-random]" value="1" <?php checked( @$options['display-random'], '1' ); ?> />
							<?php esc_html_e( 'Yes', 'cl-sales-booster' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales-min-delay"><?php esc_html_e( 'Product Name Length', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<select name="cl_salesbooster_setting[product-name-limit]">
							<option value="unlimited" <?php echo ( @$options['product-name-limit'] == 'unlimited' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Unlimited', 'cl-sales-booster' ); ?></option>
							<?php for ( $i=1; $i < 11; $i++ ) : ?> 
								<option value="<?php echo $i; ?>" <?php echo ( @$options['product-name-limit'] == $i ) ? 'selected' : ''; ?>><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
						<?php esc_html_e( 'Word(s)', 'cl-sales-booster' ); ?>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Display from same product ?', 'cl-sales-booster' ); ?></th>
					<td>
						<label for="sales-same-product">
							<input type="checkbox" id="sales-same-product" name="cl_salesbooster_setting[sales-same-product]" value="1" <?php checked( @$options['sales-same-product'], '1' ); ?> />
							<?php esc_html_e( 'Yes', 'cl-sales-booster' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'If selected, it will ONLY display sales notification from the same product on the product page. If there\'s no sale yet for that product, it will display sales from other products.', 'cl-sales-booster' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Display purchase date', 'cl-sales-booster' ); ?></th>
					<td>
						<label for="purchase-date">
							<input type="checkbox" id="purchase-date" name="cl_salesbooster_setting[purchase-date]" value="1" <?php checked( @$options['purchase-date'], '1' ); ?> />
							<?php esc_html_e( 'Yes', 'cl-sales-booster' ); ?>
						</label>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="auto-update"><?php esc_html_e( 'Auto Update Order List', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<select name="cl_salesbooster_setting[auto-update]">
							<option value="hourly" <?php echo ( @$options['auto-update'] == 'hourly' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Every 1 Hour', 'cl-sales-booster' ); ?></option>
							<option value="cl_3_hours" <?php echo ( @$options['auto-update'] == 'cl_3_hours' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Every 3 Hours', 'cl-sales-booster' ); ?></option>
							<option value="cl_6_hours" <?php echo ( @$options['auto-update'] == 'cl_6_hours' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Every 6 Hours', 'cl-sales-booster' ); ?></option>
							<option value="twicedaily" <?php echo ( @$options['auto-update'] == 'twicedaily' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Every 12 Hours', 'cl-sales-booster' ); ?></option>
							<option value="daily" <?php echo ( (@$options['auto-update'] == 'daily') || ( !isset($options['auto-update']) ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Daily', 'cl-sales-booster' ); ?></option>
							<option value="cl_weekly" <?php echo ( @$options['auto-update'] == 'cl_weekly' ) ? 'selected' : ''; ?>><?php esc_html_e( 'Weekly', 'cl-sales-booster' ); ?></option>
						</select>
					</td>
				</tr>

	            <tr>
	             	<td></td>
	             	<td><input type="submit" name="wooboaster_submit" class="button-primary cl-sales-submit" value="<?php esc_html_e( 'Save Changes', 'cl-sales-booster' ); ?>" /></td>
	            </tr>
			<?php else : do_settings_sections( 'cl_marketplace' ); ?>
				<tr>
                	<th scope="row"><label for="blogname"><?php esc_html_e( 'Enable Marketplace Link', 'cl-sales-booster' ) ?></label>
	                </th>
	                <td>
	                	<input type="radio" name="cl_enable_marketplace" id="marketplace_yes" value="yes" <?php $option = get_option( 'cl_enable_salesbooster_marketplace' ); echo ( $option == 'yes' ? 'checked' : '' );?>/>
	                	<label for="smarketplace_yes"><?php esc_html_e( 'Yes','cl-sales-booster' ) ?></label>
	                    <input type="radio" name="cl_enable_marketplace" id="smarketplace_no" value="no" <?php $option = get_option( 'cl_enable_salesbooster_marketplace' ); echo ( $option == '' ? 'checked' : '' );?>/>
	                    <label for="marketplace_no"><?php esc_html_e( 'No', 'cl-sales-booster' ) ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Text Information', 'cl-sales-booster' ); ?></th>
					<td>
						<label for="cl-marketplace-info">
							<textarea class="large-text" name="cl_salesbooster_setting[cl-marketplace-info]" rows="3"><?php echo isset( $options['cl-marketplace-info'] ) ? $options['cl-marketplace-info'] : 'Anda juga dapat memesan produk kami pada situs-situs berikut:'; ?></textarea>
						</label>
					</td>
				</tr>
				
				<?php if ( !empty( $cl_options['cepatlakoo_facebook_pixel_id'] ) ? $cl_options['cepatlakoo_facebook_pixel_id'] : '' ) : ?>
					<tr>
						<?php $fb_pix = isset( $options['cl-marketplace-fb-pixel'] ) ? $options['cl-marketplace-fb-pixel'] : '';
						$fb_pix_custom = isset( $options['cl-marketplace-fb-pixel-custom'] ) ? $options['cl-marketplace-fb-pixel-custom'] : ''; ?>
						<th scope="row"><?php esc_html_e('Facebook Pixel Event', 'cl-sales-booster'); ?></th>
						<td>
							<label for="cl-marketplace-fb-pixel">
							<select name="cl_salesbooster_setting[cl-marketplace-fb-pixel]" class="cl_marketplace_pixel_events">
								<option value="ViewContent" <?php echo ($fb_pix == 'ViewContent')? 'selected="selected"' : ''; ?>>ViewContent</option>
								<option value="Lead" <?php echo ($fb_pix == 'Lead')? 'selected="selected"' : ''; ?>>Lead</option>
								<option value="AddToWishlist" <?php echo ($fb_pix == 'AddToWishlist')? 'selected="selected"' : ''; ?>>AddToWishlist</option>
								<option value="AddPaymentInfo" <?php echo ($fb_pix == 'AddPaymentInfo')? 'selected="selected"' : ''; ?>>AddPaymentInfo</option>
								<option value="CompleteRegistration" <?php echo ($fb_pix == 'CompleteRegistration')? 'selected="selected"' : ''; ?>>CompleteRegistration</option>
								<option value="AddToCart" <?php echo ($fb_pix == 'AddToCart')? 'selected="selected"' : ''; ?>>AddToCart</option>
								<option value="InitiateCheckout" <?php echo ($fb_pix == 'InitiateCheckout')? 'selected="selected"' : ''; ?>>InitiateCheckout</option>
								<option value="Purchase" <?php echo ($fb_pix == 'Purchase')? 'selected="selected"' : ''; ?>>Purchase</option>
								<option value="Custom" <?php echo ($fb_pix == 'Custom')? 'selected="selected"' : ''; ?>>Custom Event</option>
							</select>

							<input type="text" name="cl_salesbooster_setting[cl-marketplace-fb-pixel-custom]" placeholder="<?php echo esc_html__('Custom Event', 'cl-sales-booster'); ?>" class="cl_marketplace_custom_event" <?php echo ($fb_pix != 'Custom')? 'disabled="disabled"' : ''; ?> value=<?php echo ($fb_pix_custom)? $fb_pix_custom : ''; ?>>
							</label>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<th scope="row"><?php esc_html_e('Listing Marketplace', 'cl-sales-booster'); ?></th>
					<td>
						<table class="wp-list-table widefat fixed striped posts">
							<thead>
								<tr>
									<td><?php esc_html_e( 'Sort', 'cl-sales-booster' ); ?></td>
									<td><?php esc_html_e( 'Logo', 'cl-sales-booster' ); ?></td>
									<td><?php esc_html_e( 'Name', 'cl-sales-booster' ); ?></td>
									<td><?php esc_html_e( 'Action', 'cl-sales-booster' ); ?></td>
								</tr>
							</thead>
							<tbody class="cl_marketplace_parent_button">
								<?php if( isset($options['cl-marketplace']) && count($options['cl-marketplace']) > 0 ) :
									foreach( $options['cl-marketplace'] as $market) : ?>
									<tr class="cl_marketplace_item">
										<td><span class="dashicons dashicons-move" title="Drag  to reorder"></span></td>
										<td>
											<input class="cl_marketplace_url" type="text" style="display:none" name="cl_salesbooster_setting[cl-marketplace-item-logo][]" value="<?php echo $market['logo']; ?>" />
											<img src="<?php echo $market['logo']; ?>" class="cl_marketplace_image" style="width:100px"> <br />
											<input class="cl_marketplace_button button-secondary" type="button" value="<?php esc_html_e( 'Change Logo', 'cl-sales-booster' ); ?>" />
										</td>
										<td>
											<input type="text" name="cl_salesbooster_setting[cl-marketplace-item-name][]" placeholder="<?php esc_html_e('Name', 'cl-sales-booster'); ?>" value="<?php echo $market['name']; ?>"/>
										</td>
										<!-- <td>
											<input type="text" name="cl_salesbooster_setting[cl-marketplace-item-color][]" placeholder="<?php esc_html_e('Color', 'cl-sales-booster'); ?>" class="color-field" value="<?php //echo $market['color']; ?>"/>
										</td> -->
										<td>
											<button class="cl_marketplace_setting_plus">+</button>
											<button class="cl_marketplace_setting_min">-</button>
										</td>
									</tr>
								<?php endforeach; endif; ?>
								<tr class="cl_marketplace_item">
									<td><span class="dashicons dashicons-move" title="Drag to reorder"></span></td>
									<td>
										<input class="cl_marketplace_url" type="text" style="display:none" name="cl_salesbooster_setting[cl-marketplace-item-logo][]" value="" />
										<img src="" class="cl_marketplace_image" style="max-width: 100px; height: auto;">
										<input class="cl_marketplace_button button-secondary" type="button" value="<?php esc_html_e( 'Upload Logo', 'cl-sales-booster' ); ?>" />
									</td>
									<td>
										<input type="text" name="cl_salesbooster_setting[cl-marketplace-item-name][]" placeholder="<?php esc_html_e('Name', 'cl-sales-booster'); ?>"/>
									</td>
									<!-- <td>
										<input type="text" name="cl_salesbooster_setting[cl-marketplace-item-color][]" placeholder="<?php esc_html_e('Color', 'cl-sales-booster'); ?>" class="color-field"/>
									</td> -->
									<td>
										<button class="cl_marketplace_setting_plus">+</button>
										<button class="cl_marketplace_setting_min">-</button>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
	            <tr>
	             	<td></td>
	             	<td><input type="submit" name="wooboaster_submit" class="button-primary cl-sales-submit" value="<?php esc_html_e( 'Save Changes', 'cl-sales-booster' ); ?>" /></td>
	            </tr>
			<?php endif; ?>
				
        	</table>
    	</form>
	</div>
	<script>
		jQuery('#sales-max-delay, #sales-min-delay').change(function(){
			if ( parseInt(jQuery('#sales-min-delay').val()) >= parseInt(jQuery('#sales-max-delay').val()) ){
				jQuery('.cl-sales-delay-error').show();
				jQuery('.cl-sales-submit').prop('disabled', true);
			}
			else{
				jQuery('.cl-sales-submit').prop('disabled', false);
				jQuery('.cl-sales-delay-error').hide();
			}
		});

		jQuery(document).on('change', '.cl_marketplace_pixel_events', function(e){
			e.preventDefault();
			val = jQuery(this).val();
			
			if (val == 'Custom'){
				jQuery( '.cl_marketplace_custom_event' ).prop("disabled", false);
				jQuery( '.cl_marketplace_custom_event' ).show();
			}
			else{
				jQuery( '.cl_marketplace_custom_event' ).prop("disabled", true);
				jQuery( '.cl_marketplace_custom_event' ).hide();
			}
		});
	</script>
<?php
}

function cl_salesbooster_async_scripts( $url ) {
    if ( strpos( $url, '#asyncload') === false ) {
        return $url;
    } else if ( is_admin() ) {
        return str_replace( '#asyncload', '', $url );
    } else {
    	return str_replace( '#asyncload', '', $url )."' async=async";
    }
}
add_filter( 'clean_url', 'cl_salesbooster_async_scripts', 11, 1 );

function cl_salesbooster_theme_scripts() {
	global $product;

    $showNotify = get_post_meta( get_the_ID(), 'notify_meta_checkbox' ) ? get_post_meta( get_the_ID(), 'notify_meta_checkbox' )[0] : '';
	wp_enqueue_style( 'cl-sales-booster-css', plugin_dir_url( __FILE__ ) . 'css/cl-sales-booster.css' );

    if ( is_woocommerce() || $showNotify == 'on' ) {
		$notify_enabled = get_option( 'cl_enable_salesbooster' );
		
		if ( $notify_enabled != 'yes' ) {
			return;
		}

		$cs_options = get_option( 'cl_salesbooster_setting' );
		$display_name = @$cs_options['display-name'];
		$show_date = @$cs_options['purchase-date'];
		$min_delay = @$cs_options['sales-min-delay'];
		$max_delay = @$cs_options['sales-max-delay'];
		$display_random = @$cs_options['display-random'];

		// $booster = 'booster.dev.js';
		$booster = 'booster.min.js';

		// if ( $display_random == '1' ) {
		// 	$booster = 'booster-random.dev.js';
		// }

		if( isset($product) || is_page( get_the_ID() ) ) {
			$product_id = get_the_ID();
			$page_type = ( is_page( get_the_ID()) ) ? 'page' : 'product';
		}
		else{
			$product_id = '';
			$page_type = '';
		}

		// wp_enqueue_script( 'cepatlakoo_sales', plugin_dir_url( __FILE__ ) . 'js/booster.min.js#asyncload', 'jquery', '', true );
		wp_enqueue_script( 'cepatlakoo_sales', plugin_dir_url( __FILE__ ) . 'js/'.$booster.'#asyncload', 'jquery', '', true );
      	wp_localize_script( 'cepatlakoo_sales', 'cl_sales', array(
            'folder'		=> plugins_url( '/json/',__FILE__),
            'admin_url'		=> admin_url('admin-ajax.php'),
            'product_id'	=> $product_id,
            'page_type'		=> $page_type,
            'translation'	=> array(
                                esc_html__('Someone in', 'cl-sales-booster'),
                                esc_html__('purchase a', 'cl-sales-booster'),
                                esc_html__('ago', 'cl-sales-booster'),
                                esc_html__('from now', 'cl-sales-booster'),
                                esc_html__('less than a minute', 'cl-sales-booster'),
                                esc_html__('about a minute', 'cl-sales-booster'),
                                esc_html__('minutes', 'cl-sales-booster'),
                                esc_html__('about an hour', 'cl-sales-booster'),
                                esc_html__('about', 'cl-sales-booster'),
                                esc_html__('hours', 'cl-sales-booster'),
                                esc_html__('a day', 'cl-sales-booster'),
                                esc_html__('days', 'cl-sales-booster'),
                                esc_html__('about a month', 'cl-sales-booster'),
                                esc_html__('months', 'cl-sales-booster'),
                                esc_html__('about a year', 'cl-sales-booster'),
                                esc_html__('years', 'cl-sales-booster'),
								esc_html__('in', 'cl-sales-booster'),
                              ),
			'display_name'		=> $display_name,
			'display_random'	=> $display_random,
			'show_date'			=> $show_date,
			'min_delay'			=> $min_delay,
			'max_delay'			=> $max_delay,
      	));
    };
}
add_action( 'wp_enqueue_scripts', 'cl_salesbooster_theme_scripts');

function cl_salesbooster_admin_script() {
	wp_enqueue_media();
	wp_register_script( 'cl-salesbooster-functions-admin', plugin_dir_url( __FILE__ ) . 'js/cl-function-admin.js', array('jquery','media-upload','thickbox', 'jquery-ui-sortable', 'wp-color-picker'), '1.0.0', true );
	if( get_current_screen()->id == 'woocommerce_page_cl_sales_booster'){
		wp_enqueue_script('jquery');
 
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
 
        wp_enqueue_script('media-upload');
		wp_enqueue_script('cl-salesbooster-functions-admin');
	}
}
add_action( 'admin_enqueue_scripts', 'cl_salesbooster_admin_script' );

function cl_salesbooster_custom_meta() {
    add_meta_box( 'sales_notify', esc_html__( 'Cepatlakoo Sales Booster', 'cl-sales-booster' ), 'cl_salesbooster_meta_callback', 'page' );
}
add_action( 'add_meta_boxes', 'cl_salesbooster_custom_meta' );

function cl_salesbooster_meta_callback() {
    // $post is already set, and contains an object: the WordPress post
    global $post;

    $check = get_post_meta( $post->ID , 'notify_meta_checkbox' ) ? esc_attr( get_post_meta( $post->ID , 'notify_meta_checkbox' )[0] ) : '';

    // We'll use this nonce field later on when saving.
    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
    ?>
    <p>
        <input type="checkbox" id="notify_meta_checkbox" name="notify_meta_checkbox" <?php checked( $check, 'on' ); ?> />
        <label for="notify_meta_checkbox"><?php esc_html_e( 'Display sales booster notification in this page', 'cl-sales-booster' ) ?></label>
    </p>
	<?php
	$args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1
    );
	$product_loop = new WP_Query( $args );
	$selected = get_post_meta($post->ID, '_cl_salesboster_id', true);
	
	?>
	<div class="cl-cr-admin-post-form">
		<table class="form-table">
			<tbody>
				<tr style="display:none" class="cl_marketplace_custom_id_tr">
					<th scope="row">
						<label for="cl_marketplace_custom_id"><?php echo esc_html__( 'Select Product(s)', 'cl-sales-booster' ); ?></label>
					</th>
					<td>
						<select name="cl_salesbooster_custom_id[]" multiple="multiple" style="max-width: inherit; width:100%;" id="cl_marketplace_custom_id">
							<?php while ( $product_loop->have_posts() ) : $product_loop->the_post(); ?>
								<option value="<?php echo get_the_ID(); ?>" <?php echo ( (is_array($selected) && in_array(get_the_ID(), $selected)) || (get_the_ID() == $selected) ) ? 'selected' : '' ?>><?php echo get_the_title(); ?></option>
							<?php endwhile; ?>
						</select>
						<p class="description"><?php echo esc_html__( 'If any selected, it will display sales notification from those product(s). If there\'s no sale yet for that product, it will display sales from other products. Don\'t select any product if you want to display notification from all products.', 'cl-sales-booster' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<script>
		jQuery('#notify_meta_checkbox').change(function() {
			if( jQuery(this).is(":checked") ) {
				jQuery('.cl_marketplace_custom_id_tr').show();
			}
			else{
				jQuery('.cl_marketplace_custom_id_tr').hide();
			}      
		});
	</script>
    <?php
}

add_action( 'save_post', 'cl_salesbooster_meta_box_save' );
function cl_salesbooster_meta_box_save( $post_id ) {
    // Bail if we're doing an auto save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
    // if our nonce isn't there, or we can't verify it, bail
    if ( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;

    // if our current user can't edit this post, bail
	if ( !current_user_can( 'edit_post' ) ) return;
	
	if ( array_key_exists( 'cl_marketplace_type', $_POST ) ) {
		$value = array();
		foreach ( $_POST['cl_marketplace_type'] as $index => $val ) {
			// var_dump($_POST); exit();
			if ( !empty( $_POST['cl_marketplace_link'][$index] ) && ( $_POST['cl_marketplace_link'][$index] != 'https://' ) ) {
				$value[] = array(
					'type'  => $_POST['cl_marketplace_type'][$index],
					'link'  => $_POST['cl_marketplace_link'][$index],
					'tab'   => $_POST['cl_marketplace_tab'][$index]
				);
			}
		}

		update_post_meta(
			$post_id,
			'_cl_marketplace_link',
			$value
		);
	}
	
	if ( array_key_exists( 'cl_salesbooster_custom_id', $_POST ) ) {
		update_post_meta(
			$post_id,
			'_cl_salesboster_id',
			$_POST['cl_salesbooster_custom_id']
		);
	}
	else{
		update_post_meta(
			$post_id,
			'_cl_salesboster_id',
			'off'
		);
	}

    // This is purely my personal preference for saving check-boxes
    $chk = isset( $_POST['notify_meta_checkbox'] ) ? 'on' : 'off';
    update_post_meta( $post_id, 'notify_meta_checkbox', $chk );
}

// var_dump(dirname(plugin_basename(__FILE__)) . '/languages/'); exit();
load_plugin_textdomain( 'cl-sales-booster', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

add_action('add_meta_boxes', 'cl_init_marketplace');

function cl_init_marketplace(){
	$marketplace_enabled = get_option( 'cl_enable_salesbooster_marketplace' );
	
	if ( $marketplace_enabled != 'yes' ) {
		return;
	}
	else{
		add_meta_box(
			'cl_marketplace_meta_box', // Unique ID
			esc_html__( 'Marketplace Links', 'cl-sales-booster' ), // Box title
			'cl_marketplace_meta_box_html', // Content callback, must be of type callable
			array('product'), // Post type
			'normal',
			'high'
		);
	}
}

function cl_marketplace_meta_box_html( $post ) {
	$options = get_option('cl_salesbooster_setting');

	$links = get_post_meta($post->ID, '_cl_marketplace_link', true);
	$markets = isset($options['cl-marketplace']) ? $options['cl-marketplace'] : '' ;
	?>
	<div class="form-field">
		<label for="cl_marketplace"><?php echo esc_html__('Link to your product in marketplaces.', 'cl-sales-booster'); ?></label>
		<?php if ( count($markets) > 0 ) : ?>
			<ul class="cl_marketplace_parent_button">
			<?php if ( $links ) : foreach ( $links as $link ) : ?>
				<li class="cl_marketplace_item">
					<span class="dashicons dashicons-move" title="Drag to reorder"></span>
					<select name="cl_marketplace_type[]" class="button_type">
						<?php foreach($markets as $market) : ?>
							<option value="<?php echo $market['id'] ?>" <?php echo ( $link['type'] == $market['id'] )? 'selected="selected"' : ''; ?>><?php echo $market['name'] ?></option>
						<?php endforeach; ?>
					</select>
					<input type="text" name="cl_marketplace_link[]" placeholder="<?php echo esc_html__('Link', 'cl-sales-booster' ); ?>" class="cl_marketplace_link" value="<?php echo $link['link']; ?>">
					<input type="hidden" class="cl_marketplace_tab" name="cl_marketplace_tab[]" value="<?php echo $link['tab']; ?>">
					<input type="checkbox" class="cl_marketplace_item_check" <?php echo ( $link['tab'] == 'true' )? 'checked' : ''; ?>> <?php esc_html_e( 'Open in New Tab', 'cl-sales-booster' ); ?>
					<button class="cl_marketplace_plus">+</button>
					<button class="cl_marketplace_min">-</button>
				</li>
				<?php endforeach;
				else : ?>
				<li class="cl_marketplace_item">
					<span class="dashicons dashicons-move" title="Drag to reorder"></span>
					<select name="cl_marketplace_type[]" class="button_type">
						<?php foreach($markets as $market) : ?>
							<option value="<?php echo $market['id'] ?>" ><?php echo $market['name'] ?></option>
						<?php endforeach; ?>
					</select>
					<input type="text" name="cl_marketplace_link[]" placeholder="<?php echo esc_html__('Link', 'cl-sales-booster' ); ?>" class="button_id" value="https://">
					<input type="hidden" class="cl_marketplace_tab" name="cl_marketplace_tab[]" value="false">
					<input type="checkbox" class="cl_marketplace_item_check"> <?php esc_html_e( 'Open in New Tab', 'cl-sales-booster' ); ?>
					<button class="cl_marketplace_plus">+</button>
					<button class="cl_marketplace_min">-</button>
				</li>
			<?php endif; ?>
			</ul>
		<?php else : ?>
			<p><?php echo esc_html__('Please setting listing marketplace first', 'cl-sales-booster' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
}

add_action( 'woocommerce_single_product_summary', 'cl_marketplace_render', 50 );
function cl_marketplace_render( $content ){
	$marketplace_enabled = get_option( 'cl_enable_salesbooster_marketplace' );
	
	if ( $marketplace_enabled != 'yes' ) {
		return;
	}

	global $cl_options;
	$options = get_option('cl_salesbooster_setting');
	$pixel_active = false;
	if( !empty( $cl_options['cepatlakoo_facebook_pixel_id'] ) ? $cl_options['cepatlakoo_facebook_pixel_id'] : '' ){
		if( isset($options['cl-marketplace-fb-pixel']) ){
			$fb_event = ($options['cl-marketplace-fb-pixel'] == 'Custom') ? $options['cl-marketplace-fb-pixel-custom'] : $options['cl-marketplace-fb-pixel'] ;
			$pixel_active = true;
		}
		else{
			$pixel_active = false;
		}
	}
	$market = isset( $options['cl-marketplace'] ) ? $options['cl-marketplace'] : '';
	$links = get_post_meta( get_the_ID(), '_cl_marketplace_link', true ); ?>

	<div class="cl_marketplace">
		<?php if($links) : ?>
			<?php if( isset($options['cl-marketplace-info']) && !empty($options['cl-marketplace-info']) ) : ?>
				<p class="contact-message"><?php echo $options['cl-marketplace-info']; ?></p>
			<?php endif; ?>
			
			<?php foreach($links as $link) : ?>
				<a <?php echo ( $pixel_active ) ? 'fb-pixel="'.$fb_event.'"' : ''; ?> href="<?php echo $link['link']; ?>" class="<?php echo $link['type']; ?> cl-marketplace-button" target="<?php echo ($link['tab'] == 'true') ? '_blank': '_self' ?>"
				<?php //echo ( !empty($market[$link['type']]['color']) ) ? 'style=background:'.$market[$link['type']]['color'] : ''; ?> >
					<img src="<?php echo $market[$link['type']]['logo']; ?>">
					<!-- <span class="text"><?php //echo $market[$link['type']]['name']; ?> </span> -->
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'wp_ajax_cl_booster_datas', 'cl_salesbooster_datas' );
add_action( 'wp_ajax_nopriv_cl_booster_datas', 'cl_salesbooster_datas' );
function cl_salesbooster_datas(){
	$options = get_option('cl_salesbooster_setting');
	$limit = 15;
	
	if( isset($options['sales-limit']) ) {
		$limit = intval($options['sales-limit']);
	}
	
	$id = ( isset($_POST['id']) && !empty($_POST['id']) ) ? $_POST['id'] : '';
	$page_type = ( isset($_POST['page_type']) && !empty($_POST['page_type']) ) ? $_POST['page_type'] : '';

	$filename = WOOBOASTERPATH.'/json/file.json';
	$string = file_get_contents($filename);
	$datas = json_decode($string, true);
	$check = false;
	$ret = '';

	if( !empty($id) ){
		$selected = array();
		
		if ( $page_type == 'page' ){
			$selected = get_post_meta($id, '_cl_salesboster_id', true);
			$check = true;
		}
		else{
			if( isset($options['sales-same-product']) && $options['sales-same-product'] == 1) {
				$limit = intval($options['sales-limit']);
				$check = true;
			}
		}
		
		if ($check){
			$ret_arr = array();
			foreach($datas as $data){
				if( ($data['product_id'] == $id) || ( is_array($selected) && in_array($data['product_id'], $selected) ) ){
					$ret_arr[] = $data;
				}
			}
			if( count($ret_arr) > 0 )
				$datas = $ret_arr;
		}
	}

	if( count($datas) > $limit ){
		$datas = array_slice($datas, 0, $limit);
	}
	$parsePurchases = json_encode( $datas );
	$ret = 'parsePurchases( '.$parsePurchases.' )';

	// var_dump($datas); exit();
	echo $ret; die();
}