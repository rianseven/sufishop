<?php
/**
 * Template for proccessing SMS Notification
 *
 * @package WordPress
 * @subpackage CepatLakoo
 * @since CepatLakoo 1.0.2
 */
include( get_template_directory() . '/includes/smsGateway/smsGateway.php' ); // Load Library

/**
* Functions to send SMS if order status change for customer
*
* @package WordPress
* @subpackage CepatLakoo
* @since CepatLakoo 1.0.2
*/
if ( ! function_exists( 'cepatlakoo_customer_order_notification' ) ) {
    function cepatlakoo_customer_order_notification($order) {
        global $woocommerce, $cl_options;

        $cepatlakoo_sms_gateway_token = !empty( $cl_options['cepatlakoo_sms_gateway_token'] ) ? $cl_options['cepatlakoo_sms_gateway_token'] : '';
        $cl_wa_token                    = !empty( $cl_options['cepatlakoo_wa_token'] ) ? $cl_options['cepatlakoo_wa_token'] : '';
        $cepatlakoo_sms_gateway_deviceID = !empty( $cl_options['cepatlakoo_sms_gateway_deviceID'] ) ? $cl_options['cepatlakoo_sms_gateway_deviceID'] : '';
        $cepatlakoo_sms_gateway_phone = !empty( $cl_options['cepatlakoo_sms_gateway_phone'] ) ? $cl_options['cepatlakoo_sms_gateway_phone'] : '';

        $cepatlakoo_sms_new_order = !empty( $cl_options['cepatlakoo_sms_new_order'] ) ? $cl_options['cepatlakoo_sms_new_order'] : '';
        $cepatlakoo_sms_order_process = !empty( $cl_options['cepatlakoo_sms_order_process'] ) ? $cl_options['cepatlakoo_sms_order_process'] : '';
        $cepatlakoo_sms_order_complete = !empty( $cl_options['cepatlakoo_sms_order_complete'] ) ? $cl_options['cepatlakoo_sms_order_complete'] : '';
        $cepatlakoo_sms_order_pending = !empty( $cl_options['cepatlakoo_sms_order_pending'] ) ? $cl_options['cepatlakoo_sms_order_pending'] : '';
        $cepatlakoo_sms_order_failed = !empty( $cl_options['cepatlakoo_sms_order_failed'] ) ? $cl_options['cepatlakoo_sms_order_failed'] : '';
        $cepatlakoo_sms_order_refunded = !empty( $cl_options['cepatlakoo_sms_order_refunded'] ) ? $cl_options['cepatlakoo_sms_order_refunded'] : '';
        $cepatlakoo_sms_order_cancel = !empty( $cl_options['cepatlakoo_sms_order_cancel'] ) ? $cl_options['cepatlakoo_sms_order_cancel'] : '';

        // Make Connection to SmsGateway
        $smsGateway = new SmsGateway($cepatlakoo_sms_gateway_token);
        $cl_device_id = $cepatlakoo_sms_gateway_deviceID;
        //get order & buyer datas
        $cl_order = new WC_Order( $order );
        $cl_custom_fee = $cl_order->get_fees();

        if ( $cl_custom_fee ) {
            foreach($cl_custom_fee as $cl_fee){}
        } else {
            $cl_fee = null;
        }

        $cl_order_id = trim(str_replace('#', '', $cl_order->get_order_number()));
        $cl_base_order_status = $cl_order->get_status();
        $cl_status_list = array(
                            'pending' => __('Pending', 'cepatlakoo'),
                            'processing' => __('Processing', 'cepatlakoo'),
                            'on-hold' => __('On Hold', 'cepatlakoo'),
                            'completed' => __('Completed', 'cepatlakoo'),
                            'cancelled' => __('Cancelled', 'cepatlakoo'),
                            'refunded' => __('Refunded', 'cepatlakoo'),
                            'failed' => __('Failed', 'cepatlakoo'),
                        );
        foreach ($cl_status_list as $cl_status_lists => $translate){
            if ($cl_base_order_status == $cl_status_lists){
                $cl_order_status = $translate;
            }
        }

        $cl_shop_name = get_bloginfo('name');
        $cl_fullname = get_post_meta( $cl_order_id, '_billing_first_name', true ) . ' ' . get_post_meta( $cl_order_id, '_billing_last_name', true );
        $cl_email = get_post_meta( $cl_order_id, '_billing_email', true );
        $cl_phone_number = get_post_meta( $cl_order_id, '_billing_phone', true );
        $cl_shipping_price = get_post_meta( $cl_order_id, '_order_shipping', true );
        $cl_total_price = get_post_meta( $cl_order_id, '_order_total', true );
        $cl_tracking_code = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) : '-';
        $cl_tracking_date = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true ) ) ? date( get_option('date_format'), strtotime(get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true )) ) : '-';
        $cl_courier = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) : '-';
        ( $cl_fee ) ? $cl_payment_code = $cl_fee->get_total() : $cl_payment_code = null;
        //formating phone number (format : +628....)
        if ( preg_match('[^62]', $cl_phone_number ) ) {
            $wa_phone = str_replace('62', '+62', $cl_phone_number);
        } else if ( $cl_phone_number[0] == '0' ) {
            $cl_phone_number = ltrim( $cl_phone_number, '0' );
            $wa_phone = '+62'. $cl_phone_number;
        } else if ( $cl_phone_number[0] == '8' ) {
            $wa_phone = '+62'. $cl_phone_number;
        } else {
            $wa_phone = $cl_phone_number;
        }
        //replace shortcode option to real data each status
        if ($cl_base_order_status == 'processing'){
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_tracking_date%', $cl_tracking_date, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_process);
            $cepatlakoo_sms_order_process = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_process);
            
            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_process');
            }

            $cepatlakoo_sms_order_process = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_process, $cl_device_id );
        } else if ($cl_base_order_status == 'pending') {
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_pending);
            $cepatlakoo_sms_order_pending = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_pending);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_pending');
            }

            $cepatlakoo_sms_order_pending = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_pending, $cl_device_id );
        } else if ($cl_base_order_status == 'on-hold') {
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_new_order);
            $cepatlakoo_sms_new_order = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_new_order);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_new_order');
            }

            $cepatlakoo_sms_new_order = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_new_order, $cl_device_id );
        } else if ($cl_base_order_status == 'completed') {
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_tracking_date%', $cl_tracking_date, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_complete);
            $cepatlakoo_sms_order_complete = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_complete);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_complete');
            }

            $cepatlakoo_sms_order_complete = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_complete, $cl_device_id );
        } else if ($cl_base_order_status == 'cancelled') {
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_cancel);
            $cepatlakoo_sms_order_cancel = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_cancel);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_cancel');
            }

            $cepatlakoo_sms_order_cancel = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_cancel, $cl_device_id );
        } else if ($cl_base_order_status == 'refunded') {
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_refunded);
            $cepatlakoo_sms_order_refunded = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_refunded);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_refunded');
            }

            $cepatlakoo_sms_order_refunded = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_refunded, $cl_device_id );
        } else if ($cl_base_order_status == 'failed') {
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_failed);
            $cepatlakoo_sms_order_failed = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_failed);

            if ( !empty($cl_wa_token) ) {
                cepatlakoo_wassenger_generate_msg($order, 'cepatlakoo_wa_order_failed');
            }

            $cepatlakoo_sms_order_failed = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_failed, $cl_device_id );
        }
    }
}
add_action('woocommerce_order_status_pending', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_failed', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_on-hold', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_completed', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_processing', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_refunded', 'cepatlakoo_customer_order_notification', 10);
add_action('woocommerce_order_status_cancelled', 'cepatlakoo_customer_order_notification', 10);

/**
* Functions to send SMS & WhatsApp message if order status change for admin
*
* @package WordPress
* @subpackage CepatLakoo
* @since CepatLakoo 1.0.2
*/
if ( ! function_exists( 'cepatlakoo_new_order_admin' ) ) {
    function cepatlakoo_new_order_admin( $order ) {
        global $woocommerce, $cl_options;

        $cepatlakoo_sms_gateway_token = !empty( $cl_options['cepatlakoo_sms_gateway_token'] ) ? $cl_options['cepatlakoo_sms_gateway_token'] : '';
        $cl_wa_token                    = !empty( $cl_options['cepatlakoo_wa_token'] ) ? $cl_options['cepatlakoo_wa_token'] : '';
        $cepatlakoo_sms_gateway_deviceID = !empty( $cl_options['cepatlakoo_sms_gateway_deviceID'] ) ? $cl_options['cepatlakoo_sms_gateway_deviceID'] : '';
        $cepatlakoo_sms_gateway_phone = !empty( $cl_options['cepatlakoo_sms_gateway_phone'] ) ? $cl_options['cepatlakoo_sms_gateway_phone'] : '';

        $cepatlakoo_sms_new_order_admin = !empty( $cl_options['cepatlakoo_sms_new_order_admin'] ) ? $cl_options['cepatlakoo_sms_new_order_admin'] : '';
        $cl_wa_new_order_admin = !empty( $cl_options['cepatlakoo_wa_new_order_admin'] ) ? $cl_options['cepatlakoo_wa_new_order_admin'] : '';
        
        $cl_wa_admin_phone = !empty( $cl_options['cepatlakoo_wa_admin_phone'] ) ? $cl_options['cepatlakoo_wa_admin_phone'] : '';

        $smsGateway = new SmsGateway($cepatlakoo_sms_gateway_token);
        $cl_device_id = $cepatlakoo_sms_gateway_deviceID;
        $cl_admin_number = $cepatlakoo_sms_gateway_phone;
        //get order & buyer datas
        $cl_order = new WC_Order( $order );
        $cl_order_id = trim(str_replace('#', '', $cl_order->get_order_number()));
        $cl_order_status = $cl_order->get_status();
        $cl_status_list = array(
                            'pending' => __('Pending', 'cepatlakoo'),
                            'processing' => __('Processing', 'cepatlakoo'),
                            'on-hold' => __('On Hold', 'cepatlakoo'),
                            'completed' => __('Completed', 'cepatlakoo'),
                            'cancelled' => __('Cancelled', 'cepatlakoo'),
                            'refunded' => __('Refunded', 'cepatlakoo'),
                            'failed' => __('Failed', 'cepatlakoo'),
                        );
        foreach ($cl_status_list as $cl_status_lists => $translate){
            if ($cl_order_status == $cl_status_lists){
                $cl_order_status = $translate;
            }
        }

        $cl_shop_name = get_bloginfo('name');

        $items = $cl_order->get_items();
        $data_product = array();
    
        foreach ( $items as $item ) {
            $data_variation = array();
            foreach ( get_variation_data_from_variation_id($item['variation_id']) as $variation ) {
                array_push( $data_variation, $variation );
            }

            if ( count( $data_variation ) > 2 ) {
                $data_variation = implode( ', ',$data_variation );
                $data_variation = ' - ' . $data_variation;
            } else {
                $data_variation = null;
            }

            array_push( $data_product, $item['name']  . $data_variation .' ('. $item['quantity'] .')' );
        }
    
        $data_products = implode(",\n",$data_product);
        //replace shortcode option to real data
        // SMS
        $cepatlakoo_sms_new_order_admin = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_new_order_admin);
        $cepatlakoo_sms_new_order_admin = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_new_order_admin);
        $cepatlakoo_sms_new_order_admin = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_new_order_admin);

        // WhatsApp
        $cl_wa_new_order_admin = str_replace( '%lakoo_order_id%', $cl_order_id, $cl_wa_new_order_admin);
        $cl_wa_new_order_admin = str_replace( '%lakoo_order_status%', $cl_order_status, $cl_wa_new_order_admin);
        $cl_wa_new_order_admin = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cl_wa_new_order_admin);
        $cl_wa_new_order_admin = str_replace( '%lakoo_products%', $data_products, $cl_wa_new_order_admin);
        // var_dump($wa_phone, $cl_wa_new_order_admin); exit();

        $sent_to_admin = $smsGateway->sendMessageToNumber($cl_admin_number, $cepatlakoo_sms_new_order_admin, $cl_device_id );

        if ( !empty($cl_wa_token) ) {
            cepatlakoo_wassenger_send( $cl_wa_admin_phone, $cl_wa_new_order_admin );
        }

    }
}
add_action('woocommerce_checkout_order_processed', 'cepatlakoo_new_order_admin', 10);
// add_action('woocommerce_order_status_pending_to_processing_notification', 'cepatlakoo_new_order_admin', 10);
// add_action('woocommerce_order_status_pending_to_on-hold_notification', 'cepatlakoo_new_order_admin', 10);
// add_action('woocommerce_order_status_pending_to_completed_notification', 'cepatlakoo_new_order_admin', 10);

/**
* Functions to send SMS if order note add for customer
*
* @package WordPress
* @subpackage CepatLakoo
* @since CepatLakoo 1.0.2
*/
if ( ! function_exists( 'cepatlakoo_send_customer_note' ) ) {
    function cepatlakoo_send_customer_note($data) {
        global $woocommerce, $cl_options;

        $cepatlakoo_sms_gateway_token = !empty( $cl_options['cepatlakoo_sms_gateway_token'] ) ? $cl_options['cepatlakoo_sms_gateway_token'] : '';
        $cl_wa_token                    = !empty( $cl_options['cepatlakoo_wa_token'] ) ? $cl_options['cepatlakoo_wa_token'] : '';
        $cepatlakoo_sms_gateway_deviceID = !empty( $cl_options['cepatlakoo_sms_gateway_deviceID'] ) ? $cl_options['cepatlakoo_sms_gateway_deviceID'] : '';
        $cepatlakoo_sms_gateway_phone = !empty( $cl_options['cepatlakoo_sms_gateway_phone'] ) ? $cl_options['cepatlakoo_sms_gateway_phone'] : '';
        $cepatlakoo_sms_order_note = !empty( $cl_options['cepatlakoo_sms_order_note'] ) ? $cl_options['cepatlakoo_sms_order_note'] : '';

        $cl_wa_order_note = !empty( $cl_options['cepatlakoo_wa_order_note'] ) ? $cl_options['cepatlakoo_wa_order_note'] : '';

        $smsGateway = new SmsGateway($cepatlakoo_sms_gateway_token);
        $cl_device_id = $cepatlakoo_sms_gateway_deviceID;

        $cl_order = new WC_Order( $data['order_id'] );
        $cl_custom_fee = $cl_order->get_fees();
        if ( $cl_custom_fee ) {
            foreach($cl_custom_fee as $cl_fee){}
        }else{
            $cl_fee = null;
        }
        //get order & buyer datas
        $cl_order_id = trim(str_replace('#', '', $cl_order->get_order_number()));
        $cl_base_order_status = $cl_order->get_status();
        $cl_status_list = array(
                            'pending' => __('Pending', 'cepatlakoo'),
                            'processing' => __('Processing', 'cepatlakoo'),
                            'on-hold' => __('On Hold', 'cepatlakoo'),
                            'completed' => __('Completed', 'cepatlakoo'),
                            'cancelled' => __('Cancelled', 'cepatlakoo'),
                            'refunded' => __('Refunded', 'cepatlakoo'),
                            'failed' => __('Failed', 'cepatlakoo'),
                        );
        foreach ($cl_status_list as $cl_status_lists => $translate){
            if ($cl_base_order_status == $cl_status_lists){
                $cl_order_status = $translate;
            }
        }
        $cl_shop_name = get_bloginfo('name');
        $cl_fullname = get_post_meta( $cl_order_id, '_billing_first_name', true ) . ' ' . get_post_meta( $cl_order_id, '_billing_last_name', true );
        $cl_email = get_post_meta( $cl_order_id, '_billing_email', true );
        $cl_phone_number = get_post_meta( $cl_order_id, '_billing_phone', true );
        $cl_shipping_price = get_post_meta( $cl_order_id, '_order_shipping', true );
        $cl_total_price = get_post_meta( $cl_order_id, '_order_total', true );
        $cl_tracking_code = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) : '-';
        $cl_tracking_date = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true ) ) ? date( get_option('date_format'), strtotime(get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true )) ) : '-';
        $cl_courier = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) : '-';
        ( $cl_fee ) ? $cl_payment_code = $cl_fee->get_total() : $cl_payment_code = null;
        //formating phone number (format : +628....)
        if ( preg_match('[^62]', $cl_phone_number ) ) {
            $wa_phone = str_replace('62', '+62', $cl_phone_number);
        }else if ( $cl_phone_number[0] == '0' ) {
            $cl_phone_number = ltrim( $cl_phone_number, '0' );
            $wa_phone = '+62'. $cl_phone_number;
        }else if ( $cl_phone_number[0] == '8' ) {
            $wa_phone = '+62'. $cl_phone_number;
        } else {
            $wa_phone = $cl_phone_number;
        }
        //replace shortcode option to real data
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_order_id%', $cl_order_id, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_order_status%', $cl_order_status, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_fullname%', $cl_fullname, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_email%', $cl_email, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_total_price%', $cl_total_price, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_tracking_date%', $cl_tracking_date, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_courier%', $cl_courier, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cepatlakoo_sms_order_note);
        $cepatlakoo_sms_order_note = str_replace( '%lakoo_note%', wptexturize($data['customer_note']) , $cepatlakoo_sms_order_note);
        
        $sent_to_admin = $smsGateway->sendMessageToNumber($wa_phone, $cepatlakoo_sms_order_note, $cl_device_id );

        $cl_wa_order_note = str_replace( '%lakoo_order_id%', $cl_order_id, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_order_status%', $cl_order_status, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_fullname%', $cl_fullname, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_email%', $cl_email, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_total_price%', $cl_total_price, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_tracking_date%', $cl_tracking_date, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_courier%', $cl_courier, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cl_wa_order_note);
        $cl_wa_order_note = str_replace( '%lakoo_note%', wptexturize($data['customer_note']) , $cl_wa_order_note);
        if( !empty($cl_wa_token) ){
            cepatlakoo_wassenger_send($wa_phone, $cl_wa_order_note);
        }

    }
}
add_action('woocommerce_new_customer_note', 'cepatlakoo_send_customer_note', 10);

/**
* Functions to send message to wassenger
*
* @package WordPress
* @subpackage CepatLakoo
* @since Cepatlakoo 1.4.0
*
*/
if ( ! function_exists( 'cepatlakoo_wassenger_send' ) ) {
    function cepatlakoo_wassenger_send($to, $msg) {
        global $cl_options;
        //check if $to and $msg empty return false
        if( empty($to) && empty($msg) ){
            return;
        }
        //check wa token is !empty
        if(isset($cl_options['cepatlakoo_wa_token']) && !empty($cl_options['cepatlakoo_wa_token'])){
            //generate param for wassanger
            if( !empty($cl_options['cepatlakoo_wa_device']) ){
                $data = json_encode( array("phone" => $to, "message" => $msg, "device" => $cl_options['cepatlakoo_wa_device']) );
            }
            else{
                $data = json_encode( array("phone" => $to, "message" => $msg) );
            }
            //send request to wassanger
            $ch = curl_init('https://api.wassenger.com/v1/messages');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Token: '.$cl_options['cepatlakoo_wa_token'],
            ));

            $resp = curl_exec($ch);
        }
    }
}

/**
* Functions to cron daily send pending order notification with wassenger
*
* @package WordPress
* @subpackage CepatLakoo
* @since Cepatlakoo 1.4.0
*
*/
if ( ! function_exists( 'cepatlakoo_wassenger_cron_pending_daily' ) ) {
    function cepatlakoo_wassenger_cron_pending_daily() {
        global $cl_options;
        // echo'<pre>';
        //check pending order after h+1
        if( isset($cl_options['cepatlakoo_wa_token']) && !empty($cl_options['cepatlakoo_wa_token']) && !empty($cl_options['cepatlakoo_wa_order_pending_h1']) ){
            $args = array(
                'status' => 'on-hold',
                'date_created' => date('Y-m-d', strtotime("- 1 day")),
            );
            $orders = wc_get_orders( $args );
            
            if( !empty($orders) ){
                foreach( $orders as $ord ){
                    cepatlakoo_wassenger_generate_msg($ord, 'cepatlakoo_wa_order_pending_h1');
                }
            }
        }
        //check pending order after h+2
        if( isset($cl_options['cepatlakoo_wa_token']) && !empty($cl_options['cepatlakoo_wa_token']) && !empty($cl_options['cepatlakoo_wa_order_pending_h2']) ){
            $args2 = array(
                'status' => 'on-hold',
                'date_created' => date('Y-m-d', strtotime("- 2 day")),
            );
            $orders2 = wc_get_orders( $args2 );
            
            if( !empty($orders2) ){
                foreach( $orders2 as $ord ){
                    cepatlakoo_wassenger_generate_msg($ord, 'cepatlakoo_wa_order_pending_h2');
                }
            }
        }
        // exit();
    }
}
// add_action('init', 'cepatlakoo_wassenger_cron_pending_daily');

/**
* schedule daily cron for send pending order notification with wassenger
*
* @package WordPress
* @subpackage CepatLakoo
* @since Cepatlakoo 1.4.0
*
*/
if ( ! wp_next_scheduled( 'cl_order_pending_daily_cron' ) ) {
    wp_schedule_event( time(), 'daily', 'cl_order_pending_daily_cron' );
}
add_action( 'cl_order_pending_daily_cron', 'cepatlakoo_wassenger_cron_pending_daily' );

/**
* Functions to generate message for wassenger
*
* @package WordPress
* @subpackage CepatLakoo
* @since Cepatlakoo 1.4.0
*
*/
function cepatlakoo_wassenger_generate_msg($orders, $opt){
    global $cl_options;
    //check is orders empty?
    if ( !empty($orders) ) {
        $cl_wa_pending_h1 = !empty( $cl_options[$opt] ) ? $cl_options[$opt] : '';
        $cl_order = new WC_Order( $orders );
        $cl_custom_fee = $cl_order->get_fees();
        //check custom fee from order
        if ( $cl_custom_fee ) {
            foreach($cl_custom_fee as $cl_fee){}
        } else {
            $cl_fee = null;
        }

        $cl_order_id = trim(str_replace('#', '', $cl_order->get_order_number()));
        $cl_base_order_status = $cl_order->get_status();
        $cl_status_list = array(
                            'pending' => __('Pending', 'cepatlakoo'),
                            'processing' => __('Processing', 'cepatlakoo'),
                            'on-hold' => __('On Hold', 'cepatlakoo'),
                            'completed' => __('Completed', 'cepatlakoo'),
                            'cancelled' => __('Cancelled', 'cepatlakoo'),
                            'refunded' => __('Refunded', 'cepatlakoo'),
                            'failed' => __('Failed', 'cepatlakoo'),
                        );
        foreach ($cl_status_list as $cl_status_lists => $translate) {
            if ($cl_base_order_status == $cl_status_lists) {
                $cl_order_status = $translate;
            }
        }
        //get order & buyer datas
        $cl_shop_name = get_bloginfo('name');
        $cl_fullname = get_post_meta( $cl_order_id, '_billing_first_name', true ) . ' ' . get_post_meta( $cl_order_id, '_billing_last_name', true );
        $cl_email = get_post_meta( $cl_order_id, '_billing_email', true );
        $cl_phone_number = get_post_meta( $cl_order_id, '_billing_phone', true );
        $cl_shipping_price = get_post_meta( $cl_order_id, '_order_shipping', true );
        $cl_total_price = get_post_meta( $cl_order_id, '_order_total', true );
        $cl_tracking_code = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_resi', true ) : '-';
        $cl_tracking_date = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true ) ) ? date( get_option('date_format'), strtotime(get_post_meta( $cl_order_id, '_cepatlakoo_resi_date', true )) ) : '-';
        $cl_courier = !empty( get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) ) ? get_post_meta( $cl_order_id, '_cepatlakoo_ekspedisi', true ) : '-';
        ( $cl_fee ) ? $cl_payment_code = $cl_fee->get_total() : $cl_payment_code = null;
        $items = $cl_order->get_items();
        $data_product = array();
        //get ordered products
        foreach ( $items as $item ) {
            $data_variation = array();
            foreach ( get_variation_data_from_variation_id($item['variation_id']) as $variation ) {
                array_push( $data_variation, $variation );
            }

            if( count($data_variation) > 2 ) {
                $data_variation = implode(", ",$data_variation);
                $data_variation = ' - ' . $data_variation;
            } else {
                $data_variation = null;
            }
            array_push( $data_product, $item['name']  . $data_variation .' ('. $item['quantity'] .')' );
        }
    
        $data_products = implode(",\n",$data_product);
        //formating phone number (format : +628....)
        if ( preg_match('[^62]', $cl_phone_number ) ) {
            $wa_phone = str_replace('62', '+62', $cl_phone_number);
        } else if ( $cl_phone_number[0] == '0' ) {
            $cl_phone_number = ltrim( $cl_phone_number, '0' );
            $wa_phone = '+62'. $cl_phone_number;
        } else if ( $cl_phone_number[0] == '8' ) {
            $wa_phone = '+62'. $cl_phone_number;
        } else {
            $wa_phone = $cl_phone_number;
        }
        //replace shortcode option to real data
        $cl_wa_pending_h1 = str_replace( '%lakoo_order_id%', $cl_order_id, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_order_status%', $cl_order_status, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_shop_name%', $cl_shop_name, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_fullname%', $cl_fullname, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_email%', $cl_email, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_phone_number%', $cl_phone_number, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_shipping_price%', $cl_shipping_price, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_total_price%', $cl_total_price, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_tracking_code%', $cl_tracking_code, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_tracking_date%', $cl_tracking_date, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_products%', $data_products, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_courier%', $cl_courier, $cl_wa_pending_h1);
        $cl_wa_pending_h1 = str_replace( '%lakoo_payment_code%', $cl_payment_code, $cl_wa_pending_h1);
        //send message to wassenger
        // var_dump($wa_phone, $cl_wa_pending_h1);
        cepatlakoo_wassenger_send( $wa_phone, $cl_wa_pending_h1 );
        //add note to order log
        if ( $opt == 'cepatlakoo_wa_order_pending_h1' ) {
            $cl_order->add_order_note(__('Successfully sent H+1 follow up message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_pending_h2' ) {
            $cl_order->add_order_note(__('Successfully sent H+2 follow up message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_failed' ) {
            $cl_order->add_order_note(__('Successfully sent update status failed message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_refunded' ) {
            $cl_order->add_order_note(__('Successfully sent update status refunded message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_cancel' ) {
            $cl_order->add_order_note(__('Successfully sent update status canceled message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_complete' ) {
            $cl_order->add_order_note(__('Successfully sent update status completed message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_new_order' ) {
            $cl_order->add_order_note(__('Successfully sent new order message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_pending' ) {
            $cl_order->add_order_note(__('Successfully sent update status pending message via WhatsApp', 'cepatlakoo'));
        } elseif ( $opt == 'cepatlakoo_wa_order_process' ) {
            $cl_order->add_order_note(__('Successfully sent update status completed message via WhatsApp', 'cepatlakoo'));
        } else {
            $cl_order->add_order_note(__('Successfully sent '.$opt, 'cepatlakoo'));
        }
    }
}