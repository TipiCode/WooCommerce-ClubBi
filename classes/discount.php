<?php
/**
* Clase para interactuar con el descuento de Club BI
*
* Objeto principal para interactuar con un descuento de Club BI
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/ 
class Discount {
    private $provider;
    private $subtotal;
    private $currency;
    private $discount;
    private $cbi_card;
    private $benefit_code;
    public $code;
    private $authorization;  
    private $confirmation;  

    /**
    * Constructor
    *
    * @param WC_Order  $customer_order  Orden de WooCommerce para procesar los datos del producto.
    * 
    */ 
    function __construct($card, $code, $subtotal, $currency, $discount) {
        $this->provider = ClubBi::get_instance();
        $this->subtotal = $subtotal;
        $this->currency = $currency;
        $this->discount = $discount;
        $this->benefit_code = $code;
        $this->cbi_card = $card;
    }

    /**
    * Crea un nuevo Descuento para ser validado
    * 
    * @throws Exception Si la llamada a club BI falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string|true HTTP Response Code de la llamada o true si es exitoso
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */
    public function validate(){
        error_log('Club BI Discount - Iniciando validate()');
        try{
            $url = 'https://aurora.codingtipi.com/benefits/v2/club-bi/discounts';
            
            // Log inicial
            error_log('Club BI - Iniciando validación de descuento');
            
            // Obtenemos el token
            $token = get_option('club_bi_token');
            error_log('Club BI - Token obtenido: ' . ($token ? 'Sí' : 'No'));
            error_log('Club Bi - ' . $token);
            
            if (empty($token)) {
                error_log('Club BI - Error: Token no encontrado');
                throw new Exception('Token de autenticación no encontrado');
            }

            // Inicializamos Curl con los headers necesarios
            $curl = new Curl($token);
            
            // El modelo se mantiene igual porque coincide con la documentación
            $discount = $this->get_api_model();
            error_log('Club BI - Datos a enviar: ' . json_encode($discount));

            $response = $curl->execute_post($url, $discount);
            error_log('Club BI - Respuesta completa: ' . json_encode($response));
            
            $curl->terminate();

            $this->code = $response['code'];

            // Log del código de respuesta
            error_log('Club BI - Código de respuesta: ' . $this->code);
            
            if($this->code == 200 || $this->code == 201){
                // Guardar los códigos de autorización y confirmación
                if (isset($response['body']->authorization)) {
                    $this->authorization = $response['body']->authorization;
                }
                if (isset($response['body']->confirmation)) {
                    $this->confirmation = $response['body']->confirmation;
                }
                
                // Si tenemos una orden, guardamos la metadata
                if ($order_id = WC()->session->get('order_awaiting_payment')) {
                    ClubBi::save_club_bi_metadata(
                        $order_id,
                        $this->authorization,
                        $this->confirmation
                    );
                }

                error_log('Club BI Discount - Finalizando validate()');
                return true;
            }else{
                return $response['body']->message;
            }

        } catch (Exception $e) {
            error_log('Club BI Discount - Error en validate(): ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
    * Obtiene el modelo de un un descuento para poder interactuar con el API de Club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return Array Objeto para uso del API de Club BI
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    private function get_api_model(){
        return Array(
            "discount"  => $this->discount,    // monto del descuento
            "total"  => $this->subtotal,       // monto total
            "currency"  => $this->currency,    // GTQ
            "code"  => $this->benefit_code,    // código del beneficio
            "card"  => $this->cbi_card        // número de tarjeta
        );
    }

    public function get_authorization() {
        return $this->authorization;
    }

    public function get_confirmation() {
        return $this->confirmation;
    }
}