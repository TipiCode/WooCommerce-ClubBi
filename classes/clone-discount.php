<?php
/**
* Clase para interactuar con el descuento de Club BI
*
* Objeto principal para interactuar con un descuento de Club BI
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/ 
class cloneDiscount {
    private $provider;
    private $subtotal;
    private $currency;
    private $discount;
    private $cbi_card;
    private $benefit_code;
    public $code;

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
    * Crea un nuevo DEscuento para ser validado
    * 
    * @throws Exception Si la llamada a club BI falla
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return string HTTP Response Code de la llamada
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */
    public function validate(){
        try{
            $url = 'https://aurora.codingtipi.com/discounts/v1/club-bi/benefits';

            $curl = new Curl(
                $this->provider->user, 
                $this->provider->password,
                $this->provider->branch
            );// Inicializar Curl
         
           
            $discount = $this->get_api_model();//Obtiene objeto en formato JSON como lo requiere Club BI
            
            $response = $curl->execute_post($url, $discount);
            print_r($response);
        
            $curl->terminate();

            $this->code = $response['code'];
            if($this->code == 200){
            
            }else{
                return $response['body']->message;
            }

        } catch (Exception $e) {
			return new WP_Error('error', $e->getMessage());
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
            "discount"  => $this->discount,
            "total"  => $this->subtotal,
            "currency"  => $this->currency,
            "code"  => $this->benefit_code,
            "card"  => $this->cbi_card
        );
    }
}