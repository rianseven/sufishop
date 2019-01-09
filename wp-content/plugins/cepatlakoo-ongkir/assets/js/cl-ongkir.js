/*!
 * cl_ongkir.js v1.3.1
 * Copyright 2014, eezhal92
 *
 * Freely distributable under the MIT license.
 * this script using jQuery
 *
 * http://eezhal92.com
 */

jQuery(document).ready(function( $ ) {
	
	// initialization
	$('#billing_city').attr('disabled', true).select2();
	$('#shipping_city').attr('disabled', true).select2();
	$('#billing_subdistrict').attr('disabled', true).select2();
	$('#shipping_subdistrict').attr('disabled', true).select2();
	$('select#billing_city[disabled=disabled]').css('background', 'rgba(0,0,0,0.04)');
	$('select#shipping_city[disabled=disabled]').css('background', 'rgba(0,0,0,0.04)');
	$('select#billing_subdistrict[disabled=disabled]').css('background', 'rgba(0,0,0,0.04)');
	$('select#shipping_subdistrict[disabled=disabled]').css('background', 'rgba(0,0,0,0.04)');
	
	//BIlling State = Province
	var user_billing_subdistrict = cl_ongkirAjax.billing_subdistrict;
	var user_shipping_subdistrict = cl_ongkirAjax.shipping_subdistrict;
	var user_billing_state = $('#billing_state').val();
	var user_shipping_state = $('#shipping_state').val();

	var curRequest = {};
	var diffShipping = ( $('.woocommerce-checkout').length > 0 ) ? $('#ship-to-different-address input:checkbox:checked').length : 1;

	removeFieldsClass();

	if( ! diffShipping ){
		if( user_billing_state ) {
			getCities('#billing_city', user_billing_state, cl_ongkirAjax.billing_city );
		}
	}else if( user_shipping_state ) {
		getCities('#shipping_city', user_shipping_state, cl_ongkirAjax.shipping_city );
	}

	//City Billing on Change : ALL PACKAGE
	$('#calc_shipping_state').live('change', function() {
		var b_state_id = $(this).find('option:selected').val();
		$('#calc_shipping_city').attr('disabled', true);
		$('select#calc_shipping_city').css('background', 'rgba(0,0,0,0.04)');

		if( b_state_id ) {
			$('#calc_shipping_city').empty();
			$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#calc_shipping_city');
			getCities( '#calc_shipping_city', b_state_id, cl_ongkirAjax.billing_city);
		}
	});

	//City Billing on Change : ALL PACKAGE
	$('#billing_state').live('change', function() {
		var b_state_id = $(this).find('option:selected').val();
		$('#billing_city').attr('disabled', true);
		$('select#billing_city').css('background', 'rgba(0,0,0,0.04)');

		if( b_state_id ) {
			$('#billing_city').empty();
			$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#billing_city');
			$('#billing_subdistrict').empty();
			$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#billing_subdistrict');
			getCities( '#billing_city', b_state_id, cl_ongkirAjax.billing_city);
		}
	});

	//City Billing on Change : ALL PACKAGE
	$('#shipping_state').live('change', function() {
		var diffShipping = ( $('.woocommerce-checkout').length > 0 ) ? $('#ship-to-different-address input:checkbox:checked').length : 1;

		if ( diffShipping == 1 ){
			var s_state_id = $(this).find('option:selected').val();
			$('#shipping_city').attr('disabled', true);
			$('select#shipping_city').css('background', 'rgba(0,0,0,0.04)');

			if( s_state_id ) {
				$('#shipping_city').empty();
				$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#shipping_city');
				$('#shipping_subdistrict').empty();
				$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#shipping_subdistrict');
				getCities( '#shipping_city', s_state_id, cl_ongkirAjax.shipping_city);
			}
		}
	});

	//Subdistrict Billing on Change : PRO BASIC
	$('#billing_city').live('change', function() {
		var b_city_id = $(this).find('option:selected').val();
		$('#billing_subdistrict').attr("disabled", false);
		$('select#billing_subdistrict').css('background', 'rgba(0,0,0,0.0)');
		$('#cl_bill_city_name').val( $(this).find('option:selected').html() );
		
		if( b_city_id ) {
			$('#billing_subdistrict').empty();
			$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#billing_subdistrict');
			getSubdistrict( '#billing_subdistrict', b_city_id, null);
		}
	});

	//Subdistrict Shippinh on Change : PRO BASIC
	$('#shipping_city').live('change', function() {
		var diffShipping = ( $('.woocommerce-checkout').length > 0 ) ? $('#ship-to-different-address input:checkbox:checked').length : 1;
		
		if ( diffShipping == 1 ){
			var s_city_id = $(this).find('option:selected').val();
			$('#shipping_city').attr("disabled", false);
			$('select#shipping_city').css('background', 'rgba(0,0,0,0.0)');
			$('#cl_ship_city_name').val( $(this).find('option:selected').html() );

			if( s_city_id ) {
				$('#shipping_subdistrict').empty();
				$("<option/>",{ value:'loading', text:'Loading...' }).appendTo('#shipping_subdistrict');
				getSubdistrict( '#shipping_subdistrict', s_city_id, null);
			}
		}
	});

	// Trigger Subdistrict
	$('#billing_subdistrict').live('change', function() {
		var s_city_id = $(this).find('option:selected').val();
		$('#shipping_city').attr("disabled", false);
		$('select#shipping_city').css('background', 'rgba(0,0,0,0.0)');
		$('#cl_bill_subdistrict_name').val( $(this).find('option:selected').html() );

		if( s_city_id ) {
			getSubdistrict( '#billing_subdistrict', null, s_city_id);
		}
	});

	// Trigger Subdistrict
	$('#shipping_subdistrict').live('change', function() {
		var diffShipping = ( $('.woocommerce-checkout').length > 0 ) ? $('#ship-to-different-address input:checkbox:checked').length : 1;
		
		if ( diffShipping == 1 ){
			var s_city_id = $(this).find('option:selected').val();
			$('#cl_ship_subdistrict_name').val( $(this).find('option:selected').html() );

			$('#shipping_city').attr("disabled", false);
			$('select#shipping_city').css('background', 'rgba(0,0,0,0.0)');

			if( s_city_id ) {
				getSubdistrict( '#shipping_subdistrict', null, s_city_id);
			}
		}
	});

	// Request Cities : ALL PACKAGE
	function getCities( city_element, user_state_id, user_city_id) {

		curRequest[city_element] = $.ajax({
	      	url: cl_ongkirAjax.ajax_url,
	      	type: 'get',
	      	data: {
				'action': 'get_cities',
				'nonce': cl_ongkirAjax.nonce,
				'state': user_state_id
			},
			beforeSend : function()    {
	            if(curRequest[city_element] != null) {
	                curRequest[city_element].abort();
	            }
	        },
			  success: function( data ) { // return string
				
				data = jQuery.parseJSON(data);
				if(data == '-'){
					alert(cl_ongkirAjax.error_msg);
					jQuery('#place_order').prop('disabled', true);
				}
				else{
					$(city_element).empty();
					$.each( data, function (city_id, city_name) {

						if( $(city_element).attr('data-selected') != 0 && $(city_element).attr('data-selected') == city_id ){
							$("<option/>",{ value:city_id, text:city_name, selected:'selected' }).appendTo(city_element);
						}
						else{
							$("<option/>",{ value:city_id, text:city_name}).appendTo(city_element);
						}

				  });
				  
				  $(city_element).attr('disabled', false);
				  $('select#billing_city').css('background', 'rgba(0,0,0,0)');
				  $('select#shipping_city').css('background', 'rgba(0,0,0,0)');
				  $(city_element).css('background', 'rgba(0,0,0,0)');
				  $(city_element).trigger("change");
				}
	      	}
	    });
	}

	// Request Subdistric : PRO & BASIC ONLY
	function getSubdistrict( element, user_city_id, user_subdistrict_id) {
		if(cl_ongkirAjax.type == 'pro'){
			curRequest[element] = $.ajax({
				  url: cl_ongkirAjax.ajax_url,
				  type: 'get',
				  data: {
					'action': 'get_subdistrict',
					'city': user_city_id,
					'sdistrict' : user_subdistrict_id,
					'nonce': cl_ongkirAjax.nonce,
				},
				beforeSend : function()    {
					if(curRequest[element] != null) {
						curRequest[element].abort();
					}
				},
				  success: function( data ) { // return string
					
					if( user_subdistrict_id === '' || user_subdistrict_id === null ){
						data = jQuery.parseJSON(data);
						$(element).empty();
						$.each( data, function (subdistrict_id, subdistrict_name) {
							
							if( $(element).attr('data-selected') != 0 && $(element).attr('data-selected') == subdistrict_id ){
								$("<option/>",{ value:subdistrict_id, text:subdistrict_name, selected:'selected' }).appendTo(element);
							}
							else{
								$("<option/>",{ value:subdistrict_id, text:subdistrict_name }).appendTo(element);
							}
					  });
					  
					  $(element).attr('disabled', false);
	  
					  $(element).css('background', 'rgba(0,0,0,0)');

					  $(element).trigger('change');
					}
				  }
			});
		}
	}

	// Page updated only when city fields is changed : OK
    function removeFieldsClass() {
		$('#billing_address_1_field').removeClass('address-field ');
		$('#billing_state_field').removeClass('address-field ');
		$('#billing_postcode_field').removeClass('address-field ');
		$('#shipping_address_1_field').removeClass('address-field ');
		$('#shipping_state_field').removeClass('address-field ');
		$('#shipping_postcode_field').removeClass('address-field ');
    }

	$('.select2').css('width', '100%');
	
    $(document).on('click', "#ship-to-different-address-checkbox", function() {
		if( $('#ship-to-different-address input:checkbox:checked').length > 0 && $('#shipping_city option').first().val() === '' ){
			getCities('#shipping_city', user_shipping_state, cl_ongkirAjax.shipping_city );
		}
	});

	// jQuery('#billing_state_field').after(jQuery('#billing_city_field'));
	// jQuery('#billing_city_field').after(jQuery('#billing_subdistrict_field'));
});
