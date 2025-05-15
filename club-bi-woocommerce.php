<?php
/**
* Plugin Name: Club Bi - WooCommerce
* Plugin URI: https://github.com/TipiCode/Woocommerce-ClubBi
* Description: Plugin para Woocommerce que habilita la opción de habilitar descuentos por medio del servicio de Club BI.
* Version:     1.1.0
* Requires PHP: 7.4
* Author:      tipi(code)
* Author URI: https://codingtipi.com
* License:     MIT
* WC requires at least: 7.4.0
* WC tested up to: 9.8.5
*
* @package WoocommerceClubBi
*/
define('CLUB_BI_PLUGIN_VERSION', '1.1.0');
define('CLUB_BI_APP_ID', '8c28f624-6fbc-4dac-bb86-378029cfb158');

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // No permitir acceder el plugin directamente
}

/**
* Función encargada de inicializar el plugin de Club BI
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/club-bi
* @since 1.0.0
*/
function club_bi_init() {
    if (!class_exists('WC_Payment_Gateway')) return;
    
    // Incluir archivos solo si no han sido incluidos
    if (!class_exists('ClubBi')) {
        include_once('utils/curl.php');         
        include_once('classes/discount.php');     
        include_once('classes/club-bi.php');      
        include_once('includes/support.php');      
        ClubBi::get_instance();
    }

    // Incluir el actualizador solo si no existe
    if (!class_exists('\YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
        include_once('includes/plugin-update-checker/plugin-update-checker.php');
        
        $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://tipi-pod.sfo3.digitaloceanspaces.com/plugins/club-bi/details.json',
            __FILE__,
            'woocommerce-club-bi'
        );
    }
}

// Cambiamos la prioridad a 20 para asegurarnos que WooCommerce esté cargado
add_action('plugins_loaded', 'club_bi_init', 20);

// Inicializar el validador de cupones solo una vez
if (!class_exists('ClubBi_Coupon_Validator')) {
    require_once plugin_dir_path(__FILE__) . 'includes/club-bi-coupon-validator.php';
    new ClubBi_Coupon_Validator();
}

/**
* Registra los archivos de Javascript para poder manejar el UI
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/club-bi
* @since 1.1.0
*/
function club_bi_script_enqueuer() {
    wp_enqueue_script(
        'club_bi', 
        plugins_url('/js/club_bi.js', __FILE__), 
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script(
        'club_bi', 
        'club_bi_ajax', 
        array(
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
}
add_action('wp_enqueue_scripts', 'club_bi_script_enqueuer');

/**
* Añade funcionalidad para compatibilidad con HPO de WooCommerce
* 
* @author Luis E. Mendoza <lmendoza@codingtipi.com>
* @link https://codingtipi.com/project/club-bi
* @since 1.1.0
*/
function club_bi_hpo(){
  if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
  }
} 
add_action('before_woocommerce_init', 'club_bi_hpo');