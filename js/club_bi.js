jQuery(document).ready(function () {
    jQuery(".validate").click(function (e) {
      e.preventDefault();
  
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: club_bi_ajax.ajaxurl,
        data: { action: "club_bi_redeem" },
        success: function (response) {
          if (response.type == "success") {
            jQuery("#test").html(response);
          } else {
            alert("something broke");
          }
        },
      });
    });
});