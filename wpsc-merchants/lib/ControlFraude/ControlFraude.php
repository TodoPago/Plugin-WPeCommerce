<?php
if ( ! defined( 'ABSPATH' ) ) exit;
include_once dirname(__FILE__).'/phone.php';

abstract class ControlFraude {

	protected $order;
	protected $customer;

	public function __construct($order, $customer){
		$this->order = $order;
		$this->customer = $customer;
	}

	public function getDataCF(){
		$datosCF = $this->completeCF();
		return array_merge($datosCF, $this->completeCFVertical());
	}

	private function completeCF(){
		$payDataOperacion = array();
        $payDataOperacion['AMOUNT'] = $this->order['totalprice'];
        $payDataOperacion['EMAILCLIENTE'] = $this->customer['billingemail'];
		$payDataOperacion['CSBTCITY'] = $this->getField($this->customer['billingcity']);
		$payDataOperacion['CSBTCOUNTRY'] = $this->customer['billingcountry'];
		$payDataOperacion['CSBTCUSTOMERID'] = $this->order['user_ID']; 
		$payDataOperacion['CSBTIPADDRESS'] = ($this->get_the_user_ip() == '::1') ? '127.0.0.1' : $this->get_the_user_ip();
		$payDataOperacion['CSBTEMAIL'] = $this->customer['billingemail'];
		$payDataOperacion['CSBTFIRSTNAME'] = $this->customer['billingfirstname'];
		$payDataOperacion['CSBTLASTNAME'] = $this->customer['billinglastname'];
		$payDataOperacion['CSBTPOSTALCODE'] = $this->customer['billingpostcode'];
		$payDataOperacion['CSBTPHONENUMBER'] = phone::clean($this->customer['billingphone']);
		$payDataOperacion['CSBTSTATE'] =  $this->_getStateCode($this->customer['billingstate']);
		$payDataOperacion['CSBTSTREET1'] = $this->customer['billingaddress'];
		//$payDataOperacion['CSBTSTREET2'] = $this->order->billing_address_2;
		$payDataOperacion['CSPTCURRENCY'] = "ARS";
		$payDataOperacion['CSPTGRANDTOTALAMOUNT'] = number_format($payDataOperacion['AMOUNT'],2,".","");

		if(!empty($this->order['user_ID']) && $this->order['user_ID'] != 0 ) {
	        //CSMDD7 - Fecha Registro Comprador (num Dias) - ver que pasa si es guest
	        $payDataOperacion['CSMDD7'] = $this->_getDateTimeDiff($this->order['user_registered']);
			//CSMDD8 - Usuario Guest? (S/N). En caso de ser Y, el campo CSMDD9 no deber&acute; enviarse
                $payDataOperacion['CSMDD8'] = "S";
			//CSMDD9 - Customer password Hash: criptograma asociado al password del comprador final
                $payDataOperacion['CSMDD9'] = $this->order['user_pass'];
        } else {
                $payDataOperacion['CSMDD8'] = "N";
        }

		return $payDataOperacion;
	}

    protected function _getDateTimeDiff($fecha) {
		return date_diff(DateTime::createFromFormat('Y-m-d H:i:s',$fecha), new DateTime())->format('%a');
    }

    protected function _getStateCode($stateName){
      $array = array(
        "caba" => "C",
        "capital" => "C",
        "ciudad autonoma de buenos aires" => "C",
        "buenos aires" => "B",
        "bs as" => "B",
        "catamarca" => "K",
        "chaco" => "H",
        "chubut" => "U",
        "cordoba" => "X",
        "corrientes" => "W",
        "entre rios" => "R",
        "formosa" => "P",
        "jujuy" => "Y",
        "la pampa" => "L",
        "la rioja" => "F",
        "mendoza" => "M",
        "misiones" => "N",
        "neuquen" => "Q",
        "rio negro" => "R",
        "salta" => "A",
        "san juan" => "J",
        "san luis" => "D",
        "santa cruz" => "Z",
        "santa fe" => "S",
        "santiago del estero" => "G",
        "tierra del fuego" => "V",
        "tucuman" => "T"
      );

      $name = strtolower($stateName);

      $no_permitidas = array("á","é","í","ó","ú");
      $permitidas = array("a","e","i","o","u");
      $name = str_replace($no_permitidas, $permitidas ,$name);

      return isset($array[$name]) ? $array[$name] : 'C';
    }

	private function _sanitize_string($string){
		$string = htmlspecialchars_decode($string);

		$re = "/\\[(.*?)\\]|<(.*?)\\>/i";
		$subst = "";
		$string = preg_replace($re, $subst, $string);

		$replace = array("!","'","\'","\"","  ","$","\\","\n","\r",
			'\n','\r','\t',"\t","\n\r",'\n\r','&nbsp;','&ntilde;',".,",",.","+", "%", "-", ")", "(", "°");
		$string = str_replace($replace, '', $string);

		$cods = array('\u00c1','\u00e1','\u00c9','\u00e9','\u00cd','\u00ed','\u00d3','\u00f3','\u00da','\u00fa','\u00dc','\u00fc','\u00d1','\u00f1');
		$susts = array('Á','á','É','é','Í','í','Ó','ó','Ú','ú','Ü','ü','Ṅ','ñ');
		$string = str_replace($cods, $susts, $string);

		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$string = str_replace($no_permitidas, $permitidas ,$string);
                
                $string = str_replace('#', '', $string);

		return $string;
	}

	protected function getMultipleProductsInfo(){
		global $wpsc_cart;

		$payDataOperacion = array();

        ///datos de la orden separados con #
		$productcode_array = array();
		$description_array = array();
		$name_array = array();
		$sku_array = array();
		$totalamount_array = array();
		$quantity_array = array();
		$price_array = array();

		//print_r($wpsc_cart->total_price); // para el total 

		foreach($wpsc_cart->cart_items as $cart_key => $cart_item_array){
		
			$sku = $cart_item_array->sku;

			$category_list = $cart_item_array->category_list;			

			$product_cat = "default";
			if($category_list && is_array($category_list)){
				$product_cat = $category_list[0];
			}

			$productcode_array[] = $product_cat;

			$descripcion = $this->_setDescription($cart_item_array);

			$description_array[] = $descripcion;

			$name_array[] = str_replace('#', '', $cart_item_array->product_name );
			$sku_array[] = str_replace('#', '', empty($sku) ? $cart_item_array->product_id : $sku);
			$totalamount_array[] = number_format($cart_item_array->total_price,2,".","");
			$quantity_array[] = $cart_item_array->quantity;
			$price_array[] = number_format($cart_item_array->unit_price,2,".","");
		}

		$payDataOperacion['CSITPRODUCTCODE'] = join('#', $productcode_array);
		$payDataOperacion['CSITPRODUCTDESCRIPTION'] = join("#", $description_array);
		$payDataOperacion['CSITPRODUCTNAME'] = join("#", $name_array);
		$payDataOperacion['CSITPRODUCTSKU'] = join("#", $sku_array);
		$payDataOperacion['CSITTOTALAMOUNT'] = join("#", $totalamount_array);
		$payDataOperacion['CSITQUANTITY'] = join("#", $quantity_array);
		$payDataOperacion['CSITUNITPRICE'] = join("#", $price_array);

		return $payDataOperacion;
	}

	public function getField($datasources){
		$return = "";
		//try{
			$return = $this->_sanitize_string($datasources);
		//}catch(Exception $e){
		//}

		return $return;
	}

	protected abstract function getCategoryArray($productId);
        
	protected abstract function completeCFVertical();

	private function _setDescription($cart_item_array){
		$return = "";
		$name = $cart_item_array->product_name;
		$description = $cart_item_array->product_name; // por ahora le pongo el nombre , despues veo de donde sacar correctamente la descripcion del producto y su descripcion corta
		$shortDescription = $cart_item_array->product_name;
	
		if($description == null or empty($description)){
			if($shortDescription == null or empty($shortDescription)){
				$return = strip_tags($name);
				$return = substr($this->_sanitize_string($return),0,50);
			}else{
				$return = strip_tags($shortDescription);
				$return = substr($this->_sanitize_string($return),0,50);
			}
		}else{
			$return = substr($this->_sanitize_string($description),0,50);
		}

        return $return;
	}

	public function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'wpb_get_ip', $ip );
	}

}
