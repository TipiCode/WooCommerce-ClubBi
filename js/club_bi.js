jQuery(document).ready(function ($) {
    console.log('Club BI JS initialized');
    $(document).on('click', '.validate', function (e) {
        e.preventDefault();
        const $button = $(this);
        const $loader = $('#club_bi_loader');
        const cardNumber = $('#cbi_card').val();
        
        $button.prop('disabled', true);
        $loader.show();

        $.ajax({
            type: "post",
            dataType: "json",
            url: club_bi_ajax.ajaxurl,
            data: { 
                action: "club_bi_redeem",
                cbi_card: cardNumber
            },
            beforeSend: function(xhr) {
                if (club_bi_ajax.token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + club_bi_ajax.token);
                }
            },
            success: function (response) {
                console.log('Club BI - Success Response:', response);
                $(document.body).trigger("update_checkout");
            },
            error: function(xhr, status, error) {
                console.log('Club BI - Error Status:', status);
                console.log('Club BI - Error:', error);
                console.log('Club BI - Response Text:', xhr.responseText);
                console.log('Club BI - Status Code:', xhr.status);
                
                let errorMessage = 'Error desconocido';
                try {
                    const responseData = JSON.parse(xhr.responseText);
                    errorMessage = responseData.data || responseData.message || error;
                } catch(e) {
                    errorMessage = xhr.responseText || error;
                }

                // Remover errores previos
                $('.woocommerce-NoticeGroup-checkout').remove();
                
                // Insertar el nuevo error 
                $('.wc-block-components-main').before(
                    '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' +
                    '<ul class="woocommerce-error" role="alert">' +
                    '<li>' + errorMessage + '</li>' +
                    '</ul>' +
                    '</div>'
                );
                
                // Scroll al error
                $('html, body').animate({
                    scrollTop: $('.woocommerce-NoticeGroup-checkout').offset().top - 100
                }, 1000);
            },
            complete: function() {
                $button.prop('disabled', false);
                $loader.hide();
            }
        });
    });
});