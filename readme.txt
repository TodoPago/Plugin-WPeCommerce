=== Plugin Name ===
Contributors: todopago
Tags: todopago, payment, wpecommerce
Requires at least: 3.5.7
Tested up to: 4.6.1
Stable tag: V1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin de integración de TodoPago para WPeCommerce

== Description ==
WPeCommerce- Módulo Todo Pago (v1.1.0)

== Consideraciones Generales ==
El plug in de pagos de <strong>Todo Pago</strong>, provee a las tiendas WooCommerce de un nuevo método de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.3 en adelante y WordPress 3.7.5 con WpeCommerce 3.11.3

== Instalación ==

1. Copiar y pegar la carpeta wpsc-merchants en la carpeta **\wp-content\plugins\wp-e-commerce\
2. Ir a la administration de wp-ecommerce, ir a Settings > Store > Payments.
3. Check TodoPago, click update y luego click Edit para configurar el modulo

Observaciónes:

1. Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
Descomentar: <em>extension=php_openssl.dll</em> del php.ini 

2. En caso de tener conflictos con Jquery por los diferentes temas, descomentar la siguiente linea que se encuentra al final del index.php


== Configuración ==

####Activación
La activación se realiza en la seccion payments del plugin wp-ecommerce: Desde Settings -> Store -> Payments
Marcar la opción TodoPago y luego guardar con Save Changes.

####Configuración plug in
Para llegar al menu de configuración del plugin ir a: Settings -> Store -> Payments-> Todopago y seleccionar settings. Se desplegará el formulario de configuracion

####Formulario Hibrido
En la configuracion del plugin tambien estara la posibilidad de mostrarle al cliente el formulario de pago de TodoPago integrada en el sitio. 
Para esto , en la configuracion se debe seleccionar la opcion Integrado en el campo de seleccion de formulario

El formulario tiene dos formas de pago, ingresando los datos de una tarjeta ó utilizando la billetera de Todopago. Al ir a "Pagar con Billetera" desplegara una ventana que permitira ingresar a billetera y realizar el pago.

####Obtener datos de configuracion
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. 
a. Ir a la opcion Obtener credenciales
b. Loguearse con el mail y password de Todopago.
c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente y solo hay que hacer click en el boton guardar datos y listo.

####Configuración de Maximo de Cuotas
Se puede configurar la cantidad máxima de cuotas que ofrecerá el formulario de TodoPago con el campo cantidad máxima de cuotas. Para que se tenga en cuenta este valor se debe habilitar el campo Habilitar máximo de cuotas y tomará el valor fijado para máximo de cuotas. En caso que esté habilitado el campo y no haya un valor puesto para las cuotas se tomará el valor 12 por defecto.

== Características ==

#### Consulta de Transacciones
Se puede consultar on line las características de la transacción en el sistema de Todo Pago . Para esto se debe ir al listado de ordenes en el menu izquierdo Dashboard -> Store Sales

#### Devoluciones
Es posible realizar devoluciones de TodoPago desde el detalle de la orden. Para ello dirigirse al detalle de la orden y en la sección Reembolsar con Todo Pago, hay un campo para hacer devoluciones parciales y al lado un botón Reembolsar monto ingresado, al hacer click ahí devolvera el monto ingresado. Si se quiere hacer una devolucion total del monto simplemente hacer click en el boton Reembolsar todo.
