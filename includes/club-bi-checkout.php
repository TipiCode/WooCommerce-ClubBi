<?php
/**
* Clase principal para interactura con el componente de frontend de club bi
*
* Esta clase es la principal para interactuar con el UI
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/

class ClubBiCheckout{
    // Movemos el contador a una propiedad estática de la clase
    private static $render_count = 0;
    private static $rendered = false; // Nueva variable para control adicional

    /**
    * Retorna el formulario de ingreso para tarjeta de Club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @return string String representando el componente del checkout
    * @since 1.0.0
    */
    public static function get_checkout_component(){
        // Si ya se renderizó, retornar vacío
        if (self::$rendered) {
            return '';
        }

        self::$rendered = true;
        
        if (self::$render_count > 0) {
            return '';
        }
        
        self::$render_count++;
        
        // Encolamos el script
        wp_enqueue_script(
            'club-bi-checkout', 
            plugins_url('../js/club_bi.js', __FILE__), 
            array('jquery'), 
            '1.0.0', 
            true
        );

        // Pasamos las variables necesarias al script
        wp_localize_script('club-bi-checkout', 'clubBiSettings', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
        
        $styles = "<style>
        .club_bi {
            background-color: #003865;
            padding: 1.5rem 1.5rem;
            margin-bottom: 1rem;
            border-bottom-right-radius: 2.5rem;
            border-top-left-radius: 2.5rem;
        }

        .club_bi h1{
            color: #fff;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .club_bi h1 span{
            color: #00C1D4;
        }

        .club_bi .form-row label{
            color: #00C1D4 !important;
        }

        .club_bi .form-row input{
            width: 100%;
            line-height: 1rem;
            border-radius: 10px;
        }

        .club_bi .button_row{
            display: flex;
            justify-content: end;
            align-items: center;
            gap: 10px;
        }

        .club_bi button.validate{
            background-color: #FFB81C !important;
            color: #003865 !important;
            border-bottom-right-radius: 1.8rem !important;
            border-bottom-left-radius: 0px !important;
            border-top-right-radius: 0px !important;
            border-top-left-radius: 1.8rem !important;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .club_bi button.validate:disabled {
            opacity: 0.7 !important;
            cursor: not-allowed !important;
        }

        .club_bi .loader {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: #00C1D4;
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        </style>";

        $template = $styles."<div class='club_bi' id='club_bi_form'>
            <h1>¿Cuentas con tarjeta <span>Club BI</span>?</h1>
            <p class='form-row form-row-wide'>
                <label>Número de Tarjeta</label>
                <input id='cbi_card' type='text' placeholder='0508-5321-8877-1231' class='main_input'></input>
            </p>
            <div class='button_row'>
                <div class='loader' id='club_bi_loader'></div>
                <button type='button' class='validate' id='club_bi_submit'>
                    Obtener mi beneficio
                </button>
            </div>
        </div>";

        return $template;
    }
}