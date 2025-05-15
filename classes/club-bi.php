<?php
/**
* Clase principal para interactuar con el Web Service de Club Bi
*
* Esta clase es la principal para interactuar con la validación de la tarjeta Club BI.
*
* @copyright  2024 - tipi(code)
* @since      1.0.0
*/ 

class ClubBi {
    public $branch;
    public $user;
    public $password;
    private static $instance;
    private static $initialized = false; // Nueva variable para controlar la inicialización

    /**
    * Constructor
    */ 
    private function __construct() { // Hacemos el constructor privado
        // No hacemos nada aquí
    }

    /**
    * Función para patron de singleton
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @return ClubBi Clase inicializada
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            
            // Solo inicializamos una vez
            if (!self::$initialized) {
                self::$instance->init();
                self::$initialized = true;
            }
        }
        return self::$instance;
    }

    /**
    * Nueva función de inicialización
    */ 
    private function init() {
        // Movemos el registro de acciones AJAX fuera del hook de WooCommerce
        if(!is_admin()) {

        }

        add_action('wp_ajax_club_bi_redeem', array($this, 'process_card'));
        add_action('wp_ajax_nopriv_club_bi_redeem', array($this, 'process_card'));
        
        // Mantenemos el resto de las inicializaciones en woocommerce_init
        add_action('woocommerce_init', array($this, 'init_actions'));

        //Llena las opciones guardadas dentro del plugin 
        $options = get_option('club_bi_options');
        $this->branch = $options['branch']; //Surcursal
        $this->user = $options['user']; //Usuario
        $this->password = $options['password']; //Contraseña
    }

    /**
    * Función para inicialización de acciones requeridas por el plugin
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public function init_actions() {
        if(is_admin()){
            add_action('admin_menu', array($this, 'init_settings_page'));
            add_action('woocommerce_coupon_options', array($this, 'init_coupon_fields'));
            add_action('woocommerce_coupon_options_save', array($this, 'save_coupon_fields'), 10, 2);
            add_action('admin_init', array($this, 'register_settings'));
        } else {
            // Dejamos solo el hook del formulario aquí
            add_action('woocommerce_review_order_before_payment', array($this, 'show_form_at_order_summary'), 10);
        }
    }

    /**
    * Función para inicialización de opcion en el menú de settings
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public function init_settings_page() {
        add_options_page('Club Bi Page', 'Club Bi', 'manage_options', 'club_bi', array($this, 'render_settings_page'));
    }

    /**
    * Función para inicialización de página de settings
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public function render_settings_page() {
        ?>
        <h2>Configuración de Club Bi</h2>
        <form action="options.php" method="post">
            <?php 
            settings_fields( 'club_bi_options' );
            do_settings_sections( 'club_bi' ); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
        <?php
    }

    /**
    * Función para llenar la pagina de settings
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    function register_settings() {
        include_once dirname(__FILE__) . '/../includes/club-bi-settings.php';
        $settings = ClubBiSettings::get_instance();
        $settings->register_settings();
    }

    /**
    * Muestra un nuevo campo al área de cupones de WooCommerce
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function init_coupon_fields() {
        woocommerce_wp_text_input( array(
            'id'                => 'benefit_code',
            'label'             => __( 'Código del Beneficio', 'woocommerce' ),
            'placeholder'       => '',
            'description'       => __( 'Código del Beneficio brindado por el banco BI.', 'woocommerce' ),
            'desc_tip'    => true,

        ) );
    }

    /**
    * Guarda nuestro campo personalizado en el área de cupones de WooCommerce
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function save_coupon_fields( $post_id, $coupon ) {
        if( isset( $_POST['benefit_code'] ) ) {
            $coupon->update_meta_data( 'benefit_code', sanitize_text_field( $_POST['benefit_code'] ) );
            $coupon->save();
        }
    }

    /**
    * Muestra el formulario de ingreso para tarjeta de Club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */
    public function show_form_at_order_summary(){
        include_once dirname(__FILE__) . '/../includes/club-bi-checkout.php';
        echo ClubBiCheckout::get_checkout_component();
    }

    /**
    * Procesa la tarjeta Club BI y aplica el descuento
    * 
    * @author Franco A. Cabrera <francocabreradev@gmail.com>
    * @return array|string Respuesta del proceso o mensaje de error
    * @since 1.0.0
    */
    public function process_card() {
        try {
            error_log('Club BI - Iniciando process_card');
            
            // Verificar si los datos POST están llegando
            error_log('Club BI - Datos POST recibidos: ' . print_r($_POST, true));
            
            $card = sanitize_text_field($_POST['cbi_card']);
            error_log('Club BI - Tarjeta recibida: ' . $card);
            
            $total = WC()->cart->get_total('edit');
            error_log('Club BI - Total del carrito: ' . $total);
            
            // Obtener cupón con código de beneficio
            $coupon_data = $this->get_valid_coupon();
            error_log('Club BI - Datos del cupón: ' . print_r($coupon_data, true));
            
            if(empty($coupon_data['benefit']) || empty($coupon_data['coupon'])) {
                wp_send_json_error($this->invalid_coupon(), 400);
                return;
            }
            
            // Obtener el monto del descuento del cupón
            $coupon = new WC_Coupon($coupon_data['coupon']);
            $discount_amount = $coupon->get_amount();
            
            error_log('Club BI - Antes de crear instancia de Discount');
            // Crear instancia de Discount
            $discount = new Discount(
                $card,
                $coupon_data['benefit'],
                $total,
                'GTQ',
                $discount_amount
            );
            error_log('Club BI - Después de crear instancia de Discount');
            
            // Validar el descuento usando la función validate() de la clase Discount
            error_log('Club BI - Antes de validate()');
            $result = $discount->validate();
            error_log('Club BI - Después de validate() - Resultado: ' . ($result === true ? 'true' : $result));
            
            if ($result === true) {
                $this->save_validation_to_session($coupon_data, $discount);
                WC()->cart->apply_coupon($coupon_data['coupon']);
                wp_send_json_success(['message' => 'Beneficio aplicado correctamente']);
            } else {
                wp_send_json_error($result, 400);
            }
            
        } catch (Exception $e) {
            error_log('Club BI - Error en process_card: ' . $e->getMessage());
            wp_send_json_error($e->getMessage(), 400);
        }
    }

    private function save_validation_to_session($coupon_data, Discount $discount) {
        if (!isset($coupon_data['coupon'])) {
            error_log('Club BI - Error: Datos de cupón inválidos');
            return;
        }

        $validated_coupons = WC()->session->get('clubbi_validated_coupons', array());
        $validated_coupons[$coupon_data['coupon']] = [
            'authorization' => $discount->get_authorization(),
            'confirmation' => $discount->get_confirmation(),
            'benefit_code' => $coupon_data['benefit']
        ];
        WC()->session->set('clubbi_validated_coupons', $validated_coupons);
        
        error_log('Club BI - Información guardada en sesión: ' . print_r($validated_coupons, true));
    }

    private function get_valid_coupon() {
        error_log('Club BI - Iniciando get_valid_coupon()');
        
        $coupon_posts = $this->get_benefit_coupons();
        foreach ($coupon_posts as $coupon_post) {
            $coupon_data = $this->validate_coupon_post($coupon_post);
            if ($coupon_data) {
                return $coupon_data;
            }
        }
        
        return ['benefit' => 0, 'coupon' => 0];
    }

    private function get_benefit_coupons() {
        return get_posts([
            'posts_per_page' => -1,
            'post_type'     => 'shop_coupon',
            'post_status'   => 'publish',      
            'meta_query'    => [
                ['key' => 'benefit_code']
            ]
        ]);
    }

    private function validate_coupon_post($coupon_post) {
        $benefit_code = get_post_meta($coupon_post->ID, 'benefit_code', true);
        
        if (empty($benefit_code) || !is_numeric($benefit_code)) {
            error_log('Club BI - Benefit code inválido para cupón ID: ' . $coupon_post->ID);
            return null;
        }

        $coupon = new WC_Coupon($coupon_post->post_title);
        if ($this->is_coupon_valid($coupon)) {
            return [
                'benefit' => $benefit_code,
                'coupon' => $coupon->get_code()
            ];
        }

        return null;
    }

    private function is_coupon_valid($coupon) {
        return $coupon->get_status() === 'publish' && 
               (!$coupon->get_date_expires() || $coupon->get_date_expires()->getTimestamp() > time()) &&
               (!$coupon->get_usage_limit() || $coupon->get_usage_count() < $coupon->get_usage_limit());
    }

    /**
    * Función para alertar al usuario sobre su tarjeta invalida de Club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    private function invalid_card(){
        return "Ops! Tu tarjeta Club Bi es invalida, porfavor ingresala correctamente.";
    }

    /**
    * Función para alertar al usuario sobre beneficio no valido
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    private function invalid_coupon(){
        return "Oops! Parece que no contamos con un beneficio Club BI valido para tu compra";
    }

    /**
     * Guarda la metadata de Club BI en la orden
     * 
     * @author Franco A. Cabrera <francocabreradev@gmail.com>
     * @param int $order_id ID de la orden
     * @param string $authorization Código de autorización de Club BI
     * @param string $confirmation Código de confirmación de Club BI
     * @return void
     * @since 1.0.0
     */
    public static function save_club_bi_metadata($order_id, $authorization, $confirmation) {
        if (!$order_id) {
            error_log('Club BI Plugin - Error: No order ID provided for metadata');
            return;
        }

        // Obtener la orden
        $order = wc_get_order($order_id);
    }
}