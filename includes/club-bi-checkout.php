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

    /**
    * Retorna el formulario de ingreso para tarjeta de Club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @return string String representando el componente del checkout
    * @since 1.0.0
    */
    public static function get_checkout_component(){
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
        }

        .club_bi button.validate{
            background-color: #FFB81C !important;
            color: #003865 !important;
            border-bottom-right-radius: 1.8rem !important;
            border-bottom-left-radius: 0px !important;
            border-top-right-radius: 0px !important;
            border-top-left-radius: 1.8rem !important;
            font-weight: 600;
        }
        </style>";

	    $template = $styles."<div class='club_bi'>
            <h1>¿Cuentas con tarjeta <span>Club BI</span>?</h1>
            <p class='form-row form-row-wide'>
                <label>Número de Tarjeta</label>
                <input type='text' placeholder='0508-5321-8877-1231' class='main_input'></input>
            </p>
            <div class='button_row'>
                <button type='button' class='validate'>
                    Obtener mi beneficio
                </button>
            </div>
        </div>";

        return $template;
    }
}