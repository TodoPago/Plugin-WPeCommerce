<?php
//if ( ! defined( 'ABSPATH' ) ) exit;

use TodoPago\Sdk as Sdk;

require_once (dirname(__FILE__) . '/../vendor/autoload.php');
require_once(dirname(__FILE__).'/../../../../../../wp-blog-header.php');
//require_once(dirname(__FILE__).'/../TodoPago/lib/Sdk.php');
http_response_code(200);

$http_header = getHttpHeader();

$connector = new Sdk($http_header, get_option('todopago_environment'));

//opciones para el método getStatus 
$optionsGS = array('MERCHANT'=>tp_getMerchant(),'OPERATIONID'=>$_GET['order_id']);
$status = $connector->getStatus($optionsGS);

$rta = '<div>';
$rta .= '<img src="https://portal.todopago.com.ar/app/images/logo.png" alt="Todopago"/>';
$rta .= '<h3>Estado de la operacion - TodoPago </h3>';


$refunds = $status['Operations']['REFUNDS'];

$auxArray = array(
       "REFUND" => $refunds
       );
$auxColection  = '';
if($refunds != null){  
    $aux = 'REFUND'; 
    $auxColection = 'REFUNDS';
}

$rta .='<table>';

if (isset($status['Operations']) && is_array($status['Operations']) ) {
    
      foreach ($status['Operations'] as $key => $value) {   
          if(is_array($value) && $key == $auxColection){
              $rta .= "<tr><td>$key: </td>\n";
              foreach ($auxArray[$aux] as $key2 => $value2) {  
                  $rta .= '<td>';           
                  $rta .= $aux." \n";                
                  if(is_array($value2)){                    
                      foreach ($value2 as $key3 => $value3) {
                          if(is_array($value3)){ 
                              foreach ($value3 as $key4 => $value4) {
                                  $rta .= "   - $key4: $value4 \n";
                              }
                          }else{
                              $rta .= "   - $key3: $value3 \n"; 
                          }                   
                      }
                  }else{
                    $rta .= "   - $key2: $value2 \n";
                  }
                  $rta .= '<td>';
              }
              $rta .= "</tr>";                                
          }else{
              if(is_array($value)){
                  $rta .= "<tr><td>$key:</td><td>";
                  foreach ($value as $key5 => $value5) {
                      $rta .= "   - $key5: $value5 \n";
                  }
                  $rta .= "</td></tr>";
              }else{
                  $rta .= "<tr><td>$key:</td><td>$value</td></tr>";
              }
          }
      }
 }else{
     $rta .= '<tr><td>No hay operaciones para esta orden.<td></tr>';
 }

$rta .= '</table>';
$rta .= '</div>';
//echo($rta);
echo '<pre>'; print($rta);
