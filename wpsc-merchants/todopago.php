<?php
/*
Plugin Name: TODO PAGO
Plugin URI:  https://github.com/TodoPago/Plugin-WPeCommerce
Description: Plug in para la integración con gateway de pago Todo Pago
Version:     1.2.0
Author:      http://www.softtek.com/
Author URI:  http://www.softtek.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit; 

define('TODOPAGO_PLUGIN_VERSION','1.2.0');
define('TP_FORM_EXTERNO', 'ext');
define('TP_FORM_HIBRIDO', 'hib');
define('TODOPAGO_DEVOLUCION_OK', 2011);
define('TODOPAGO_FORMS_PROD','https://forms.todopago.com.ar');
define('TODOPAGO_FORMS_TEST','https://developers.todopago.com.ar');
define('TODOPAGO_ENVIRONMENT_TEST', 'test');
define('TODOPAGO_ENVIRONMENT_PROD', 'prod'); 
define('TODOPAGO_TABLE_TRANSACTION', 'todopago_transaction');
define('TODOPAGO_MAXINSTALLMENTS_ENABLED', '1');
define('TODOPAGO_MAXINSTALLMENTS_DISABLED', '0');
define('TODOPAGO_CHECKBOX_ENABLED', '1');
define('TODOPAGO_CHECKBOX_DISABLED', '0');

// Estados de las ordenes que utiliza wp-eCommerce
define('TODOPAGO_STATUS_INCOMPLETE_SALE', '1');
define('TODOPAGO_STATUS_ORDER_RECEIVED', '2');
define('TODOPAGO_STATUS_ACCEPTED_PAYMENT', '3');
define('TODOPAGO_STATUS_JOB_DISPATCHED', '4');
define('TODOPAGO_STATUS_CLOSED_ORDER', '5');
define('TODOPAGO_STATUS_PAYMENT_DECLINED', '6');

use TodoPago\Sdk as Sdk;

$nzshpcrt_gateways[$num]['name'] = 'Todo Pago';
$nzshpcrt_gateways[$num]['internalname'] = 'todopago';
$nzshpcrt_gateways[$num]['function'] = 'function_todopago'; 
$nzshpcrt_gateways[$num]['form'] = 'form_todopago'; // carga el formulario de config de TP
$nzshpcrt_gateways[$num]['submit_function'] = 'tp_submit_todopago'; // guarda los datos de configuracion de TP
$nzshpcrt_gateways[$num]['payment_type'] = 'tp';
$nzshpcrt_gateways[$num]['display_name'] = 'Todo Pago';
$nzshpcrt_gateways[$num]['class_name'] = 'wpsc_merchant_todopago';


require_once (dirname(__FILE__) . '/lib/vendor/autoload.php');

require_once(dirname(__FILE__) .'/lib/logger.php');
//require_once(dirname(__FILE__) .'/lib/TodoPago/lib/Sdk.php');
require_once(dirname(__FILE__).'/lib/ControlFraude/ControlFraudeFactory.php');

global $tplogger; 
$tplogger = new TodoPagoLogger();

	function form_todopago()
	{   


		$url_sucess = (get_option('todopago_url_sucess') != '')? get_option('todopago_url_sucess'):get_site_url(); 
		$url_pending = (get_option('todopago_url_pending') != '')?get_option('todopago_url_pending'):get_site_url();


		$output.='<tr><td>Ambiente:</td>'; 
		$output.='<td>' .tp_environment_list(). '</td></tr>';

		$output.='<tr><td>Tipo de segmento:</td>'; 
		$output.='<td>' .tp_segment_list(). '</td></tr>';

		$output.='<tr><td colspan="2"><h4>Credenciales ambiente desarrollo</h4>
		<p>Obtene los datos de configuracion para tu negocio ingresando con tu cuenta de Todo Pago:</p>';   
		
		////////// form para obtener credenciales	////////////////////
		$output.= '<tr><td>Mail de TodoPago:</td><td><input id="mail_dev"  name="mail_dev" type="text" value="" /></td></tr>';
		$output.= '<tr><td>Password:</td><td><input id="pass_dev" name="pass_dev" type="password" value="" /></td></tr>';
		$output.='<tr><td colspan="2"><a id="btn-credentials" class="button" onclick="credentials('."'".'test'."'".')" >obtener credenciales</a></td></tr>';

		$output.='<tr><td>Merchant Id:</td>';
		$output.='<td><input id="todopago_merchant_id_dev" name="todopago_merchant_id_dev" type="text" value="'. get_option('todopago_merchant_id_dev') .'"/></td></tr>';
		
		$output.='<tr><td>Authorization header:</td>';
		$output.='<td><input id="todopago_authorization_header_dev" name="todopago_authorization_header_dev" type="text" value="'. get_option('todopago_authorization_header_dev') .'"/></td></tr>';

		$output.='<tr><td>Security:</td>';
		$output.='<td><input id="todopago_security_dev" name="todopago_security_dev" type="text" value="'. get_option('todopago_security_dev') .'"/></td></tr>';

		$output.='<tr><td colspan="2"><h4>Credenciales ambiente producción</h4>	
				  <p>Obtene los datos de configuracion para tu negocio ingresando con tu cuenta de Todo Pago:</p>';
        $output.= '<tr><td>Mail de TodoPago:</td><td><input id="mail_prod"  name="mail_prod" type="text" value="" /></td></tr>';
		$output.= '<tr><td>Password:</td><td><input id="pass_prod" name="pass_prod" type="password" value="" /></td></tr>';
		$output.='<tr><td colspan="2"><a id="btn-credentials" class="button" onclick="credentials('."'".'prod'."'".')" >obtener credenciales</a></td></tr>';

		$output.='<tr><td>Merchant Id:</td>';
		$output.='<td><input id="todopago_merchant_id_prod" name="todopago_merchant_id_prod" type="text" value="'. get_option('todopago_merchant_id_prod') .'"/></td></tr>';
		
		$output.='<tr><td>Authorization header:</td>';
		$output.='<td><input id="todopago_authorization_header_prod" name="todopago_authorization_header_prod" type="text" value="'. get_option('todopago_authorization_header_prod') .'"/></td></tr>';

		$output.='<tr><td>Security:</td>';
		$output.='<td><input id="todopago_security_prod" name="todopago_security_prod" type="text" value="'. get_option('todopago_security_prod') .'"/></td></tr>';

		
		$output.='<tr><td colspan="2"><h4>Estados del Pedido</h4>
					<p>Datos correspondientes al estado de los pedidos</p>';
		$output.= '<tr><td>Estado cuando la transacción ha sido iniciada</td>';			
		$output.='<td>'. tp_status_list('todopago_estado_inicio') .'</td></tr>';

		$output.= '<tr><td>Estado cuando la transacción ha sido aprobada</td>';			
		$output.='<td>'. tp_status_list('todopago_estado_aprobacion') .'</td></tr>';

		$output.= '<tr><td>Estado cuando la transacción ha sido rechazada</td>';			
		$output.='<td>'. tp_status_list('todopago_estado_rechazo') .'</td></tr>';

		$output.= '<tr><td>Estado cuando la transacción ha sido offline</td>';			
		$output.='<td>'. tp_status_list('todopago_estado_offline') .'</td></tr>';

		$output.='<tr><td colspan="2"><h4>Cart Customization</h4></td></tr>';
		$output.='<tr><td>Store Country</td>';
		$output.='<td>'. todopago_country() .'</td></tr>';

		$output.='<tr><td>Currency</td>';
		$output.='<td>'. todopago_currency() .'</td></tr>';

	
		$output.='<tr><td>Tipo de formulario de pago</td>';
		$output.='<td>'. todopago_type_checkout() .'</td></tr>';
		
		$output.='<tr><td>Habilitar tiempo de duracion del formulario</td>';
		$output.='<td>'. todopago_enabled_checkbox('todopago_form_timeout_enabled') .'<p class="description">si no se especifica un tiempo de duración se toma el valor por defecto de 1800000 milisegundos (30 minutos) </p></td></tr>';

		$output.='<tr><td>Tiempo de duracion del formulario</td>';
		$output.='<td><input id="todopago_form_timeout" name="todopago_form_timeout" type="number" value="'. get_option('todopago_form_timeout') .'"/></td></tr>';

		$output.='<tr><td>Limite máximo de cuotas a ofrecer</td>';
		$output.='<td>'. todopago_installments() .'<p class="description">Selecciona el máximo numero de cuotas para tus clientes.</p></td></tr>';

		$output.='<tr><td>Habilitar maximo de cuotas</td>';
		$output.='<td>'. todopago_enabled_checkbox('todopago_max_installments_enabled') .'<p class="description">Habilita el limite máximo de cuotas a ofrecer.</p></td></tr>';

		$output.='<tr><td>Vaciar carrito cuando una transaccion sea rechazada</td>';
		$output.='<td>'. todopago_enabled_checkbox('todopago_empty_cart_enabled') .'<p class="description">si está desactivado no limpiará el carrito de compras.</p></td></tr>';

		$output.='<tr><td>URL Approved Payment</td>';
		$output.='<td><input name="todopago_url_sucess" type="text" value="'. $url_sucess .'"/><p class="description">This is the URL where the customer is redirected if his payment is approved.</p></td></tr>';
		
		$output.='<tr><td>URL Pending Payment</td>';
		$output.='<td><input name="todopago_url_pending" type="text" value="'. $url_pending .'"/><p class="description">This is the URL where the customer is redirected if his payment is in process.</p></td></tr>';
		
		$output.='<input type="hidden" name="wpnonce" id="wpnonce" value="'.tp_nonce().'" />';

		$output.= '
		<script>
			var x = document.getElementsByName("user_defined_name[todopago]");
			x[0].disabled = true;
			console.log(x[0].disabled); 

		</script>
		';


		include_once dirname(__FILE__)."/lib/view/credentialsjs.php";
		
		if (current_user_can('edit_plugins')) {
			$return_values = $output;	
		} else { 
			$return_values = '<tr><td></td><td>No tiene permisos suficientes para editar este plugin</td></tr>';
		}
		return $return_values;
	}

	function tp_submit_todopago()
	{	
		$arr_inputs = ['todopago_environment', 'todopago_merchant_id_dev', 'todopago_merchant_id_prod', 'todopago_authorization_header_dev', 'todopago_authorization_header_prod', 'todopago_security_dev', 'todopago_security_prod', 'todopago_estado_inicio', 'todopago_estado_aprobacion', 'todopago_estado_rechazo', 'todopago_estado_offline', 'todopago_typecheckout', 'todopago_max_installments', 'todopago_url_sucess', 'todopago_url_pending', 'todopago_country'  ];

		foreach ($arr_inputs as $input_name){
			if( isset($_POST[$input_name]) ) {
				update_option($input_name,trim($_POST[$input_name]));
			}	
		}

		if($_POST['todopago_form_timeout'] != null) {
			update_option('todopago_form_timeout',trim($_POST['todopago_form_timeout']));
		}else{ // si es null agrego 1800000 ms
			update_option('todopago_form_timeout','1800000');	
		}

		$arr_checkboxes = ['todopago_form_timeout_enabled', 'todopago_empty_cart_enabled', 'todopago_max_installments_enabled'];
	
		foreach ($arr_checkboxes as $field){
			if(	isset($_POST[$field]) && $_POST[$field] == TODOPAGO_CHECKBOX_ENABLED ) {
				update_option($field,trim($_POST[$field]));
			}else{
				update_option($field, TODOPAGO_CHECKBOX_DISABLED ); 
			}
		}
		
		// si la tabla no esta creada la crea
		todopago_create_transaction_table();

		return true;
	}

	function tp_get_purchase_logs($params = array(array('field'=> "wp_wpsc_purchase_logs.id" , 'value' => '1')) ){
		global $wpdb;
		$where = ' 1=1 ';

		foreach($params as $filter){
			$where .= ' AND '.$filter['field'] ."= '". $filter['value']."'";
		}

		$sql = "SELECT * FROM " . WPSC_TABLE_PURCHASE_LOGS. " left join `wp_users` on `wp_users`.ID = `".WPSC_TABLE_PURCHASE_LOGS. "`.user_ID WHERE ".$where." LIMIT 1" ;

        return $wpdb->get_row( $sql, ARRAY_A);
	}



	//Se ejecuta luego de Finalizar compra -> Realizar el pago
    function tp_first_step_todopago($sessionid = null){
        
        global $wpdb;

        if(isset($_GET["second_step"])){
            //Second Step
            tp_second_step_todopago();
        }else{
        	
        	$purchase_logs = tp_get_purchase_logs( array(array('field' => 'sessionid', 'value' => $sessionid)) ); 
        	
            $purchaseid = $purchase_logs['id'];
          
        	$logger = _tp_obtain_logger(phpversion(), 'wp-ecommerce', TODOPAGO_PLUGIN_VERSION, get_option('todopago_environment'), $sessionid, $purchaseid, true);
            
            tp_prepare_order($purchase_logs, $logger);
            
            $paramsSAR = tp_get_paydata($purchase_logs, $logger);
            $logger->info('params SAR '.json_encode($paramsSAR));

            $response_sar = tp_call_sar($paramsSAR, $logger);

            tp_persistRequestKey($purchaseid, $response_sar["RequestKey"]);

            tp_insert_transaction($purchaseid, $paramsSAR, $response_sar);

          //  custom_commerce($wpdb, $order, $paramsSAR, $response_sar);
		    return $response_sar;                     	
        }

    }

    //Persiste el RequestKey en la DB
    function tp_persistRequestKey($order_id, $request_key){
    //	update_option('request_key', $request_key);
        update_post_meta( $order_id, 'request_key', $request_key);
    }

	function tp_prepare_order($order, $logger){
	    $logger->info('first step');
	    tp_setOrderStatus($order,'estado_inicio');
	}

	function tp_loadStatus($status){
		global $wpsc_purchlog_statuses;

		$returnData = null; 

		foreach ( $wpsc_purchlog_statuses as $statusData){
			if ($statusData['internalname'] == $status){
				$returnData = $statusData;
				break;		
			}
		}

		return $returnData;
	}


	function tp_setOrderStatus($order, $status){
        global $wpdb;

        $statusData = tp_loadStatus($status);
        $order_id = filter_var( $order['id'] , FILTER_SANITIZE_NUMBER_INT);
		
		$wpdb->query( $wpdb->prepare( "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed = %s, notes = 'Payment Approved by Todo Pago' WHERE `id`= %d LIMIT 1",
	        $statusData['order'] , $order_id ) ); 
	}

	function tp_update_amount($order, $amount_buyer){
        global $wpdb;

        $order_id = filter_var( $order['id'] , FILTER_SANITIZE_NUMBER_INT);
		
		// guardo el monto original 
        update_post_meta( $order_id, 'originalamount', $order['totalprice']);

		// actualizo monto total pagado
		$wpdb->query( $wpdb->prepare( "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET totalprice = %s  WHERE `id`= %d LIMIT 1",
	        $amount_buyer , $order_id ) ); 
	}

	function getUserInfo($order_id){
		global $wpdb; 

		$userinfo = array();
		$usersql = $wpdb->prepare( "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value,
		`".WPSC_TABLE_CHECKOUT_FORMS."`.`name`,
		`".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM
		`".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN
		`".WPSC_TABLE_SUBMITED_FORM_DATA."` ON
		`".WPSC_TABLE_CHECKOUT_FORMS."`.id =
		`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE
		`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`= %d",
        $order_id);

		foreach ($wpdb->get_results($usersql, ARRAY_A) as $item ){
			$userinfo[$item['unique_name']] = $item['value'];
		}

		return $userinfo;
	}


	function tp_get_paydata($order, $logger){
        global $wpdb;

		$userinfo = getUserInfo($order['id']);

		// se crea una instancia de ControlFraude.php
        $controlFraude = ControlFraudeFactory::get_ControlFraude_extractor('Retail', $order, $userinfo);
       	
        $datosCs = $controlFraude->getDataCF();

        $sessionid = $order['sessionid'];
		        
        $home = home_url();

        $arrayHome = explode("/", $home);

        $return_URL_ERROR = $arrayHome[0].'//'."{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}". '?' . http_build_query(array_merge($_GET, array('sessionid'=> $sessionid, 'second_step' => 'true')));

        $return_URL_OK = get_option('todopago_url_sucess') . '?' . http_build_query(array_merge($_GET, array('sessionid'=> $sessionid, 'second_step' => 'true')));

        $esProductivo = get_option('todopago_environment') == "prod";
        
        $optionsSAR_comercio = getOptionsSARComercio($esProductivo, $return_URL_OK,$return_URL_ERROR);
        
        $optionsSAR_operacion = getOptionsSAROperacion($esProductivo, $order);
              
        $optionsSAR_operacion = array_merge_recursive($optionsSAR_operacion, $datosCs);
 
        $paramsSAR['comercio'] = $optionsSAR_comercio;
        $paramsSAR['operacion'] = $optionsSAR_operacion;

        return $paramsSAR;
    }

    function getOptionsSARComercio($esProductivo, $return_URL_OK, $return_URL_ERROR){
	    return array (
	        'Security'      => $esProductivo ? get_option('todopago_security_prod') : get_option('todopago_security_dev'),
	        'EncodingMethod'=> 'XML',
	        'Merchant'      => strval($esProductivo ? get_option('todopago_merchant_id_prod') : get_option('todopago_merchant_id_dev') ),
	        'URL_OK'        => $return_URL_OK,
	        'URL_ERROR'     => $return_URL_ERROR
	    ); 
	}

    function getOptionsSAROperacion($esProductivo, $order){

        $arrayResult = array ( 
            'MERCHANT'    => strval($esProductivo ? get_option('todopago_merchant_id_prod') : get_option('todopago_merchant_id_dev')),
            'OPERATIONID' => strval($order['id']),
            'CURRENCYCODE'=> '032', //Por el momento es el único tipo de moneda aceptada
        ); 
        // setea max de cuotas si es que se esta habilitada la opcion 
        if(get_option('todopago_max_installments_enabled') == 1){
            $arrayResult['MAXINSTALLMENTS']  =  strval(get_option('todopago_max_installments') );
        }
       	
       	// setea tiempo de duracion del form si es que se esta habilitada la opcion 
        if(get_option('todopago_form_timeout_enabled') == 1){
            $arrayResult['TIMEOUT']  =  strval(get_option('todopago_form_timeout') );
        }
       	
       	return $arrayResult;
    }

    function tp_call_sar($paramsSAR, $logger){
             
        $logger->debug(tp_call_sar);
        $esProductivo = get_option('todopago_environment') == "prod";
        $http_header = getHttpHeader();
        
        $logger->debug("http header: ".json_encode($http_header));
        $connector = new Sdk($http_header, get_option('todopago_environment'));
        
        $logger->debug("Connector: ".json_encode($connector));
        $response_sar = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
        $logger->info('response SAR '.json_encode($response_sar));

        if($response_sar["StatusCode"] == 702 && !empty($http_header) && !empty($paramsSAR['comercio']['Merchant']) && !empty($paramsSAR['comercio']['Security'])){
            $response_sar = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
            $logger->info('reintento');
            $logger->info('response SAR '.json_encode($response_sar));
        }

        return $response_sar;
    }

 	function getHttpHeader(){
        $esProductivo = get_option('todopago_environment') == "prod";
        $http_header = $esProductivo ? get_option('todopago_authorization_header_prod') : get_option('todopago_authorization_header_dev');
        $header_decoded = json_decode(html_entity_decode($http_header,TRUE));
        return (!empty($header_decoded)) ? $header_decoded : array("authorization" => $http_header);
    }

	function function_todopago($seperator, $sessionid){
		
		global $wpdb, $wpsc_cart;

		$response_sar = tp_first_step_todopago($sessionid );
		
		$link = $response_sar['URL_Request'];			
		$title = '';
		$url_img = '';
		$img_banner = '';
		$button = '';
		$html ='';
		if($response_sar["StatusCode"] == -1){

			if (get_option('todopago_typecheckout') == TP_FORM_EXTERNO) {
				//title
				$title = 'Continue pagando con Todo Pago';
				//add image
				$url_img = "http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg";
				
				$img_banner = '<img src="'.$url_img.'" alt="Todo Pago" title="Todo Pago" />'; 

				$button = '<form action="'.$link.'" method="post" id="todopago_payment_form">
					<div style=" width: 500px;">
 						<div style="float:right;"><input type="submit" class="button-alt" id="submit_todopago_payment_form" value="Pagar con TodoPago">
 						</div>				
 						<div style="float:left;"><a class="button cancel" href="'. $_SERVER['HTTP_REFERER'] .'"> Cancelar orden </a>
 						</div>
 					</div>	
 				</form>';

				$html = '<div style="width:50%; float:center; margin:0 auto;" >';
				$html .= '<div style="margin: 0 auto; width: 100%; ">';
				$html .= '<h3>' . $title . '</h3>';
				$html .= '<p>' . $img_banner . '</p>';
				$html .= $button;
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</br></br>';
			}
			else {

                $purchase_logs = tp_get_purchase_logs( array(array('field' => 'sessionid', 'value' => $sessionid)) ); 

                $purchaseid = $purchase_logs['id'];

                $logger = _tp_obtain_logger(phpversion(), 'wp-ecommerce', TODOPAGO_PLUGIN_VERSION, get_option('todopago_environment'), $sessionid, $purchaseid, true);

                $paramsSAR = tp_get_paydata($purchase_logs, $logger);

			    $basename = plugin_basename(dirname(__FILE__));
			  
			    $baseurl = plugins_url();
			   	$form_dir = "{$baseurl}/{$basename}/lib/view/formulario-hibrido";
               
			    $firstname = $paramsSAR['operacion']['CSSTFIRSTNAME'];
			    $lastname = $paramsSAR['operacion']['CSSTLASTNAME'];
			    $email = $paramsSAR['operacion']['CSSTEMAIL'];
			    $merchant = $paramsSAR['operacion']['MERCHANT'];
			    $amount = $paramsSAR['operacion']['CSPTGRANDTOTALAMOUNT'];
			    $prk = $response_sar['PublicRequestKey'];

			    $home = home_url();
			    $arrayHome = explode ("/", $home); 
        
        		$return_URL_ERROR = $arrayHome[0].'//'."{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}". '?' . http_build_query(array_merge($_GET, array('sessionid'=> $sessionid, 'second_step' => 'true')));
        
		        $return_URL_OK = get_option('todopago_url_sucess') . '?' . http_build_query(array_merge($_GET, array('sessionid'=> $sessionid, 'second_step' => 'true')));

			  	//$logger->info('ReturnURL '.$returnURL);
			    $env_url = (get_option('todopago_environment') == "prod" ? TODOPAGO_FORMS_PROD : TODOPAGO_FORMS_TEST);

				add_filter('show_admin_bar', '__return_false');

				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
				header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
			    get_header();
			    require 'lib/view/formulario-hibrido/formulario.php';		
				get_footer();
				exit();
				
			}
		}else{
			echo _printErrorMsg();
		}

		//show page 
		get_header();	
		echo $html;
		get_footer();		
		exit;	
	}

	//Se ejecuta luego de pagar con el formulario
    function tp_second_step_todopago(){

        global $wpsc_cart, $data_GAA; 

		$sessionid = filter_var($_GET['sessionid'], FILTER_SANITIZE_STRING);
    	$purchase_logs = tp_get_purchase_logs( array(array('field' => 'sessionid', 'value' =>  $sessionid)) );
    	
        if(isset($purchase_logs['id'])){
            $order_id = $purchase_logs['id']; 

            if($purchase_logs['gateway'] == 'todopago'){      
            	$logger = _tp_obtain_logger(phpversion(), 'wp-ecommerce', TODOPAGO_PLUGIN_VERSION, get_option('todopago_environment'), $_GET['sessionid'], $order_id, true);
                $data_GAA = tp_call_GAA($order_id, $logger);
            
                tp_take_action($purchase_logs, $data_GAA, $logger);               
            }
        }

    }


    function tp_call_GAA($order_id, $logger){ 

            $logger->info('second step _ ORDER ID: '.$order_id);
            $request_key = get_post_meta($order_id, 'request_key', true);       
            $esProductivo = get_option('todopago_environment') == "prod";

            $params_GAA = array (     
                'Security'   => $esProductivo ? get_option('todopago_security_prod') : get_option('todopago_security_dev'),      
                'Merchant'   => strval($esProductivo ? get_option('todopago_merchant_id_prod') : get_option('todopago_merchant_id_dev') ),     
                'RequestKey' => $request_key,     
                'AnswerKey'  => filter_var($_GET['Answer'], FILTER_SANITIZE_STRING)
            );

            $logger->info('params GAA '.json_encode($params_GAA));

            //$esProductivo =  $this->ambiente == "prod"; 
            $http_header = getHttpHeader();
            $logger->info("HTTP_HEADER: ".json_encode($http_header));
            $connector = new Sdk($http_header, get_option('todopago_environment')); 

           // $logger->info("PARAMETROS GAA: ".json_encode($params_GAA));
            //$logger->info("PARAMETROS GAA: ".json_encode($params_GAA));

            $response_GAA = $connector->getAuthorizeAnswer($params_GAA);
            $logger->info('response GAA '.json_encode($response_GAA));

            $data_GAA['params_GAA'] = $params_GAA;
            $data_GAA['response_GAA'] = $response_GAA;

            return $data_GAA;    
    }


    function tp_update_transaction($order, $data_GAA, $logger){
    	global $wpdb;
    	$logger->info('tp_update_transaction order_id:' . $order['id'] );
    	$wpdb->update( 
	        $wpdb->prefix.TODOPAGO_TABLE_TRANSACTION,
	        array(
	            'second_step'=>date("Y-m-d H:i:s"), // string
	            'params_GAA'=>json_encode($data_GAA['params_GAA']), // string
	            'response_GAA'=>json_encode($data_GAA['response_GAA']), // string
	            'answer_key'=>$data_GAA['params_GAA']['AnswerKey'] //string
	        ),
	        array('id_orden'=> $order['id'] ), // int
	        array(
	            '%s',
	            '%s',
	            '%s',
	            '%s'
	        ),
	        array('%d')
	    );

    }


    function tp_take_action($order, $data_GAA, $logger){
		global $wpsc_cart, $data_GAA;
        // hago un GAA o un getstatus 
        $request_GAA = $data_GAA['response_GAA']['Payload']['Request'];
        $request_GAA['FINANCIALCOST'] = $request_GAA['AMOUNTBUYER'] - $request_GAA['AMOUNT']; 



	    tp_update_transaction($order, $data_GAA, $logger);

	    if ($data_GAA['response_GAA']['StatusCode']== -1){
	    	// seteo estado de orden aprobada
			tp_setOrderStatus($order, get_option('todopago_estado_aprobacion') );
			tp_update_amount($order, $request_GAA['AMOUNTBUYER']);
			$wpsc_cart->empty_cart();
	    }else{
          	if (get_option('todopago_empty_cart_enabled')){	$wpsc_cart->empty_cart(); } 

	    	$message = '';
	    	if(isset($_GET['Error'])){
	    		$message .= $_GET['Error'];
    		}

	        tp_setOrderStatus($order, get_option('todopago_estado_rechazo') );
 
	        if(isset($data_GAA['response_GAA']['StatusMessage'])){
	        	$message .= $data_GAA['response_GAA']['StatusMessage'];
	        }
	        echo _printErrorMsg($message);

	    }
	}


	function _printErrorMsg($message=null){
	if($message != null)
	    	return "<script> window.addEventListener('load', alert('". $message ."'), false); </script>";

	return "<script> window.addEventListener('load', alert('Ha ocurrido un eror en la operación, por favor, intente nuevamente'), false); </script>";
    }

	function tp_insert_transaction( $order_id, $paramsSAR, $response_sar){
		global $wpdb;
		
		$wpdb->insert(
		    $wpdb->prefix . TODOPAGO_TABLE_TRANSACTION, 
		    array('id_orden'=>$order_id,
		          'params_SAR'=>json_encode($paramsSAR),
		          'first_step'=>date("Y-m-d H:i:s"),
		          'response_SAR'=>json_encode($response_sar),
		          'request_key'=>$response_sar["RequestKey"],
		          'public_request_key'=>$response_sar['PublicRequestKey']
		         ),
		    array('%d','%s','%s','%s','%s')
		);
	}


	function tp_retorno(){
		if( isset($_GET['second_step']) ){
			tp_second_step_todopago();
		}
	}


	function tp_nonce() {
		return wp_create_nonce( 'getCredentials');
	}

	function tp_environment_list(){
		$environment = get_option('todopago_environment');
		$options = array('test'=> 'Desarrollo' , 'prod'=>'Producción');
		$show_environment_select = '<select name="todopago_environment">';

		foreach ($options as $k => $val){
			$selected = ($k == $environment )? ' selected="selected" ':'';
	    	$show_environment_select .= '<option value="'.$k.'" '.$selected.' >'.$val.'</option>';
		}
		$show_environment_select .= '</select>';

		return $show_environment_select;
	}


	function tp_segment_list(){
		$segment = get_option('todopago_segment');
		$options = array('retail'=> 'Retail');
		$show_segment_select = '<select name="todopago_segment">';

		foreach ($options as $k => $val){
			$selected = ($k == $environment )? ' selected="selected" ':'';
	    	$show_segment_select .= '<option value="'.$k.'" '.$selected.' >'.$val.'</option>';
		}
		$show_segment_select .= '</select>';

		return $show_segment_select;

	}



	function tp_status_list( $status_field = null)
	{
		global $wpsc_purchlog_statuses;

		if (get_option($status_field) == null || get_option($status_field) == ''){
			$todopago_status = 'incomplete_sale';        
		} else {
			$todopago_status = get_option($status_field);  
		}
		
		$show_status_list= '<select name="'.$status_field.'" id="'.$status_field.'">';

		foreach ( $wpsc_purchlog_statuses as $status){
			if( $status['internalname'] == $todopago_status){
				$show_status_list .= '<option value="'. $status['internalname'] .'" selected="selected" id="'. $status['internalname'] .'">'. $status['label'] .'</option>'; 
			} else {
				$show_status_list .= '<option value="'.$status['internalname'].'" id="'.$status['internalname'].'">'.$status['label'].'</option>';    
			}        

		}
		
		$show_status_list .= '</select>';
		return $show_status_list;
	}




	function todopago_country(){
	
	
		if (get_option('todopago_country') == null || get_option('todopago_country') == ''){
			$todopago_country = 'AR';        
		} else {
			$todopago_country = get_option('todopago_country');  
		}
		
		$sites = array('AR' =>'Argentina');
		
		$showsites= '<select name="todopago_country">';

		foreach ($sites as $site_id => $site_name):
			if($site_id == $todopago_country){
				$showsites .= '<option value="'.$site_id.'" selected="selected" id="'.$site_id.'">'.$site_name.'</option>'; 
			} else {
				$showsites .= '<option value="'.$site_id.'" id="'.$site_id.'">'.$site_name.'</option>';    
			}         
		endforeach;
		
		$showsites .= '</select>';
		return $showsites;

	}

	function todopago_currency(){

		if (get_option('todopago_country') == null || get_option('todopago_country') == ''){
			$todopago_currency = 'Select first one country, save and reload the page to show the currency';    
			return $todopago_currency;
		}else{	

			return $todopago_currency = 'ARS';
			
		}
	}


	function todopago_installments(){
	
		if (get_option('todopago_max_installments') == null || get_option('todopago_max_installments') == ''){
			$todopago_max_installments = 24;        
		} else {
			$todopago_max_installments = get_option('todopago_max_installments');  
		}
		
		$times = array('1','2','3','4','5','6','7','8','9', '10', '11', '12');
		$showinstallment = '<select name="todopago_max_installments">';
		
		foreach ($times as $installment):
			if($installment == $todopago_max_installments){
				$showinstallment .= '<option value="'.$installment.'" selected="selected">'.$installment.'</option>'; 
			} else {
				$showinstallment .= '<option value="'.$installment.'">'.$installment .'</option>';    
			}         
		endforeach;
		
		$showinstallment .= '</select>';
		
		return $showinstallment;
	}
	
	function todopago_enabled_installments(){
		
		$showinstallment = '<input type="checkbox" name="todopago_max_installments_enabled" value="'.TODOPAGO_MAXINSTALLMENTS_ENABLED.'" ';
	
		if (get_option('todopago_max_installments_enabled') == TODOPAGO_MAXINSTALLMENTS_ENABLED ){
			$showinstallment .= ' checked ';        
		} 

		$showinstallment .= '/>';
		
		return $showinstallment;
	}

	/*
	*	$field is a field-name of form configuration 
	*/
	function todopago_enabled_checkbox($field){
		
		$showinstallment = '<input type="checkbox" name="'. $field.'" value="'.TODOPAGO_CHECKBOX_ENABLED.'" ';
	
		if (get_option($field) == TODOPAGO_CHECKBOX_ENABLED ){
			$showinstallment .= ' checked ';        
		} 

		$showinstallment .= '/>';
		
		return $showinstallment;
	}



	function todopago_type_checkout(){
		
		$type_checkout = get_option('todopago_typecheckout');
		$type_checkout = $type_checkout === false || is_null($type_checkout) ? TP_FORM_EXTERNO : $type_checkout;

		//Type Checkout
		$type_checkout_options = array(
			TP_FORM_EXTERNO => 'Externo',
			TP_FORM_HIBRIDO => 'Integrado en la pagina'
		);		
		
		$select_type_checkout = '<select name="todopago_typecheckout" id="todopago_typecheckout">';

		foreach($type_checkout_options as $k => $select_type):
		
			$selected = "";
			if($k == $type_checkout):
				$selected = 'selected="selected"';
			endif;
			
			$select_type_checkout .= '<option value="' . $k . '" id="type-checkout-' . $k . '" ' . $selected . ' >' . $select_type . '</option>';
		endforeach;
		$select_type_checkout .= "</select>";
		
		return $select_type_checkout;
	}

	function _tp_obtain_logger(
		$php_version, 
		$wpecommerce_version, 
		$todopago_plugin_version, 
		$endpoint, 
		$customer_id, 
		$order_id, 
		$is_payment
		)
	{
        global $tplogger, $wpsc_cart; 
        $tplogger->setPhpVersion($php_version);
        $tplogger->setCommerceVersion($wpecommerce_version);
        $tplogger->setPluginVersion($todopago_plugin_version);
        $tplogger->setEndPoint($endpoint);
        $tplogger->setCustomer($customer_id);
        $tplogger->setOrder($order_id);

        return  $tplogger->getLogger(true);
    }


	function tp_getSecurity(){
		$environment = get_option('todopago_environment');
		return ($environment == TODOPAGO_ENVIRONMENT_TEST)? get_option('todopago_security_dev'):get_option('todopago_security_prod');
	}

	function tp_getMerchant(){
		$environment = get_option('todopago_environment');
		return ($environment == TODOPAGO_ENVIRONMENT_TEST)? get_option('todopago_merchant_id_dev'):get_option('todopago_merchant_id_prod');
	}

	function process_refund( $order_id, $amount = null, $total_refund=0 ){
			$return_response ='';
            $logger = _tp_obtain_logger(phpversion(), 'wp-ecommerce', TODOPAGO_PLUGIN_VERSION, get_option('todopago_environment'), tp_getMerchant(), $order_id, true);
			$amount = filter_var( $amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
            //configuración común a ambos servicios.
            $options_return = array(
                    "Security" => tp_getSecurity(),
                    "Merchant" => tp_getMerchant(),
                    "RequestKey" => get_post_meta($order_id, 'request_key', true)
            );

            //Intento instanciar la Sdk, si la configuración está mal, le avisará al usuario.
            try {
            	$http_header = getHttpHeader();
		        $logger->debug("http header: ".json_encode($http_header));
        		$connector = new Sdk($http_header, get_option('todopago_environment'));
            }
            catch (Exception $e) {
                $logger->warn("Error al crear el connector, ", $e);           
            //	throw new Exception("Revise la configuarción de TodoPago");
            	return array('result' => 0 , 'message' => '"Revise la configuarción de TodoPago" - ErrorException : ' . $e->getMessage() );
            }


            $order = tp_get_purchase_logs(array(array('field'=>WPSC_TABLE_PURCHASE_LOGS.'.id' , 'value' => $order_id )));

	     	$totalAmount = get_post_meta($order_id, 'originalamount', true); 

            if(empty($amount) || $amount == 0 || $amount > $totalAmount){
            	// throw new Exception("El monto esta vacio o es invalido.");
            	return array('result' => 0 , 'message' => "El monto es invalido. Ingrese un monto menor o igual a $". $totalAmount );
            }

            if($total_refund){
            	//Intento realizar la devolución
                try {
                    $logger->info("Parametros devolucion Total voidRequest : " . var_export($options_return ,true) );
                    $return_response = $connector->voidRequest($options_return);
                    $logger->info("Se hace devolucion Total voidRequest : " . var_export($return_response ,true) );
                    // guardo monto devuelto
                    $refunds_json = get_post_meta($order_id, 'refund', true); 
    				$arr = json_decode($refunds_json, true); 
                    $arr[] = array('amount' => $totalAmount , 'result' => $return_response['StatusCode'] );
                  
                    update_post_meta( $order_id, 'refund', json_encode($arr));
                }
                catch (Exception $e) {
                    $logger->error("Falló al consultar el servicio: ", $e);
                	// guardo como metadato el monto devuelto
                    return array('result' => 0 , 'message' => "Falló al consultar el servicio:" . $e->getMessage() );

                }
            }else{
                $logger->info("Pedido de devolución por $amount pesos de la orden $order_id");
                $options_return['AMOUNT'] = $amount;
                $logger->info("Params devolución: ".json_encode($options_return));
                //Intento realizar la devolución
                try {
                    $return_response = $connector->returnRequest($options_return);
                    $logger->info("Se hace devolucion Parcial returnRequest : " . var_export($return_response ,true) );
                    
                    $refunds_json = get_post_meta($order_id, 'refund', true); 
    				$arr = json_decode($refunds_json, true); 
                    $arr[] = array('amount' => $amount , 'result' => $return_response['StatusCode'] );
           
                    update_post_meta( $order_id, 'refund', json_encode($arr));
                }
                catch (Exception $e) {
                    $logger->error("Falló al consultar el servicio: ", $e);
                    //throw new Exception("Falló al consultar el servicio");
                    return array('result' => 0 , 'message' => "Falló al consultar el servicio:" . $e->getMessage() );
                }

            }

            $logger->debug("return Response: ".json_encode($return_response));

            //Si el servicio no responde según lo esperado, se interrumpe la devolución
            if (!is_array($return_response) || !array_key_exists('StatusCode', $return_response) || !array_key_exists('StatusMessage', $return_response)) {
            //    throw new Exception("El servicio no responde correctamente");
                return array('result' => 0 , 'message' => "El servicio no responde correctamente");
            }
            if ($return_response['StatusCode'] == TODOPAGO_DEVOLUCION_OK) {
                //retorno true para que Wp tome la devolución
                return array('result' => 1 , 'message' => "Se realizo la devolucion correctamente");
            }
            else {
            //    throw new Exception($return_response["StatusMessage"]);
                return array('result' => 0 , 'message' => $return_response["StatusMessage"] );
                //return false;
            }
    }


	function todopago_create_transaction_table(){
   	
	    global $wpdb;

	    $table_name = $wpdb->prefix . TODOPAGO_TABLE_TRANSACTION;
	    $charset_collate = $wpdb->get_charset_collate();

	    $sql = "CREATE TABLE IF NOT  EXISTS $table_name (
	    id INT NOT NULL AUTO_INCREMENT,
	    id_orden INT NULL,
	    first_step TEXT NULL,
	    params_SAR TEXT NULL,
	    response_SAR TEXT NULL,
	    second_step TEXT NULL,
	    params_GAA TEXT NULL,
	    response_GAA TEXT NULL,
	    request_key TEXT NULL,
	    public_request_key TEXT NULL,
	    answer_key TEXT NULL,
	    PRIMARY KEY (id)
	  	) $charset_collate;";

	    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	    dbDelta($sql);
	
	    add_option('todopago_db_version', TODOPAGO_PLUGIN_VERSION);

	}

	function wp_get_request_uri(){
		return $_SERVER["REQUEST_URI"];
	}
	/*
	*	verifica si la orden ya fue reembolsada en su totalidad
	*/
	function todopago_is_fully_refunded( $order_id , $total_price){
		$amountRefunded = todo_pago_refunded_amount( $order_id );
		return ( ($total_price - $amountRefunded) == 0 )? true : false;
	}

	 //Persiste el cantidad reembolsada en la DB
    function todopago_persistRefunds($order_id, $refunded_amount){
    //	update_option('request_key', $request_key);
        update_post_meta( $order_id, 'refunds', $refunded_amount);
    }


    function todo_pago_refunded_amount( $order_id ){

    	$refunds_json = get_post_meta($order_id, 'refund', true); 
    	$refunds_arr = json_decode($refunds_json, true);

    	$total_refund = 0;
	if(!empty($refunds_arr)) {
    		foreach ($refunds_arr as $refund) {
	    		if( $refund['result'] == TODOPAGO_DEVOLUCION_OK ){
    				$total_refund += $refund['amount'];
    			}
	    	}
    	}
    	return $total_refund;
    }


	function meta_box_todopago(){
		// desde aca puedo llamar a las funciones de la orden y hacer la devolucion 
		$id = filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING); 
		$order = tp_get_purchase_logs(array(array('field'=>WPSC_TABLE_PURCHASE_LOGS.'.id' , 'value' => $id )));
		$totalAmount = $order['totalprice'];
		$originalamount = get_post_meta($order['id'], 'originalamount', true);
		$financial_cost = $totalAmount - $originalamount; 
	
		if( isset($_REQUEST['ref_total']) && !$_REQUEST['ref_total'] ){
			// partial refund 	
			$partial_refund = filter_var($_REQUEST['partial_refund'], FILTER_SANITIZE_STRING); 
			$result = process_refund( $id, $partial_refund );		
		}

		if( isset($_REQUEST['ref_total']) && $_REQUEST['ref_total'] == 1 ){
			// total refund
			$result = process_refund( $id, $totalAmount , 1);		
		}

		if (isset( $result['result']) ){
			$message = ''; 
			$result_message = filter_var($result['message'], FILTER_SANITIZE_STRING);
			switch ($result['result']) {
				case '1':
					# success
					$message = '<p class="widefat" ><font color="green">' . $result_message . '</font></p>';
					break;
				
				case '0':
					# error
					$message = '<script> alert("' . $result_message . '"); </script>';
					break;
			}

			echo $message;

		}
		
		?>
			<table class="widefat" >
				<tbody>
				<tr class="wpsc_purchaselog_start_totals"> 
					<td colspan="5" style="width: 460px;"></td><td style="width: 100px; padding-right: 2px;">Otros Cargos:</td><td style="text-align: left;"><?php echo "$".$financial_cost; ?></td>
				</tr>
				<tr> 
					<td colspan="5" style="width: 460px;"></td><td style="width: 100px; padding-right: 2px;">Costo Total:</td><td style="text-align: left;"> <?php echo "$" . $totalAmount; ?></td>
				</tr>
				</tbody>
			</table>	


			<div>
			<form method="post" action="<?php echo get_refund_url(0); ?>" >
			<table class="widefat" cellspacing="1">
				<tr> 
					<td><h3 class="hndle">Reembolsar con Todo Pago</h3></td>
				</tr>
				<tr>
					<td>Cantidad Devuelta : $<?php echo number_format(todo_pago_refunded_amount($_REQUEST['id']), 2, '.', ''); ?></td><td></td>	
				</tr>
				<tr>
					<td>Devolucion Parcial - (el monto no debe superar $ <?php echo $originalamount;  ?>  )</td>
				</tr>
				<tr>
					<td><input id="partial_refund" type="text" name="partial_refund" value="0.00" />
						<input name="partial_refund_todopago" class="button" type="submit" value="Reembolsar monto ingresado" />
						<!--input name="partial_refund_manual" class="button" type="submit" value="Devolver manualmente" /-->
					</td>
				</tr>
				<tr>
					<td><button class="button" type="button" onclick="window.location='<?php echo get_refund_url(1); ?>'" >Reembolsar todo</button></td>
				</tr>
			</table>
			</form>
			</div>

			<div>
			<table class="widefat" cellspacing="1">
				<tr><td><h3 class="hndle">Estado de la orden </h3></td></tr>
				<tr><td><button class="button" type="button" onclick="verstatus(<?php echo $_REQUEST['id']; ?>)">ver estado</button></td>
				</tr>
			</table>
			</div>	
	<?php 	
			//echo dirname(__FILE__);
			include_once dirname(__FILE__)."/lib/view/status.php";
	}

	function get_refund_url($ref_total=0){
		$arr_url = explode('?', wp_get_request_uri()); 
		$_REQUEST['ref_total'] = $ref_total;

		return add_query_arg( $_REQUEST , $arr_url[0] );
	}

    function getCredentials(){
        if((isset($_POST['user']) && !empty($_POST['user'])) &&  (isset($_POST['password']) && !empty($_POST['password']))){

        	if(wp_verify_nonce( $_REQUEST['_wpnonce'], "getCredentials" ) == false) {
                $response = array( 
                    "mensajeResultado" => "Error de autorizacion"
                );  
                echo json_encode($response);
                exit;
            }

            $userArray = array(
                "user" => trim($_POST['user']), 
                "password" => trim($_POST['password'])
            );

            $http_header = array();

            //ambiente developer por defecto 
            $mode = "test";
            if($_POST['mode'] == "prod"){
                $mode = "prod";
            }

            try {
                $connector = new \TodoPago\Sdk($http_header, $mode);
                $userInstance = new \TodoPago\Data\User($userArray);
                $rta = $connector->getCredentials($userInstance);

                $security = explode(" ", $rta->getApikey()); 
                $response = array( 
                    "codigoResultado" => 1,
                    "merchandid" => $rta->getMerchant(),
                    "apikey" => $rta->getApikey(),
                    "security" => $security[1]
                );
            }catch(\TodoPago\Exception\ResponseException $e){
                $response = array(
                    "mensajeResultado" => $e->getMessage()
                );
            }catch(\TodoPago\Exception\ConnectionException $e){
                $response = array(
                    "mensajeResultado" => $e->getMessage()
                );
            }catch(\TodoPago\Exception\Data\EmptyFieldException $e){
                $response = array(
                    "mensajeResultado" => $e->getMessage()
                );
            }
            echo json_encode($response);
        }else{
            $response = array( 
                "mensajeResultado" => "Ingrese usuario y contraseña de Todo Pago"
            );  
            echo json_encode($response);
        }
        exit;
    }

    function format_puchase($args){
        global $data_GAA;
        // hago un GAA o un getstatus 
        $request_GAA = $data_GAA['response_GAA']['Payload']['Request'];
        $request_GAA['FINANCIALCOST'] = $request_GAA['AMOUNTBUYER'] - $request_GAA['AMOUNT']; 
        
        $args['total_price'] = (string)  'Total: $' . $request_GAA['AMOUNTBUYER'];
        $args['total'] = (string) $request_GAA['AMOUNTBUYER'];
        $args['otros_cargos'] = (string) 'Costo Financiero: $'.$request_GAA['FINANCIALCOST'];  
        
        return $args;
    }

    function format_order_list($table_args){
        global $data_GAA;
        
        $purchase_logs = tp_get_purchase_logs( array(array('field' => 'sessionid', 'value' => $_REQUEST['sessionid'])) ); 

        $totalprice = $purchase_logs['totalprice'];
        $request_GAA = $data_GAA['response_GAA']['Payload']['Request']; 
        $financial_cost = $request_GAA['AMOUNTBUYER'] - $totalprice; 

        $table_args['headings']['Otros Cargos'] = 'right';
        $table_args['rows'][0][]= (string) $financial_cost;

        return $table_args;
    }


/*  function my_init() {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js', false, '1.3.2');
        wp_enqueue_script('jquery');
    }
    */

//add_action('init', 'my_init');
add_action('init', 'tp_retorno');
add_action( 'wpsc_purchlogitem_metabox_start', 'meta_box_todopago', 8 );


add_action('wp_ajax_getCredentials', 'getCredentials' ); // executed when logged in
add_action('wp_ajax_nopriv_getCredentials', 'getCredentials' ); 


add_filter("wpsc_purchase_log_notification_common_args", "format_puchase");
add_filter("wpsc_purchase_log_notification_product_table_args","format_order_list");

