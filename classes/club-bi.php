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
    public $establishment;
    private static $instance;

    /**
    * Constructor
    */ 
    function __construct() {
        $this->init_actions(); //Inicializa las acciónes del plugin

        //Llena las opciones guardadas dentro del plugin 
        $options = get_option( 'club_bi_options' );
        $this->branch = $options['branch']; //Surcursal
        $this->establishment = $options['estabishment']; //Establecimiento
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
      }
      return self::$instance;
    }

    /**
    * Función para inicialización de acciones requeridas por el plugin
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public function init_actions() {
        if(is_admin()){
            add_action('admin_menu', array($this, 'init_settings_page'));
            add_action( 'woocommerce_coupon_options', array($this, 'init_coupon_fields') );
            add_action( 'woocommerce_coupon_options_save', array($this, 'save_coupon_fields') );
            add_action( 'admin_init', array($this, 'register_settings') );
        }
        add_action( 'woocommerce_review_order_before_payment', array($this, 'init_checkout') );
        add_action('wp_ajax_club_bi_redeem', array($this, 'process_card') );
    }

    /**
    * Función para inicialización de opcion en el menú de settings
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/club-bi
    * @since 1.0.0
    */ 
    public function init_settings_page() {
        add_options_page( 'Club Bi Page', 'Club Bi', 'manage_options', 'club_bi', array($this, 'render_settings_page') );
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
    public function init_checkout(){
        include_once dirname(__FILE__) . '/../includes/club-bi-checkout.php';
        echo ClubBiCheckout::get_checkout_component();
    }

    /**
    * Función para procesar la tarjeta club BI
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @link https://codingtipi.com/project/recurrente
    * @since 1.0.0
    */ 
    public function process_card(){
        error_log('Esta entrando al form');
    }
}