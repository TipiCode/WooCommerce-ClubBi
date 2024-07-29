<?php
/**
* Plugin Name: Club Bi - WooCommerce
* Plugin URI: https://github.com/TipiCode/Woocommerce-ClubBi
* Description: Plugin para Woocommerce que habilita la opción de habilitar descuentos por medio del servicio de Club BI.
* Version:     1.0.0
* Requires PHP: 7.4
* Author:      tipi(code)
* Author URI: https://codingtipi.com
* License:     MIT
* WC requires at least: 7.4.0
* WC tested up to: 8.7.0
*
* @package WoocommerceClubBi
*/

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // No permitir acceder el plugin directamente
}
include_once dirname(__FILE__) . '/classes/club-bi.php';

/**
* Añade funcionalidad para compatibilidad con HPO de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.0.0
*/
function club_bi_hpo(){
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} 
add_action('before_woocommerce_init', 'club_bi_hpo');

/**
* Función encargada de inicializar el plugin de Club BI
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.0.0
*/
function club_bi_init() {
   
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    include_once ('classes/club-bi.php') ;
  
    ClubBi::get_instance();
  
   
}
add_action( 'plugins_loaded', 'club_bi_init', 0 );

/**
* Registra los archivos de Javascript para poder manejar el UI
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/recurrente
* @since 1.0.0
*/
function club_bi_script_enqueuer() {
    wp_register_script( "club_bi", WP_PLUGIN_URL.'/Woocommerce-ClubBi/js/club_bi.js', array('jquery') );
    wp_localize_script( 'club_bi', 'club_bi_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
 
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'club_bi' );
}
add_action( 'init', 'club_bi_script_enqueuer' );


// Add custom Field
add_action('woocommerce_after_order_notes', 'custom_checkout_field');

function custom_checkout_field($checkout)
{
    echo '<div id="custom_checkout_field" style="display:none;">';
    woocommerce_form_field('_custom_value', array(
        'type' => 'hidden',
        'required' => true,
        'class' => array('my-field-class form-row-wide'),
        'label' => __('Custom Field'),
        'placeholder' => __('Enter Custom Data'),
    ), $checkout->get_value('_custom_value'));

      woocommerce_form_field('_coupon_status', array(
        'type' => 'hidden',
        'id' => 'coupon_status',
        'required' => true,
        'class' => array('my-field-class form-row-wide'),
        'label' => __('Second Custom Field'),
        'placeholder' => __('Enter Second Custom Data'),
    ), $checkout->get_value('_coupon_status'));

    woocommerce_form_field('_authorozacion_club_bi', array(
        'type' => 'hidden',
        'id' => 'authorozacion_club_bi',
        'required' => true,
        'class' => array('my-field-class form-row-wide'),
        'label' => __('Second Custom Field'),
        'placeholder' => __('Enter Second Custom Data'),
    ), $checkout->get_value('_authorozacion_club_bi'));

    woocommerce_form_field('_confirmacion_club_bi', array(
        'type' => 'hidden',
        'id' => 'confirmacion_club_bi',
        'required' => true,
        'class' => array('my-field-class form-row-wide'),
        'label' => __('Second Custom Field'),
        'placeholder' => __('Enter Second Custom Data'),
    ), $checkout->get_value('_confirmacion_club_bi'));

    echo '</div>';
    ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#cbi_card').on('keyup', function() {
                var inputVal = $(this).val();
                $('input[name="_custom_value"]').val(inputVal);
            });
        });
        </script>
    <?php
}

//  Save the custom data
add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');

function custom_checkout_field_update_order_meta($order_id) {
    $order = wc_get_order( $order_id ); 
    foreach( $order->get_used_coupons() as $coupon_code ){
        $coupon = new WC_Coupon($coupon_code);
        $coupon_id = $coupon->code;
        $couponstatus = 200;

        if (!empty($coupon_id) && !empty($couponstatus)) {
            $order->update_meta_data( '_custom_value',  $_POST['_custom_value'] );
            $order->update_meta_data( '_coupon_status',  $couponstatus );
            $order->update_meta_data( '_authorozacion_club_bi',  $_POST['_authorozacion_club_bi'] );
            $order->update_meta_data( '_confirmacion_club_bi',  $_POST['_confirmacion_club_bi'] );
            $order->save();
        } 
     }
}


// Get the Custom Field value
add_action( 'woocommerce_admin_order_data_after_order_details', 'misha_editable_order_meta_general' );

function misha_editable_order_meta_general( $order ){
	?>
		<br class="clear" />
		<?php
			$gift_name = $order->get_meta( '_custom_value' );
			$gift_message = $order->get_meta( '_coupon_status' );
            $gift_auth = $order->get_meta( '_authorozacion_club_bi' );
            $gift_conf = $order->get_meta( '_confirmacion_club_bi' );
		?>
		<div class="address">
			<?php
				woocommerce_wp_text_input( array(
					'id' => '_custom_value',
					'label' => 'Coupon Code',
					'value' => $gift_name,
					'wrapper_class' => 'form-field-wide'
				) );
				woocommerce_wp_textarea_input( array(
					'id' => '_coupon_status',
					'label' => 'Coupon Success Code',
					'value' => $gift_message,
					'wrapper_class' => 'form-field-wide'
				) );
                woocommerce_wp_textarea_input( array(
					'id' => '_authorozacion_club_bi',
					'label' => 'Authorisation Code',
					'value' => $gift_auth,
					'wrapper_class' => 'form-field-wide'
				) );
                woocommerce_wp_textarea_input( array(
					'id' => '_confirmacion_club_bi',
					'label' => 'Confirmation Code',
					'value' => $gift_conf,
					'wrapper_class' => 'form-field-wide'
				) );
			?>
		</div>
	<?php 
}






















