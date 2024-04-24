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



