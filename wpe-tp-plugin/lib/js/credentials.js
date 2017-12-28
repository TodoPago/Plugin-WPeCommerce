var tpformJquery = jQuery.noConflict();
var globalError = false;

function credentials(tipo) {
    console.log(tipo);
    var user = tpformJquery("#mail_" + tipo).val();
    var password = tpformJquery("#pass_" + tipo).val();
    var wpnonce = tpformJquery("#wpnonce").val();
    getCredentials(user, password, tipo, wpnonce);
}

/*tpformJquery("#woocommerce_todopago_btnCredentials_dev").click(function() {

    var user = tpformJquery("#woocommerce_todopago_user_dev").val();
    var password = tpformJquery("#woocommerce_todopago_password_dev").val();
    var wpnonce = tpformJquery("#woocommerce_todopago_wpnonce").attr('placeholder');

    getCredentials(user, password, 'test', wpnonce);

});

tpformJquery("#woocommerce_todopago_btnCredentials_prod").click(function() {

    var user = tpformJquery("#woocommerce_todopago_user_prod").val();
    var password = tpformJquery("#woocommerce_todopago_password_prod").val();
    var wpnonce = tpformJquery("#woocommerce_todopago_wpnonce").attr('placeholder');

    getCredentials(user, password, 'prod', wpnonce);

});
*/
function getCredentials(user, password, mode, nonce) {

    tpformJquery.ajax({
        type: 'POST',
        url: 'admin-ajax.php',
        data: {
            'action': 'getCredentials',
            '_wpnonce': nonce,
            'user': user,
            'password': password,
            'mode': mode
        },
        success: function (data) {
            setCredentials(data, mode);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            switch (xhr.status) {
                case 404:
                    alert("Verifique la correcta instalación del plugin");
                    break;
                default:
                    alert("Verifique la conexion a internet y su proxy");
                    break;
            }
        }
    });
}


function setCredentials(data, ambiente) {
    var response = tpformJquery.parseJSON(data);
    console.log(response);
    if (globalError === false && response.codigoResultado === undefined) {
        globalError = true;
        alert(response.mensajeResultado);
    } else {
        globalError = false;
        tpformJquery("#todopago_merchant_id_" + ambiente).val(response.merchandid);
        tpformJquery("#todopago_authorization_header_" + ambiente).val(response.apikey);
        tpformJquery("#todopago_security_" + ambiente).val(response.security);
    }
}

/*
    if( tpformJquery("#woocommerce_todopago_enabledCuotas").prop('checked') ) {
        tpformJquery("#woocommerce_todopago_max_cuotas").prop('disabled', false);
    }else{
        tpformJquery("#woocommerce_todopago_max_cuotas").prop('disabled', true);
    }

    tpformJquery("#woocommerce_todopago_enabledCuotas").click(function() {

        if( tpformJquery("#woocommerce_todopago_enabledCuotas").prop('checked') ) {
            tpformJquery("#woocommerce_todopago_max_cuotas").prop('disabled', false);
        }else{
            tpformJquery("#woocommerce_todopago_max_cuotas").prop('disabled', true);
        }
    });
    */

