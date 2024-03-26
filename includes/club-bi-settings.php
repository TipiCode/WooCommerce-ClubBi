<?php
/**
* Clase principal para interactura con la configuracion a guardar de Club Bi
*
* Esta clase es la principal para el manejo de las configuración
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/

class ClubBiSettings{
    private static $instance;


    /**
    * Función para patron de singleton
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return ClubBiSettings Clase inicializada
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public static function get_instance() {
        if (!isset(self::$instance)) {
          self::$instance = new self();
        }
        return self::$instance;
    }

    /**
    * Registra los campos de la configuración
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function register_settings(){
        register_setting( 'club_bi_options', 'club_bi_options', array($this, 'club_bi_options_validate') );
        add_settings_section( 'service_settings', '', array($this, 'service_settings_section_text'), 'club_bi' );

        add_settings_field( 'club_bi_branch', 'Código de Sucursal', array($this, 'club_bi_settings_branch'), 'club_bi', 'service_settings' );
        add_settings_field( 'club_bi_estabishment', 'Código de establecimiento', array($this, 'club_bi_settings_establishment'), 'club_bi', 'service_settings' );
    }

    /**
    * Realiza la validación de los inputs del formulario
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function club_bi_options_validate( $input ) {
    
        return $input;
    }

    /**
    * Registra el título de la sección de configuración
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function service_settings_section_text() {
        echo '<p>Ingresa ambos códigos que te fueron brindados. Recuerda que el código de beneficio lo debes de ingresar en tú cupon.</p>';
    }
    
    /**
    * Registra el campo de Código de Surcursal
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function club_bi_settings_branch() {
        $options = get_option( 'club_bi_options' );
        echo "<input id='club_bi_branch' name='club_bi_options[branch]' type='text' value='" . esc_attr( $options['branch'] ?? '' ) . "' />";
    }

    /**
    * Registra el campo de Código de establecimiento
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function club_bi_settings_establishment() {
        $options = get_option( 'club_bi_options' );
        echo "<input id='club_bi_branch' name='club_bi_options[estabishment]' type='text' value='" . esc_attr( $options['estabishment'] ?? '' ) . "' />";
    }
}