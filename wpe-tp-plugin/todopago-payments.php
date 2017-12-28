<?php

require_once(dirname(__FILE__) . '/Core/vendor/autoload.php');
require_once(dirname(__FILE__) . '/Core/ControlFraude/ControlFraudeFactory.php');
require_once(dirname(__FILE__) . '/lib/db/AdressBook.php');
require_once(dirname(__FILE__) . '/lib/logger.php');


use TodoPago\Core;
use TodoPago\Core\Address\AddressDTO;
use TodoPago\Core\Config\ConfigDTO;
use TodoPago\Core\Customer\CustomerDTO;
use TodoPago\Core\Order\OrderDTO;
use TodoPago\Utils\Constantes;

class WPSC_Payment_Gateway_Todopago_Payments extends WPSC_Payment_Gateway
{
    const TODOPAGO_PLUGIN_VERSION = "1.4.0";
    const TP_FORM_EXTERNO = "ext";
    const TP_FORM_HIBRIDO = "hib";
    const TODOPAGO_DEVOLUCION_OK = 2011;
    const TODOPAGO_FORMS_PROD = "https://forms.todopago.com.ar/resources/v2/TPBSAForm.min.js";
    const TODOPAGO_FORMS_TEST = "https://developers.todopago.com.ar/resources/v2/TPBSAForm.min.js";
    const TODOPAGO_ENVIRONMENT_TEST = "test";
    const TODOPAGO_ENVIRONMENT_PROD = "prod";
    const TODOPAGO_TABLE_TRANSACTION = "todopago_transaction";
    const TODOPAGO_MAXINSTALLMENTS_ENABLED = "1";
    const TODOPAGO_MAXINSTALLMENTS_DISABLED = "0";
    const TODOPAGO_CHECKBOX_ENABLED = "1";
    const TODOPAGO_CHECKBOX_DISABLED = "0";
    const TODOPAGO_PLUGIN_URL = "wp-content/plugins/wpe-tp-plugin/";
    const TODOPAGO_PLUGIN_MADRE = "wp-eCommerce";
    // Estados de las ordenes que utiliza wp-eCommerce
    const TODOPAGO_STATUS_INCOMPLETE_SALE = "1";
    const TODOPAGO_STATUS_ORDER_RECEIVED = "2";
    const TODOPAGO_STATUS_ACCEPTED_PAYMENT = "3";
    const TODOPAGO_STATUS_JOB_DISPATCHED = "4";
    const TODOPAGO_STATUS_CLOSED_ORDER = "5";
    const TODOPAGO_STATUS_PAYMENT_DECLINED = "6";
    const TODOPAGO_MAX_INSTALLMENTS = 12;

    private $payment_capture;

    //General
    private $wpsc_ps;
    private $wpdb;
    private $tpLogger;

    //TP
    private $todopago_environment;
    private $todopago_segment;
    private $todopago_begin_state;
    private $todopago_aprobattion_state;
    private $todopago_denyal_state;
    private $todopago_offline_state;
    private $todopago_store_country;
    private $todopago_currency;
    private $todopago_typecheckout;
    private $todopago_form_timeout_enabled;
    private $todopago_empty_cart_enabled;
    private $todopago_max_installments_enabled;
    private $todopago_max_installments;
    private $todopago_gmaps_validation;
    private $todopago_form_timeout;
    private $todopago_url_success;
    private $todopago_url_pending;
    private $todopago_gaa_response;

    // Core
    private $core;
    private $coreConfig;

    public $location_id;

    /* ===INDEX===
     * Setup Form
     * TodoPago Install
     * Set Order Status
     * Get Order Status
     * Obtain Logger
     * Print Error MSG
     * TODOPAGO COUNTRY
     * TODOPAGO CURRENCY
     * TODOPAGO TYPE CHECKOUT
     * TP STATUS LIST
     * TP LOAD STATUS
     * GET HTTP HEADER
     * TP LOAD NONCE
     * GET CREDENTIALS
     * META BOX TODOPAGO
     * PROCESS (FIRST STEP)
     * SECOND STEP
     * TAKE ACTION
     * BUILD PRODUCTS
     * PREPARE ORDER -> REFACTOR
     * BUILD OPCIONAES
     * BUILD MERCHANT DTO
     * BUILD ORDER DTO
     * BUILD CUSTOMER DTO
     * BUILD ADDRESS DTO
     * PAYMENT FIELDS
     * SETTERS/GETTERS
     */

    public function __construct()
    {
        parent::__construct();

        global $wpsc_purchlog_statuses;

        $this->title = __('Todo Pago', 'todopago');
        $this->supports = array('tev1');
        #$this->sandbox = $this->setting->get('sandbox_mode') == '1' ? true : false;
        #$this->endpoint = $this->sandbox ? $this->endpoints['sandbox'] : $this->endpoints['production'];
        #$this->order_handler = WPSC_Todopago_Payments_Order_Handler::get_instance($this);
        $this->payment_capture = $this->setting->get('payment_capture') !== null ? $this->setting->get('payment_capture') : '';

        //General
        $this->wpsc_ps = $wpsc_purchlog_statuses;

        //TP
        $this->todopago_environment = $this->setting->get("todopago_environment") !== false ? $this->setting->get('todopago_environment') : Constantes::TODOPAGO_PROD;
        $this->todopago_segment = $this->setting->get("todopago_segment") !== false ? $this->setting->get('todopago_segment') : '';
        $this->todopago_begin_state = $this->setting->get("todopago_begin_state") !== false ? $this->setting->get('todopago_begin_state') : '';
        $this->todopago_aprobattion_state = $this->setting->get("todopago_aprobattion_state") !== false ? $this->setting->get('todopago_aprobattion_state') : '';
        $this->todopago_denyal_state = $this->setting->get("todopago_denyal_state") !== false ? $this->setting->get('todopago_denyal_state') : '';
        $this->todopago_offline_state = $this->setting->get("todopago_offline_state") !== false ? $this->setting->get('todopago_offline_state') : '';
        $this->todopago_store_country = "AR";
        $this->todopago_currency = $this->setting->get("todopago_currency") !== false ? $this->setting->get('todopago_currency') : '';
        $this->todopago_typecheckout = $this->setting->get("todopago_typecheckout") !== false ? $this->setting->get("todopago_typecheckout") : Constantes::TODOPAGO_EXT;
        $this->todopago_form_timeout_enabled = $this->setting->get('todopago_form_timeout_enabled') !== false ? $this->setting->get('todopago_form_timeout_enabled') : "no";
        $this->todopago_empty_cart_enabled = $this->setting->get('todopago_empty_cart_enabled') !== false ? $this->setting->get('todopago_empty_cart_enabled') : "no";
        $this->todopago_max_installments_enabled = $this->setting->get('todopago_max_installments_enabled') !== false ? $this->setting->get("todopago_max_installments_enabled") : "no";
        $this->todopago_max_installments = $this->setting->get('todopago_max_installments') == '' ? self::TODOPAGO_MAX_INSTALLMENTS : $this->setting->get('todopago_max_installments');
        $this->todopago_gmaps_validation = $this->setting->get('todopago_gmaps_validation') !== false ? $this->setting->get("todopago_gmaps_validation") : "no";
        $this->todopago_form_timeout = $this->setting->get("todopago_form_timeout") !== false ? $this->setting->get("todopago_form_timeout") : "180000";
        $this->todopago_url_success = $this->setting->get("todopago_url_success") !== false ? $this->setting->get("todopago_url_success") : get_site_url();
        $this->todopago_url_pending = $this->setting->get("todopago_url_pending") !== false ? $this->setting->get("todopago_url_pending") : get_site_url();

        // Define user set variables
        $this->app_id = $this->setting->get('app_id');
        $this->location_id = $this->setting->get('location_id');
        $this->acc_token = $this->setting->get('acc_token');
        $this->todopago_install();
        global $wp_version;
        $this->setCoreConfig(new ConfigDTO($this->todopago_environment, $this->todopago_typecheckout, $this->todopago_form_timeout_enabled, $this->todopago_empty_cart_enabled, $this->todopago_gmaps_validation, self::TODOPAGO_PLUGIN_URL, self::TODOPAGO_PLUGIN_MADRE, WPSC_VERSION, $wp_version, self::TODOPAGO_PLUGIN_VERSION));
        $opcionales = $this->buildOpcionales();
        $this->getCoreConfig()->setArrayOpcionales($opcionales);
        $merchant = $this->buildMerchantDTO();
        $this->core = new Core($this->getCoreConfig(), $merchant);
        $this->core->setTpLogger($this->getTpLogger());

        //General
        $this->wpdb = $this->core->getWpdb();
        $this->tpLogger = $this->core->getTpLogger();

        add_action('wp_ajax_getCredentials', array($this, 'getCredentials')); // executed when logged in
        add_action('wp_ajax_nopriv_getCredentials', array($this, 'getCredentials'));

        $this->cartUrl = $this->get_cart_url();

    }

    public function init()
    {
        parent::init();

        //add_action('wp_enqueue_scripts', array($this, 'square_scripts'));

        add_action("admin_head", array($this, "head_script"));

        // Add hidden field to hold token value
        add_action('wpsc_inside_shopping_cart', array($this, 'te_v1_insert_hidden_field'));
        if (isset($_GET['second_step'])) {
            $this->second_step_todopago();
        }

        # add_action('wpsc_default_credit_card_form_end', array($this, 'load_todopago_checkout'));

        // Add extra zip field to card data for TeV1
        //add_action('wpsc_tev1_default_credit_card_form_end', array($this, 'tev1_add_billing_card_zip'));
        //add_filter('wpsc_default_credit_card_form_fields', array($this, 'tev2_add_billing_card_zip'), 10, 2);

        add_action('wpsc_purchlogitem_metabox_start', array($this, 'meta_box_todopago'), 8);
        add_action('wpsc_purchlogitem_metabox_end', array($this, 'todopago_refund_ui'), 8);

        if (isset($_GET['TodoPago_redirect']) && $_GET["TodoPago_redirect"] == "true" && isset($_GET["order"])) {
            $row = get_post_meta($_GET["order"], 'response_SAR', true);
            $response_SAR = unserialize($row);
            if ($_GET["form"] == "ext") {
                header('Location: ' . $response_SAR["URL_Request"]);
                exit;
            } else {
                $res = array("prk" => $response_SAR["PublicRequestKey"]);
            }
            echo json_encode($res);
            exit;
        }

    }

    public function head_script()
    {
        ?>

        <style>

            #gateway_settings_todopago-payments_form tr:first-of-type {
                display: none
            }
        </style>

        <?php
    }

    /**
     * Load gateway only if PHP 5.3+ and TEv2.
     *
     * @return bool Whether or not to load gateway.
     */
    public function load()
    {
        return version_compare(phpversion(), '5.3', '>=');
    }

    /*
     * FORMULARIO ADMIN
     */
    public function setup_form()
    {

        ?>
        <!-- Account Credentials -->
        <tr>
            <td>Ambiente:</td>
            <td>
                <select id="todopago_environment"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_environment')); ?>">
                    <option value='test' <?php selected('test', $this->setting->get('todopago_environment')); ?>><?php _e('Desarrollo', 'todopago') ?></option>
                    <option value='prod' <?php selected('prod', $this->setting->get('todopago_environment')); ?>><?php _e('Produccion', 'todopago') ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Tipo de segmento:</td>
            <td>
                <select id="todopago_segment"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_segment')); ?>">
                    <option value='' <?php selected('', $this->setting->get('todopago_segment')); ?>></option>
                    <option value='retail' <?php selected('retail', $this->setting->get('todopago_segment')); ?>><?php _e('Retail', 'todopago') ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <h4>Credenciales ambiente desarrollo</h4>
                <p>Obtene los datos de configuracion para tu negocio ingresando con tu cuenta de Todo Pago:</p>
        <tr>
            <td>Mail de TodoPago:</td>
            <td>
                <input id="mail_dev" name="mail_dev" type="text" value=""/>
            </td>
        </tr>
        <tr>
            <td>
                Password:
            </td>
            <td>
                <input id="pass_dev" name="pass_dev" type="password" value=""/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <a id="btn-credentials" class="button" onclick="credentials('dev')">
                    obtener credenciales
                </a>
            </td>
        </tr>
        <tr>
            <td>
                Merchant Id:
            </td>
            <td>
                <input id="todopago_merchant_id_dev"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_merchant_id_dev')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_merchant_id_dev"); ?>"/>
            </td>
        </tr>

        <tr>
            <td>Authorization header:</td>
            <td>
                <input id="todopago_authorization_header_dev"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_authorization_header_dev')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_authorization_header_dev"); ?>"/>
            </td>
        </tr>
        <tr>
            <td>Security:</td>
            <td>
                <input id="todopago_security_dev"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_security_dev')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_security_dev"); ?>"/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Credenciales ambiente producción</h4>
                <p>Obtene los datos de configuracion para tu negocio ingresando con tu cuenta de Todo Pago:</p>
        <tr>
            <td>Mail de TodoPago:</td>
            <td>
                <input id="mail_prod" name="mail_prod" type="text" value=""/>
            </td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input id="pass_prod" name="pass_prod" type="password" value=""/></td>
        </tr>
        <tr>
            <td colspan="2">
                <a id="btn-credentials" class="button" onclick="credentials('prod')">
                    obtener credenciales
                </a>
            </td>
        </tr>
        <tr>
            <td>Merchant Id:</td>
            <td>
                <input id="todopago_merchant_id_prod"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_merchant_id_prod')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_merchant_id_prod"); ?>"/>
            </td>
        </tr>

        <tr>
            <td>Authorization header:</td>
            <td>
                <input id="todopago_authorization_header_prod"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_authorization_header_prod')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_authorization_header_prod"); ?>"/>
            </td>
        </tr>
        <tr>
            <td>Security:</td>
            <td>
                <input id="todopago_security_prod"
                       name="<?php echo esc_attr($this->setting->get_field_name('todopago_security_prod')); ?>"
                       type="text"
                       value="<?php echo $this->setting->get("todopago_security_prod"); ?>"/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Estados del Pedido</h4>
                <p>Datos correspondientes al estado de los pedidos</p>
        <tr>
            <td>Estado cuando la transacción ha sido iniciada</td>
            <td><?php echo $this->tp_status_list('todopago_begin_state'); ?></td>
        </tr>
        <tr>
            <td>Estado cuando la transacción ha sido aprobada</td>
            <td><?php echo $this->tp_status_list('todopago_aprobattion_state'); ?></td>
        </tr>
        <tr>
            <td>Estado cuando la transacción ha sido rechazada</td>
            <td><?php echo $this->tp_status_list('todopago_denyal_state'); ?></td>
        </tr>
        <tr>
            <td>Estado cuando la transacción ha sido offline</td>
            <td><?php echo $this->tp_status_list('todopago_offline_state'); ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Cart Customization</h4>
            </td>
        </tr>
        <tr>
            <td>Store Country</td>
            <td><?php echo $this->todopago_country(); ?></td>
        </tr>
        <tr>
            <td>Currency</td>
            <td><?php echo $this->todopago_currency(); ?></td>
        </tr>

        <tr>
            <td>Tipo de formulario de pago</td>
            <td><?php echo $this->todopago_type_checkout(); ?></td>
        </tr>

        <tr>
            <td>Habilitar tiempo de duración del formulario</td>
            <td>
                <select id="todopago_form_timeout_enabled"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_form_timeout_enabled')); ?>">
                    <option value='si' <?php selected('si', $this->setting->get('todopago_form_timeout_enabled')); ?>><?php _e('si', 'wp-e-commerce') ?></option>
                    <option value='no' <?php selected('no', $this->setting->get('todopago_form_timeout_enabled')); ?>><?php _e('no', 'wp-e-commerce') ?></option>
                </select>
                <p class="description">si no se especifica un tiempo de duración se toma el valor por defecto de 1800000
                    milisegundos (30 minutos) </p>
            </td>
        </tr>
        <tr>
            <td>Tiempo de duración del formulario</td>
            <td>
                <input type="number" id="todopago_form_timeout"
                       name="<?php echo $this->setting->get_field_name("todopago_form_timeout"); ?>"
                       value="<?php echo $this->setting->get("todopago_form_timeout"); ?>"/>
            </td>
        </tr>
        <tr>
            <td>Habilitar máximo de cuotas</td>
            <td>
                <select id="todopago_max_installments_enabled"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_max_installments_enabled')); ?>">
                    <option value='si' <?php selected('si', $this->setting->get('todopago_max_installments_enabled')); ?>><?php _e('si', 'todopago') ?></option>
                    <option value='no' <?php selected('no', $this->setting->get('todopago_max_installments_enabled')); ?>><?php _e('no', 'todopago') ?></option>
                </select>
                <p class="description">Habilita el límite máximo de cuotas a ofrecer.</p>
            </td>
        </tr>
        <tr>
            <td>Limite máximo de cuotas a ofrecer</td>
            <td>
                <select id="todopago_max_installments"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_max_installments')); ?>">
                    <?php
                    for ($i = 1; $i <= self::TODOPAGO_MAX_INSTALLMENTS; $i++) {
                        ?>
                        <option value='<?php echo $i; ?>' <?php selected($i, $this->setting->get('todopago_max_installments')); ?>><?php _e($i, 'wp-e-commerce') ?></option>
                        <?php

                    }
                    ?>
                </select>
                <p class="description">Selecciona el máximo numero de cuotas para tus clientes.</p>
            </td>
        </tr>

        <tr>
            <td>Vaciar carrito cuando una transaccion sea rechazada</td>
            <td>
                <select id="todopago_empty_cart_enabled"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_empty_cart_enabled')); ?>">
                    <option value='si' <?php selected('si', $this->setting->get('todopago_empty_cart_enabled')); ?>><?php _e('si', 'wp-e-commerce') ?></option>
                    <option value='no' <?php selected('no', $this->setting->get('todopago_empty_cart_enabled')); ?>><?php _e('no', 'wp-e-commerce') ?></option>
                </select>
                <p class="description">si está desactivado no limpiará el carrito de compras.</p>
            </td>
        </tr>
        <tr>
            <td>URL Approved Payment</td>
            <td>
                <input name="<?php echo $this->setting->get_field_name("todopago_url_sucess"); ?>" type="text"
                       value="<?php echo $this->setting->get("todopago_url_sucess"); ?>"/>
                <p class="description">This is the URL where the customer is redirected if his payment is approved.</p>
            </td>
        </tr>

        <tr>
            <td>URL Pending Payment</td>
            <td>
                <input name="<?php echo $this->setting->get_field_name("todopago_url_pending"); ?>" type="text"
                       value="<?php echo $this->setting->get("todopago_url_pending"); ?>"/>
                <p class="description">This is the URL where the customer is redirected if his payment is in
                    process.
                </p>
            </td>
        </tr>
        </td>

        <tr>
            <td colspan="2">
                <h4><?php _e('Validar campos con Gmaps', 'todopago'); ?></h4>
            </td>
        </tr>
        <tr>
            <td>Validación Gmaps</td>
            <td><?php //$this->todopago_yes_no('todopago_gmaps_validation');
                ?>

                <select id="todopago_gmaps_validation"
                        name="<?php echo esc_attr($this->setting->get_field_name('todopago_gmaps_validation')); ?>">
                    <option value='si' <?php selected('si', $this->setting->get('todopago_gmaps_validation')); ?>><?php _e('si', 'wp-e-commerce') ?></option>
                    <option value='no' <?php selected('no', $this->setting->get('todopago_gmaps_validation')); ?>><?php _e('no', 'wp-e-commerce') ?></option>
                </select>

            </td>

        </tr>

        <input type="hidden" name="wpnonce" id="wpnonce" value="<?php echo $this->tp_nonce(); ?>"/>
        <?php
        $urlCredentials = plugins_url('lib/js/credentials.js', __FILE__);
        echo '<script type="text/javascript" src="' . $urlCredentials . '"></script>';

        ?>
        <script>

            jQuery(document).ready(function () {

                //Timeout

                if (jQuery("#todopago_form_timeout_enabled").val() == 'no') {
                    jQuery("#todopago_form_timeout").val(0);
                    jQuery("#todopago_form_timeout").hide();
                }

                jQuery("#todopago_form_timeout_enabled").change(function () {
                    if (jQuery("#todopago_form_timeout_enabled").val() == 'si') {
                        jQuery("#todopago_form_timeout").show();
                    } else {
                        jQuery("#todopago_form_timeout").hide();
                    }
                });

                // Máximo de cuotas
                if (jQuery("#todopago_max_installments_enabled").val() == 'no') {
                    jQuery("#todopago_max_installments").val(0);
                    jQuery("#todopago_max_installments").hide();
                }

                jQuery("#todopago_max_installments_enabled").change(function () {

                    if (jQuery("#todopago_max_installments_enabled").val() == 'si') {
                        jQuery("#todopago_max_installments").show();
                    } else {
                        jQuery("#todopago_max_installments").hide();
                    }
                });
            });

        </script>
        <input type="hidden" name="todopago_plugin_version" value="<?php echo self::TODOPAGO_PLUGIN_VERSION; ?>"/>
        <?php
    }


    function test()
    {
        die("55");
    }


    public function te_v1_insert_hidden_field()
    {
        echo '<input type="hidden" id="todopago_card_nonce" name="todopago_card_nonce" value="" />';
    }

    /**
     * TODOPAGO INSTALL
     */
    private function todopago_install()
    {
        $core = new Core();
        $core->todopago_core_install();
    }

    // DATABASE MANAGMENT
    //REFACTOR
    private function tp_setOrderStatus($order, $status)
    {
        $statusData = $this->tp_loadStatus($status);
        if (!is_string($order))
            $order_id = filter_var($order->get("id"), FILTER_SANITIZE_NUMBER_INT);
        else
            $order_id = $order;

        $this->wpdb->query($this->wpdb->prepare("UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET processed = %s, notes = 'Payment Approved by Todo Pago' WHERE `id`= %d LIMIT 1",
            $statusData['order'], $order_id));
    }


    /*
     * HELPERS
     */

    // CODIGO DE ESTADO DE ORDEN
    protected function get_order_status_code($status)
    {
        switch ($status) {
            case 'incomplete_sale':
                return WPSC_Purchase_Log::INCOMPLETE_SALE;
            case 'order_received':
                return WPSC_Purchase_Log::ORDER_RECEIVED;
            case 'accepted_payment':
                return WPSC_Purchase_Log::ACCEPTED_PAYMENT;
            case 'job_dispatched':
                return WPSC_Purchase_Log::JOB_DISPATCHED;
            case 'closed_order':
                return WPSC_Purchase_Log::CLOSED_ORDER;
            case 'declined_payment':
                return WPSC_Purchase_Log::PAYMENT_DECLINED;
            case 'refunded':
                return WPSC_Purchase_Log::REFUNDED;
            case 'refund_pending':
                return WPSC_Purchase_Log::REFUND_PENDING;
            case 'partially_refunded':
                return WPSC_Purchase_Log::PARTIALLY_REFUNDED;
            default:
                return WPSC_Purchase_Log::INCOMPLETE_SALE;
        }
    }
    // END ESTADO ORDEN

    // LOGGER BUILDER
    private function _obtain_logger($php_version, $wpecommerce_version, $todopago_plugin_version, $endpoint, $customer_id, $order_id)
    {

        $this->tpLogger->setPhpVersion($php_version);
        $this->tpLogger->setCommerceVersion($wpecommerce_version);
        $this->tpLogger->setPluginVersion($todopago_plugin_version);
        $this->tpLogger->setEndPoint($endpoint);
        $this->tpLogger->setCustomer($customer_id);
        $this->tpLogger->setOrder($order_id);
        return $this->tpLogger->getLogger(true);
    }
    // END LOGGER BUILDER

    // ERROR PRINTER
    function _printErrorMsg($message = null)
    {
        if ($message != null)
            return "<script> window.addEventListener('load', alert('" . $message . "'), false); </script>";

        return "<script> window.addEventListener('load', alert('Ha ocurrido un eror en la operación, por favor, intente nuevamente'), false); </script>";
    }
    //END ERROR PRINTER

    /*
     * FORMULARIO BUILDERS
     */

    //TODOPAGO COUNTRY
    private function todopago_country()
    {
        $fieldName = "todopago_store_country";

        if ($this->setting->get($fieldName) == null || $this->setting->get($fieldName) == '') {
            $todopago_country = 'AR';
        } else {
            $todopago_country = $this->setting->get($fieldName);
        }

        $sites = array('AR' => 'Argentina');
        $showsites = '<select name="' . esc_attr($this->setting->get_field_name($fieldName)) . '">';

        foreach ($sites as $site_id => $site_name):
            if ($site_id == $todopago_country) {
                $showsites .= '<option value="' . $site_id . '" selected="selected" id="' . $site_id . '">' . $site_name . '</option>';
            } else {
                $showsites .= '<option value="' . $site_id . '" id="' . $site_id . '">' . $site_name . '</option>';
            }
        endforeach;

        $showsites .= '</select>';
        return $showsites;
    }
    // END TODOPAGO COUNTRY

    // TODOPAGO CURRENCY
    private function todopago_currency()
    {
        $fieldNameC = "todopago_store_country";

        if ($this->setting->get($fieldNameC) == null || $this->setting->get($fieldNameC) == '') {
            $todopago_currency = 'Select first one country, save and reload the page to show the currency';
            return $todopago_currency;
        } else {
            $todopago_currency = 'ARS';
            $this->todopago_currency = $todopago_currency;
            return $todopago_currency;
        }
    }
    // END TODOPAGO CURRENCY

    // TODOPAGO TYPE CHECKOUT
    private function todopago_type_checkout()
    {
        $fieldName = "todopago_typecheckout";

        $type_checkout = $this->setting->get($fieldName);
        $type_checkout = $type_checkout === false || is_null($type_checkout) ? self::TP_FORM_EXTERNO : $type_checkout;
        //Type Checkout
        $type_checkout_options = array(
            Constantes::TODOPAGO_EXT => 'Externo',
            Constantes::TODOPAGO_HIBRIDO => 'Integrado en la pagina'
        );

        $select_type_checkout = '<select name="' . esc_attr($this->setting->get_field_name("todopago_typecheckout")) . '" id="' . $fieldName . '">';
        foreach ($type_checkout_options as $k => $select_type):

            $selected = "";
            if ($k == $type_checkout):
                $selected = 'selected="selected"';
            endif;

            $select_type_checkout .= '<option value="' . $k . '" id="type-checkout-' . $k . '" ' . $selected . ' >' . $select_type . '</option>';
        endforeach;
        $select_type_checkout .= "</select>";

        return $select_type_checkout;
    }
    // END TODOPAGO TYPE CHECKOUT

    // TODOPAGO STATUS LIST
    private function tp_status_list($status_field = null)
    {
        if ($this->setting->get($status_field) == null || $this->setting->get($status_field) == '') {
            $todopago_status = 'incomplete_sale';
        } else {
            $todopago_status = $this->setting->get($status_field);
        }

        $show_status_list = '<select name="' . esc_attr($this->setting->get_field_name($status_field)) . '" id="' . $status_field . '">';
        foreach ($this->wpsc_ps as $status) {
            if ($status['internalname'] == $todopago_status) {
                $show_status_list .= '<option value="' . $status['internalname'] . '" selected="selected" id="' . $status['internalname'] . '">' . $status['label'] . '</option>';
            } else {
                $show_status_list .= '<option value="' . $status['internalname'] . '" id="' . $status['internalname'] . '">' . $status['label'] . '</option>';
            }
        }

        $show_status_list .= '</select>';
        return $show_status_list;
    }
    // END TODOPAGO STATUS LIST

    // TODOPAGO LOAD STATUS
    private function tp_loadStatus($status)
    {
        $wpsc_purchlog_statuses = $this->wpsc_ps;

        $returnData = null;

        foreach ($wpsc_purchlog_statuses as $statusData) {
            if ($statusData['internalname'] == $status) {
                $returnData = $statusData;
                break;
            }
        }
        return $returnData;
    }
    //END TODOPAGO LOAD STATUS

    // GET HTTP HEADER
    private function getHttpHeader()
    {
        $esProductivo = $this->setting->get('todopago_environment') == Constantes::TODOPAGO_PROD;
        $http_header = $esProductivo ? $this->setting->get("todopago_authorization_header_prod") : $this->setting->get("todopago_authorization_header_dev");
        $header_decoded = json_decode(html_entity_decode($http_header, TRUE));
        return (!empty($header_decoded)) ? $header_decoded : array("authorization" => $http_header);
    }

    // END HTTP HEADER

    private function tp_nonce()
    {
        return wp_create_nonce('getCredentials');
    }

    /*
     * GET CREDENTIALS
     */

    // CALL A CORE GET CREDENTIALS
    function getCredentials()
    {
        $core = new Core();
        $core->get_credentials();
    }
    // END CALL A CORE GET CREENTIALS


    /*
     * GET STATUS
     */

    // GET STATUS META BOX

    public function meta_box_todopago()
    {
        // desde aca puedo llamar a las funciones de la orden y hacer la devolucion
        $id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
        $order = wpsc_get_order($id, $by = 'id');
        $totalAmount = $order->get_total();
        $originalamount = get_post_meta($id, 'originalamount', true);
        $financial_cost = $totalAmount - $originalamount;

        $this->core->build_todopago_meta_box($financial_cost, $totalAmount, Constantes::TODOPAGO_TODOPAGO, $id);
    }

    // END GET STATUS METABOX

    // REFUNDS
    

    

    public function todopago_refund_ui()
    {
        // desde aca puedo llamar a las funciones de la orden y hacer la devolucion 
        $id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING);
        $order = wpsc_get_order($id, $by = 'id');
        $totalAmount = $order->get_total();
        $originalamount = get_post_meta($order->get('id'), 'originalamount', true);
        $financial_cost = $totalAmount - $originalamount;
        $logger = $this->_obtain_logger(phpversion(), WPSC_VERSION, self::TODOPAGO_PLUGIN_VERSION, $this->setting->get("todopago_environment"), $order->get('sessionid'), $order->get('id'));

        $this->core->setTpLogger($logger);

        if (isset($_REQUEST['refund_amount'])) {
            $refund = filter_var($_REQUEST['refund_amount'], FILTER_SANITIZE_STRING);

            if ($refund > $totalAmount || $refund < "0") {
                $message = '<p class="widefat" ><font color="green">El monto a devolver es inválido</font></p>';
                echo $message;
            } else {
                if ($refund == $totalAmount || empty($refund)) {
                    $refund = "0";
                }

                try {
                    $result = $this->process_refund_method($id, $refund);
                } catch (Exception $e) {
                    echo '<p class="widefat" ><font color="green">' . $e->getMessage() . '</font></p>';
                }
            }
        }

        if (isset($result['StatusMessage'])) {
            $message = '';
            $result_message = $result['StatusMessage'];
            switch ($result['StatusCode']) {
                case '2011':
                    # success
                    $message = '<p class="widefat" ><font color="green">' . $result_message . '</font></p>';
                    break;

                default:
                    # error
                    $message = '<script> alert("' . $result_message . '"); </script>';
                    break;
            }

            echo $message;
        }

        ?>

        <div>
            <form method="post" action="">
                <table class="widefat" cellspacing="1">
                    <tr>
                        <td><h3 class="hndle">Reembolsar con Todo Pago</h3></td>
                    </tr>
                    <tr>
                        <td>Cantidad Devuelta :
                            $<?php echo $this->todo_pago_refunded_amount($_REQUEST['id']); ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Devolucion Parcial - (el monto no debe superar $ <?php echo $originalamount; ?> )</td>
                    </tr>
                    <tr>
                        <td>
                            <input id="refund_amount" type="text" name="refund_amount" value=""/>
                            <input name="partial_refund_todopago" class="button" type="submit"
                                   value="Reembolsar monto ingresado"/>
                            <!--input name="partial_refund_manual" class="button" type="submit" value="Devolver manualmente" /-->
                        </td>
                    </tr>

                </table>
            </form>
        </div>

        <?php
    }
    
    public function todo_pago_refunded_amount($order_id)
    {
        $refunds_json = get_post_meta($order_id, 'refund', true);
        $refunds_arr = json_decode($refunds_json, true);

        $total_refund = 0;
        if (!empty($refunds_arr)) {
            foreach ($refunds_arr as $refund) {
                if ($refund['result'] == Constantes::TODOPAGO_DEVOLUCION_OK) {
                    $total_refund += $refund['amount'];
                }
            }
        }
        return $total_refund;
    }
    
    public function process_refund_method($order_id, $amount = null)
    {
        $orderDTO = new \TodoPago\Core\Order\OrderDTO();
        $orderDTO->setOrderId($order_id);
        $orderDTO->setRefundAmount($amount);

        //$this->core->setOrder($orderDTO);
        $return_response = $this->core->process_refund($orderDTO);

        //Si el servicio no responde según lo esperado, se interrumpe la devolución
        if (!is_array($return_response) || !array_key_exists('StatusCode', $return_response) || !array_key_exists('StatusMessage', $return_response)) {
            throw new Exception("El servicio no responde correctamente");
        }
        if ($return_response['StatusCode'] == Constantes::TODOPAGO_DEVOLUCION_OK) {//sí la devolucion está ok
            //Guardo monto devuelto
            $refunds_json = get_post_meta($order_id, 'refund', true); 
            $arr = json_decode($refunds_json, true); 
            $arr[] = array('amount' => $amount , 'result' => $return_response['StatusCode'] );
            update_post_meta( $order_id, 'refund', json_encode($arr));
            return $return_response;
        } else {
            throw new Exception($return_response["StatusMessage"]);
        }

        return $return_response;
    }

    // END REFUNDS

    /*
     * CICLO DE COMPRA
     */

    // FIRST STEP
    public function process()
    {
        $home = home_url();
        $arrayHome = explode("/", $home);
        $urlSitio = (!empty($this->todopago_url_success) ? $this->todopago_url_success : get_site_url());
        $order_data = $this->purchase_log->get_data();
        $sessionid = $order_data['sessionid'];
        $orderid = $order_data['id'];
        #[2017-11-28T13:47:44+00:00] INFO  [T\Core(getCommerceData:398) | /opt/lampp/htdocs/wpe-dev/wp-content/plugins/wpe-tp-plugin/Core/Core.php] PAYMENT (PHPv.5.6.30 - eCv.3.12.4 - Pv.1.2.0 - EP.test - Cus.9041511876863 - Ord.424) params SAR {"comercio":{"Security":"65bd3e51b73d4cdfb688637b3d98dbf2","EncodingMethod":"XML","Merchant":"16759","URL_OK":"http:\/\/localhost:8080\/wpe-dev?sessionid=9041511876863&second_step=true&order_id=424","URL_ERROR":"http:\/\/localhost:8080\/wpe-dev\/products-page\/checkout\/?sessionid=9041511876863&second_step=true&order_id=424"
        $this->getCoreConfig()->setUrlSuccess($urlSitio . '?' . http_build_query(array_merge($_GET, array('sessionid' => $sessionid, 'second_step' => 'true', 'order_id' => $orderid))));
        $this->getCoreConfig()->setUrlError($arrayHome[0] . '//' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" . '?' . http_build_query(array_merge($_GET, array('sessionid' => $sessionid, 'second_step' => 'true', 'order_id' => $orderid))));
        #$token = sanitize_text_field( $_POST['token'] );
        $this->getCore()->setConfigModel($this->getCoreConfig());
        $this->payment_capture;
        $order = $this->checkout_data->get_data();
        $customerBillingDTO = $this->buildCustomerDTO(Constantes::TODOPAGO_BILLING, $order);
        $customerShippingDTO = $this->buildCustomerDTO(Constantes::TODOPAGO_SHIPPING, $order);
        $logger = $this->_obtain_logger(phpversion(), WPSC_VERSION, self::TODOPAGO_PLUGIN_VERSION, $this->setting->get("todopago_environment"), $this->purchase_log->get('sessionid'), $this->purchase_log->get('id'));
        $this->prepare_order();
        $this->core->setTpLogger($logger);
        $addressBilling = $this->buildAddressDTO(Constantes::TODOPAGO_BILLING, $order);
        $addressShipping = $this->buildAddressDTO(Constantes::TODOPAGO_SHIPPING, $order);
        $products = $this->build_productsDTO();
        $orderDTO = $this->buildOrderDTO($addressBilling, $addressShipping, $products, $this->purchase_log, $customerBillingDTO, $customerShippingDTO);
        try {
            $this->core->setOrderModel($orderDTO);
        } catch (Core\Exception\ExceptionBase $e) {
            echo "Error al setear Orden en Core.\nLINEA: " . $e->getLine() . " " . $e->getMessage();
            $logger->error("LINEA: " . $e->getLine() . " " . $e->getMessage());
            $this->_printErrorMsg('Error al validar datos.');
        }
        try {
            $transactionModel = $this->core->call_sar();
        } catch (Core\Exception\ExceptionBase $e) {
            $logger->error("LINEA: " . $e->getLine() . " " . $e->getMessage());
            $this->_printErrorMsg("Error al validar datos.\n" . $e->getMessage());
        }

        if (isset($transactionModel)) {
            $response = $transactionModel->getResponse();
            if (isset($response)) {
                if ($response->StatusCode == -1) {
                    $this->purchase_log->set('processed', $this->get_order_status_code($this->setting->get('todopago_begin_state')))->save();
                    header('Access-Control-Allow-Origin: *');
                    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
                    get_header();
                    echo '<div class="wrap">';
                    $this->core->initFormulario($transactionModel);
                    echo '</div>';
                    get_footer();
                    exit();
                } else
                    if ($response->StatusCode >= 98000 && $response->StatusCode >= 99000)
                        $this->_printErrorMsg($response->StatusMessage);
                    else
                        $this->_printErrorMsg($response->StatusMessage);
            } else {
                $this->_printErrorMsg();
            }
        } else {
            $this->_printErrorMsg();
        }
        #   $this->function_todopago($order);
    }
    // END FIRST STEP


    // SECOND STEP
    public function second_step_todopago()
    {
        $sessionid = filter_var($_GET['sessionid'], FILTER_SANITIZE_STRING);
        if (key_exists('order_id', $_GET) && key_exists('Answer', $_GET)) {
            $order_id = $_GET['order_id'];
            $tpLog = $this->_obtain_logger(phpversion(), WPSC_VERSION, self::TODOPAGO_PLUGIN_VERSION, $this->setting->get("todopago_environment"), $sessionid, $order_id);
            $this->core->setTpLogger($tpLog);
            $data_GAA = $this->core->call_gaa(intval($order_id));
            $this->take_action($order_id, $data_GAA, $sessionid);
        } else if (key_exists('order_id', $_GET) && !key_exists('Answer', $_GET)) {
            $order_id = $_GET['order_id'];
            $this->take_action($order_id, null, $sessionid);
        } else {
            exit;
        }
    }

    public function take_action($order, $data_GAA, $sessionid)
    {
        global $wpsc_cart;
        $orderLog = wpsc_get_order($order, $by = 'id');
        if (is_null($data_GAA)) {
            $orderLog->set('processed', $this->get_order_status_code($this->setting->get('todopago_denyal_state')))->save();
            do_action('wpsc_payment_failed');
            $this->error_redirect();
        } elseif ($data_GAA["response_GAA"]['StatusCode'] == -1) {
            $this->setTodopagoGaaResponse($data_GAA["response_GAA"]['StatusMessage']);
            $orderLog->set('processed', $this->get_order_status_code($this->setting->get('todopago_aprobattion_state')))->save();
            update_post_meta($orderLog->get('id'), 'originalamount', $orderLog->get_total());
            echo "<h2>Operación " . $order . " exitosa</h2>";
            echo "<script>jQuery('.entry-title').html('Compra finalizada');</script>";
            do_action('wpsc_payment_successful');
            $transaction_url_with_sessionid = add_query_arg('sessionid', $sessionid, get_option('transact_url'));
            wp_redirect($transaction_url_with_sessionid);
            exit;
        } else {
            $orderLog->set('processed', $this->get_order_status_code($this->setting->get('todopago_denyal_state')))->save();
            if ($this->setting->get('todopago_empty_cart_enabled') == 'si')
                $wpsc_cart->empty_cart();
            do_action('wpsc_payment_failed');
            $this->error_redirect($data_GAA);
        }
    }


    public function error_redirect($data_GAA = null)
    {
        echo $this->error_page($data_GAA);
    }

    public function error_page($gaa = null)
    {
        $message = "";
        if (isset($_GET['Error'])) {
            $message = $_GET['Error'];
        }
        if (isset($gaa) && array_key_exists('response_GAA', $gaa)){
            if (array_key_exists('StatusMessage', $gaa['response_GAA']))
                $message = $gaa['response_GAA']['StatusMessage'];
        }
        ?>
        <div class="tp-modal" id="hide">
            <div class="tp-get-status">
                <div class="get-status-content">
                    <a href="#hide" class="tp-close">X</a>
                    <h4>ERROR</h4>
                    <p>
                        Error al realizar la compra, intenta de nuevo.
                    </p>
                    <p>
                        <?php echo $message; ?>
                    </p>
                    <div class="separador"></div>
                </div>
            </div>
        </div>
        <script> console.log("Test"); </script>
        <?php
        include_once dirname(__FILE__) . "/lib/view/error_gaa.php";
    }

    // END SECOND STEP

    // TODO VER SI HACE FALTA

    protected function prepare_order()
    {
        $purchase_log = $this->purchase_log;
        $purchase_log->set('processed', WPSC_Purchase_Log::ORDER_RECEIVED);
        #add_action( 'wpsc_update_purchase_log_status', '_wpsc_action_update_purchase_log_status', 10, 4 );
    }

    /****CORE****/

    private function buildOpcionales()
    {
        $opcionales = Array();
        if ($this->setting->get('todopago_max_installments') == 0)
            $maxCuotas = '';
        else
            $maxCuotas = $this->setting->get('todopago_max_installments');
        $opcionalesBenchmark = array(
            'deadLine' => $this->setting->get('deadline'),
            'timeoutValor' => $this->setting->get('todopago_form_timeout'),
            'maxCuotas' => $maxCuotas,
        );
        foreach ($opcionalesBenchmark as $parametro => $valor) {
            if (!empty($valor))
                $opcionales[$parametro] = $valor;
        }
        return $opcionales;
    }

    private function buildMerchantDTO()
    {
        $http_header = $this->getHttpHeader();
        $esProductivo = $this->setting->get('todopago_environment') == Constantes::TODOPAGO_PROD;
        $apikey = $esProductivo ? $this->setting->get("todopago_security_prod") : $this->setting->get("todopago_security_dev");
        $merchantId = strval($esProductivo ? $this->setting->get('todopago_merchant_id_prod') : $this->setting->get('todopago_merchant_id_dev'));
        $merchant = new TodoPago\Core\Merchant\MerchantDTO();
        $merchant->setMerchantId($merchantId);
        $merchant->setApiKey($apikey);
        $merchant->setHttpHeader($http_header);
        return $merchant;
    }

    protected function build_productsDTO()
    {
        $products = array();
        global $wpsc_cart;
        $items = $wpsc_cart->cart_items;
        foreach ($items as $item) {
            $ProductDTO = new Core\Product\ProductDTO();
            #$product_code = (get_the_terms($value['product_id'], 'product_cat')) ? get_the_terms($value['product_id'], 'product_cat') : 'default';
            $ProductDTO->setProductCode((string)$item->product_id);
            $ProductDTO->setProductDescription($item->product_name);
            $ProductDTO->setProductName($item->product_name);
            if (empty($item->sku) || is_null($item->sku))
                $productSKU = $item->product_name;
            else
                $productSKU = $item->sku;
            $ProductDTO->setProductSKU((string)$productSKU);
            $ProductDTO->setTotalAmount((string)$item->total_price);
            $ProductDTO->setQuantity((string)$item->quantity);
            $ProductDTO->setPrice((string)$item->unit_price);
            $products[] = $ProductDTO;
        }
        return $products;
    }

    protected function buildOrderDTO(AddressDTO $addressBillingDTO, AddressDTO $addressShippingDTO, $products, $order, CustomerDTO $customerBillingDTO, CustomerDTO $customerShippingDTO)
    {
        $orderDTO = new OrderDTO();
        $orderDTO->setOrderId((int)$order->get('id'));
        $orderDTO->setAddressBilling($addressBillingDTO);
        $orderDTO->setAddressShipping($addressShippingDTO);
        $orderDTO->setProducts($products);
        $orderDTO->setTotalAmount($order->get_total());
        $orderDTO->setCustomerBilling($customerBillingDTO);
        $orderDTO->setCustomerShipping($customerShippingDTO);
        return $orderDTO;
    }

    protected function buildCustomerDTO($tipo, $order)
    {
        $customerDTO = new CustomerDTO();
        $customerDTO->setFirstName($order[$tipo . 'firstname']);
        $customerDTO->setLastName($order[$tipo . 'lastname']);
        $customerDTO->setUserEmail($order['billingemail']);
        $customerDTO->setId(0); // si es guest seteo ID=0
        if ($tipo == Constantes::TODOPAGO_BILLING) {
            $user = wp_get_current_user()->data;
            $customerDTO->setId($user->ID);
            $customerDTO->setUserName($user->user_login);
            $customerDTO->setUserPass($user->user_pass);
            $customerDTO->setUserRegistered($user->user_registered);
            $customerDTO->setIpAddress($_SERVER['REMOTE_ADDR']);
        }
        return $customerDTO;
    }

    protected function buildAddressDTO($tipo, $order)
    {
        $addressDTO = new AddressDTO();
        $addressDTO->setCity($order[$tipo . 'city']);
        $addressDTO->setCountry($order[$tipo . 'country']);
        $addressDTO->setPostalCode($order[$tipo . 'postcode']);
        $addressDTO->setPhoneNumber($order['billingphone']); // WC Order no tiene phone para Shipping
        $addressDTO->setState($order[$tipo . 'state']);
        $addressDTO->setStreet($order[$tipo . 'address']);
        return $addressDTO;
    }

    public function payment_fields()
    {
        parent::payment_fields();
        ?>

        <div><img src="https://todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg" /></div>
        
        <?php
    }

    /**
     * @return Core
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @param Core $core
     */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
     * @return Core\Config\ConfigDTO
     */
    public function getCoreConfig()
    {
        return $this->coreConfig;
    }

    /**
     * @param ConfigDTO $coreConfig
     */
    public function setCoreConfig($coreConfig)
    {
        $this->coreConfig = $coreConfig;
    }

    /**
     * @return mixed
     */
    public function getTpLogger()
    {
        return new TodoPagoLogger();
    }

    /**
     * @return mixed
     */
    public function getTodopagoGaaResponse()
    {
        return $this->todopago_gaa_response;
    }

    /**
     * @param mixed $todopago_gaa_response
     */
    public function setTodopagoGaaResponse($todopago_gaa_response)
    {
        $this->todopago_gaa_response = $todopago_gaa_response;
    }
}


?>
