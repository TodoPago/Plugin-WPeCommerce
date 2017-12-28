<?php
/*
Plugin Name: WP eCommerce TodoPago
Plugin URI: https://developers.todopago.com.ar/
Version: 1.0.0
Author: TodoPago
Description: A plugin that allows the store owner to process payments using Square
Author URI:  https://todopago.com.ar/
*/

function wpsc_tp_register_file() {
	wpsc_register_payment_gateway_file( dirname(__FILE__) . '/todopago-payments.php' );
}

add_filter( 'wpsc_init', 'wpsc_tp_register_file' );


