jQuery(document).ready(function () {
    jQuery(".validate").click(function (e) {
      e.preventDefault();
      jQuery(document.body).trigger("update_checkout");
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: club_bi_ajax.ajaxurl,
        data: { action: "club_bi_redeem",
        cbi_card: jQuery('#cbi_card').val() },
        success: function (response) {
          jQuery(document.body).trigger("update_checkout");
        },
        error: function(error) {
          jQuery('.woocommerce-notices-wrapper').first().empty().append(error.responseText);
        }
      });
    });
});