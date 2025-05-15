<?php
/**
* Clase para validar cupones de Club BI
*
* Esta clase maneja la validación de cupones que requieren verificación
* a través de la API de Club BI antes de poder ser utilizados
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/

if (!defined('ABSPATH')) {
    exit;
}

class ClubBi_Coupon_Validator {
    
    private $validated_coupons = [];
    
    /**
    * Constructor de la clase
    * 
    * Inicializa los hooks necesarios para la validación de cupones
    * y campos personalizados en la administración
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */
    public function __construct() {
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_clubbi_coupon'), 10, 2);
        add_filter('woocommerce_coupon_is_valid_for_product', array($this, 'validate_clubbi_coupon_for_product'), 10, 4);
        add_filter('woocommerce_coupon_error', array($this, 'prevent_manual_coupon_usage'), 10, 3);
    }
    
    /**
    * Valida si un cupón de Club BI puede ser utilizado
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @param bool $valid Estado actual de validación del cupón
    * @param WC_Coupon $coupon Objeto del cupón a validar
    * @return bool True si el cupón es válido, false en caso contrario
    * @since 1.0.0
    */
    public function validate_clubbi_coupon($valid, $coupon) {
        return $this->validate_clubbi_benefits($valid, $coupon);
    }
    
    /**
    * Valida si un cupón de Club BI puede ser utilizado para un producto específico
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @param bool $valid Estado actual de validación del cupón
    * @param WC_Product $product Objeto del producto
    * @param WC_Coupon $coupon Objeto del cupón
    * @param array $values Valores del producto
    * @return bool True si el cupón es válido, false en caso contrario
    * @since 1.0.0
    */
    public function validate_clubbi_coupon_for_product($valid, $product, $coupon, $values) {
        return $this->validate_clubbi_benefits($valid, $coupon);
    }
    
    /**
    * Lógica central de validación
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @param bool $valid Estado actual de validación del cupón
    * @param WC_Coupon $coupon Objeto del cupón a validar
    * @return bool True si el cupón es válido, false en caso contrario
    * @since 1.0.0
    */
    private function validate_clubbi_benefits($valid, $coupon) {
        $coupon_code = $coupon->get_code();
        
        if ($this->has_validation($coupon_code)) {
            $validation_result = $this->get_validation_result($coupon_code);
            return $validation_result;
        }
        
        $benefit_code = $coupon->get_meta('benefit_code');
        if (!empty($benefit_code)) {
            $validation_data = $this->get_api_validation($coupon_code);
            
            if (!$validation_data || empty($validation_data['authorization'])) {
                $this->set_validation_result($coupon_code, false);
                return false;
            }
        }
        
        $this->set_validation_result($coupon_code, $valid);
        return $valid;
    }
    
    /**
    * Obtiene la información de validación de la API para un cupón
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @param string $coupon_code Código del cupón
    * @return array|false Datos de validación o false si no está validado
    * @since 1.0.0
    */
    private function get_api_validation($coupon_code) {
        if (!WC()->session) {
            return false;
        }
        
        $validated_coupons = WC()->session->get('clubbi_validated_coupons', array());
        $result = isset($validated_coupons[$coupon_code]) ? $validated_coupons[$coupon_code] : false;
        
        return $result;
    }
    
    /**
    * Previene el uso manual de cupones que requieren validación API
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @param string $err Mensaje de error actual
    * @param int $err_code Código de error
    * @param WC_Coupon $coupon Objeto del cupón
    * @return string Mensaje de error modificado
    * @since 1.0.0
    */
    public function prevent_manual_coupon_usage($err, $err_code, $coupon) {
        $benefit_code = get_post_meta($coupon->get_id(), '_clubbi_benefit_code', true);
        
        if (!empty($benefit_code) && !$this->is_api_validated($coupon->get_code())) {
            return __('Este cupón requiere validación a través de una tarjeta Club BI válida.', 'woocommerce-clubbi');
        }
        
        return $err;
    }

    /**
    * Valida el código de cupon
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return bool Cupon validado
    * @since 1.0.0
    */
    private function has_validation($coupon_code) {
        return isset($this->validated_coupons[$coupon_code]);
    }

    /**
    * Obtiene la validación del código de cupon
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @since 1.0.0
    */
    private function get_validation_result($coupon_code) {
        return $this->validated_coupons[$coupon_code] ?? false;
    }

    /**
    * Guarda la validación del código de cupon
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @since 1.0.0
    */
    private function set_validation_result($coupon_code, $result) {
        $this->validated_coupons[$coupon_code] = $result;
    }
}