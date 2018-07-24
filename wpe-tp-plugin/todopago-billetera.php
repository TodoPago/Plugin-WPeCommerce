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

class WPSC_Payment_Gateway_Todopago_Billetera extends WPSC_Payment_Gateway_Todopago_Payments{
    private $tpBilletera;
    
    public function __construct() {
        parent::__construct();
        $this->title = __('Billetera checkout', 'todopago');
        $this->tpBilletera="todopago-billetera";
        $this->method_name="billetera";
        $this->autoactivateGateway();
        $this->changeDisplayName();
    }
    
    public function head_script() {
        ?>

        <style>
            #gateway_list_item_todopago-billetera{
                display:none;
            }
        </style>

        <?php
    }
    
    public function payment_fields()
    {
        return;
    }
    
    public function init(){
        parent::init();
        add_filter( 'wpsc_get_gateway_list', array($this,'filter_wpsc_get_gateway_list'), 10, 1 );
    }
    
    public function filter_wpsc_get_gateway_list( $wpsc_filter_merchant_v2_get_gateway_list ) {
        ?>

        <script>   
            window.onload = function(e){ 
                var elemento=document.getElementsByClassName("todopago-billetera")[0];
                var elem_div="<div><img src='<?php echo $this->setting->get("todopago_billetera_banner"); ?>'></div>";
                elemento.insertAdjacentHTML('afterend', elem_div);
            }
        </script>

        <?php
        
        return $wpsc_filter_merchant_v2_get_gateway_list; 
    }
    
    private function autoactivateGateway(){
        $activeOption='custom_gateway_options';
        $activeGateways=get_option($activeOption);
        $tp="todopago-payments";
        $tpActive= in_array($tp,$activeGateways);
        $billeteraActive= in_array($this->tpBilletera,$activeGateways);
        
        if($tpActive){
            if(!$billeteraActive){
                array_push($activeGateways,$this->tpBilletera);
            }
        }else{
            if($billeteraActive){
                $key= array_search($this->tpBilletera,$activeGateways);
                unset($activeGateways[$key]);
            }
        }
        update_option($activeOption,$activeGateways); 
    }
    
    private function changeDisplayName(){
        $valueBilletera="Billetera Virtual Todo Pago";
        $gateways=get_option('payment_gateway_names');
        if($gateways[$this->tpBilletera]!=$valueBilletera){
            $gateways[$this->tpBilletera]=$valueBilletera;
            update_option("payment_gateway_names",$gateways);
        }
    }
}

?>
