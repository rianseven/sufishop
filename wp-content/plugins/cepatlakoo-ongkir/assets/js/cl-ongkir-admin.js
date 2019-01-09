(function($) {
    'use strict';
    $(document).ready(function () {
    	$('input[name="woocommerce_rajaongkir_api_key"]').closest('tr').css('display','none');
    	$('input[name="woocommerce_type_account"]').closest('tr').css('display','none');
        $('.cl-service-parent').hide();
        $('.cl-service-row').hide();

        $.each( $('#woocommerce_cl_ongkir_shipping_courier').val(), function( i, val ){
            $('.cl-service-parent').show();

            if( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'pro' ){
                $('.cl-service-row').show();
            }
            else if ( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'basic' ){
                $('.cl-service-row.jne, .cl-service-row.pos, .cl-service-row.tiki, .cl-service-row.pcp, .cl-service-row.esl, .cl-service-row.rpx').show();
            }
            else if ( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'starter' ){
                $('.cl-service-row.jne, .cl-service-row.pos, .cl-service-row.tiki').show();
            }
            else{
                $('.cl-service-row.'+val).show();
            }
        });

        $(document).on('change', '#woocommerce_cl_ongkir_shipping_courier', function() {
            $('.cl-service-row').hide();
            if( $(this).val() === null ){
                $('.cl-service-parent').hide();
            }
            $.each( $('#woocommerce_cl_ongkir_shipping_courier').val(), function( i, val ){
                $('.cl-service-parent').show();
                
                if( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'pro' ){
                    $('.cl-service-row').show();
                }
                else if ( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'basic' ){
                    $('.cl-service-row.jne, .cl-service-row.pos, .cl-service-row.tiki, .cl-service-row.pcp, .cl-service-row.esl, .cl-service-row.rpx').show();
                }
                else if ( val == 'all' && $('#woocommerce_cl_ongkir_shipping_type_ongkir').val() == 'starter' ){
                    $('.cl-service-row.jne, .cl-service-row.pos, .cl-service-row.tiki').show();
                }
                else{
                    $('.cl-service-row.'+val).show();
                }
            });
        });
    });

})(jQuery);
