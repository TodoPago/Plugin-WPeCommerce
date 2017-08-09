<?php
if ( ! defined( 'ABSPATH' ) ) exit;

include_once dirname(__FILE__).'/ControlFraude.php';

class ControlFraude_Retail extends ControlFraude{

    protected function completeCFVertical(){
        $payDataOperacion = array();
        $payDataOperacion['CSSTCITY'] = $this->getField($this->customer['shippingcity']);
        $payDataOperacion['CSSTCOUNTRY'] = $this->getField($this->customer['shippingcountry']);
        $payDataOperacion['CSSTEMAIL'] = $this->getField($this->customer['billingemail']); 
        
        $payDataOperacion['CSSTFIRSTNAME'] = $this->getField($this->customer['shippingfirstname']);
        if(empty($payDataOperacion['CSSTFIRSTNAME'])){           
            $payDataOperacion['CSSTFIRSTNAME'] = $this->getField($this->customer['billingfirstname']);
        }
        
        $payDataOperacion['CSSTLASTNAME'] = $this->getField($this->customer['shippinglastname']);
        if(empty($payDataOperacion['CSSTLASTNAME'])){           
            $payDataOperacion['CSSTLASTNAME'] = $this->getField($this->customer['billinglastname']);
        }

        $payDataOperacion['CSSTPHONENUMBER'] = $this->getField(phone::clean($this->customer['billingphone']));
        $payDataOperacion['CSSTPOSTALCODE'] = $this->getField($this->customer['shippingpostcode']);
        $payDataOperacion['CSSTSTATE'] = $this->_getStateCode($this->customer['shippingstate']);
        $payDataOperacion['CSSTSTREET1'] =$this->getField($this->customer['billingaddress']);

        //$payDataOperacion['CSMDD12'] = Mage::getStoreConfig('payment/modulodepago2/cs_deadline');
        //$payDataOperacion['CSMDD13'] = $this->getField($this->order->getShippingDescription());
        //$payData ['CSMDD14'] = "";
        //$payData ['CSMDD15'] = "";
        //$payDataOperacion ['CSMDD16'] = $this->getField($this->order->getCuponCode());
        
        $payDataOperacion = array_merge($this->getMultipleProductsInfo(), $payDataOperacion);

        return $payDataOperacion;
    }

    protected function getCategoryArray($product_id){
        //return Mage::helper('modulodepago2/data')->getCategoryTodopago($product_id);
    }
}
