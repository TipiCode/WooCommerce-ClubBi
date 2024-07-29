jQuery(document).ready(function () {
    jQuery(".validate").click(function (e) {
        e.preventDefault();
        jQuery('.club_bi, .woocommerce-checkout-review-order-table').addClass('disabled'); 
        jQuery('.loader').addClass('show'); 
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: club_bi_ajax.ajaxurl, 
            data: { 
                action: "club_bi_redeem",
                cbi_card: jQuery('#cbi_card').val() 
            },
            success: function (response) {
                if(response.code = 200){
                    jQuery('#coupon_status').val(200);
                    jQuery('#authorozacion_club_bi').val(response.authorization);
                    jQuery('#confirmacion_club_bi').val(response.confirmation);
                    jQuery( document.body ).trigger( 'update_checkout' );
                }
            
                jQuery('.club_bi, .woocommerce-checkout-review-order-table').removeClass('disabled');
                jQuery('.loader').removeClass('show');
            },
            error: function(error) {
                console.log('Error');
                console.log(error.responseText);
                jQuery('.club_bi, .woocommerce-checkout-review-order-table').removeClass('disabled');
                jQuery('.loader').removeClass('show');
                jQuery('.woocommerce-notices-wrapper').first().empty().append('<ul class="woocommerce-error" role="alert"><li>' + error.responseText + '</li></ul>');
                jQuery('html, body').animate({
                    scrollTop: 0
               }, 1000);
         
            }
        });
    });
  });
  