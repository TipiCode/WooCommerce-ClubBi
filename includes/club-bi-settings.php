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
        add_settings_field( 'club_bi_user', 'Usuario', array($this, 'club_bi_settings_user'), 'club_bi', 'service_settings' );
        add_settings_field( 'club_bi_password', 'Contraseña', array($this, 'club_bi_settings_password'), 'club_bi', 'service_settings' );
    }

    /**
    * Realiza la validación de los inputs del formulario y obtiene el token de la API
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @since 1.0.0
    */
    public function club_bi_options_validate($input) {
        // Preparar datos para la API
        $api_data = array(
            'user' => sanitize_text_field($input['user']),
            'password' => sanitize_text_field($input['password']),
            'branch' => intval($input['branch'])
        );

        $url = 'https://aurora.codingtipi.com/benefits/v2/club-bi/setup';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: application/json',
            'Content-Type: application/json'
        ));
        $response = json_decode(curl_exec($ch));
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Realizar llamada a la API
        if($response_code != 200){
            ClubBiSupport::log_error('98', 'club-bi-settings.php', 'Ocurrio un error obteniendo el Token para uso del API.', print_r($response, true));
            add_settings_error(
                'club_bi_options',
                'token_error',
                'No se recibió el token de autenticación.',
                'error'
            );
        }else{
            update_option('club_bi_token', $response->token);
        }

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
    * Registra el campo de usuario
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function club_bi_settings_user() {
        $options = get_option( 'club_bi_options' );
        echo "<input id='club_bi_user' name='club_bi_options[user]' type='text' value='" . esc_attr( $options['user'] ?? '' ) . "' />";
    }

    /**
    * Registra el campo de password
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function club_bi_settings_password() {
        $options = get_option( 'club_bi_options' );
        echo "<input id='club_bi_password' name='club_bi_options[password]' type='password' value='" . esc_attr( $options['password'] ?? '' ) . "' />";
    }
}