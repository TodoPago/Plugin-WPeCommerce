<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
        
        //var_dump("Url del formulario: ".$url_form);
?>
	<html>

	<head>
		<title>Formulario Híbrido</title>
		<meta charset="UTF-8">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src="<?php echo $url_form;  ?>"></script>
		<link href="<?php echo "$form_dir/todopago-formulario.css " ?>" rel="stylesheet" type="text/css">
		<script>
			$(window).load(function() {
				$("#tp-form-tph").submit(function(e){
					e.preventDefault();
                                });
				$("#formaDePagoCbx").change(function () {
					if(this.value == 500 || this.value == 501){
						$(".form-row.tp-no-cupon").each(function(div) {
							$(this).removeClass($(this).attr("data-validate_classes"));
						});
					}else{
						$(".form-row.tp-no-cupon").each(function() {
							$(this).addClass($(this).attr("data-validate_classes"));
						});
					}
				});
				$("#MY_btnConfirmarPago").click(_clean_errors);
				$(".form-field").change(_unclean_errors);
			});
			function _clean_errors() { //Se ajecuta al apretar pagar
				//Remueve la clases a todos los fields que los marcan como invalido y les pone las de campo valido
				$("#tp-form-tph").find(".form-row").removeClass("woocommerce-invalid woocommerce-invalid-required-field").addClass("woocommerce-validated");
				//limpia los errores
				$(".woocommerce-error").empty();
				$(".woocommerce-error").hide();
				//$("errors_clean").val("true");
				//Si hay errores pendientes los agrego al div de errores (Los errores pendientes se ponen en el div de errores en validationCollector)
				$("#pending_errors").children().each(function _add_errors() {
					$('.woocommerce-error').append("<li>"+$(this).val()+"</li>");
					$('.woocommerce-error').show();
					$($(this).attr('data-element')).parent().addClass("woocommerce-invalid woocommerce-invalid-required-field").removeClass("woocommerce-validated");
				})
			}
			function _unclean_errors() { //Se ejecuta al hacer cambios en alguno de los campos del formulario
				//Lo marco como "sucio" lo cuál significa que validationCollector pondrá los errores en el div de errores pendientes, para lo cuál lo vacía.
				$("#errors_clean").val("false");
				$("#pending_errors").empty();
			}
		</script>
	</head>

	<body class="contentContainer">
            <form id="tp-form-tph">
		
			<div id="tp-content-form" class="col2-set tp-content-form">
				<div id="tp-logo"><img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/logo.png" /></div>
			<table style="width:600px;">
				<div class="col-1">	
					<tr>
                        <td>
                            <select id="formaPagoCbx"></select>
                        </td>	
					</tr>
					
					<tr>
						<td><div class="form-row tp-no-cupon" data-validate_classes="validate-required">
								<label id="labelPromotionTextId" class="left tp-label"></label>
								<div class="clear"></div>
							</div>
						</td>
					</tr>

					<tr>
						<td colspan='2'>
							<div class="form-row tp-no-cupon" data-validate_classes="validate-required">

							<label id="labelPeiCheckboxId"></label>
							</br>

							</div>
						</td>
					</tr>

					<tr>
						<td colspan="1">
                            <div class="form-row tp-no-cupon" data-validate_classes="validate-required">
                                <input id="numeroTarjetaTxt"/>
                                <label id="numeroTarjetaLbl"></label>
						</td>
						<td> 
                                <input id="nombreTxt"/>
                                <label id="nombreLbl"></label>
						    </div>
						</td>
					</tr>
					<tr>
						<td>
                            <div class="form-row form-row-first dateFields tp-no-cupon" data-validate_classes="validate-required">
                            <input id="codigoSeguridadTxt" style="width: 155px; margin-right: 100px;"/>
                            <label id="codigoSeguridadLbl" for="codigoSeguridadTxt"></label>
                        </td>
						<td>
                                <select id="tipoDocCbx"></select>
                                <input id="nroDocTxt" class="not-input-max" style="width: 175px;" />
                            	<label id="nroDocLbl"></label>
                                <div class="clear"></div>
                            </div>
						</td>
					</tr>
					<tr>
						<td>
                                                    <div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
                                                        <select id="medioPagoCbx"></select>
						</td>
						<td>
							<input id="emailTxt"/>
							<label id="emailLbl"></label>
							<div class="clear"></div>
                                                    </div>
						</td>
					</tr>
				</div>

				<div class="col-2" data-validate_classes="validate-required">
				<tr>
					<div class="form-row">
                                            <td>
                                                <select id="bancoCbx"></select>
                                            </td>
                                            
					</div>
				</tr>
				<tr>
					<div class="form-row form-row-first tp-no-cupon"  data-validate_classes="validate-required">
					<td>
                         <select id="promosCbx"></select>
                         <label id="promosLbl"></label>
                    </td>
					<td></td>
					</div>
				</tr>
				<tr>	
					<div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
					<td>
                                            <select id="mesCbx"></select>
                                            <select id="anioCbx"></select>
                                        </td>
                                        <td>
                                            <label id="fechaLbl"></label>
                                        </td>
					<td></td>
					</div>
				</tr>
				<tr>
					<div class="form-row form-row-wide" data-validate_classes="validate-required validate-email">
					<td>
                                            <label id="peiLbl"></label>
                                            <input id="peiCbx"/>
                                        </td>
					<td>
						<br/>
					</td>
					</div>
				</tr>

				<tr>
					<div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
					<td>
                        <label id="tokenPeiLbl"></label>
                        <input id="tokenPeiTxt"/>
                    </td>
                    
					</div>
				</tr>
				<tr>
					<td>
                        <button id="MY_btnConfirmarPago" class="tp-button button alt"></button>
                        <button id="MY_btnPagarConBilletera" class="tp-button button btn-sm btn btn-success"/>Pagar con Billetera</button>
                    </td>
				</tr>

				</div>
			</table>
			</div>
		</form>
		
	</body>
	<script>
                $(document).ready(function(){
                    $("#tp-form-tph").submit(function(event){
                        event.preventDefault();
                    });
                });
                        
		/************* CONFIGURACION DEL API ************************/
		window.TPFORMAPI.hybridForm.initForm({
                    callbackValidationErrorFunction: 'validationCollector',
                    callbackBilleteraFunction: 'billeteraPaymentResponse',
                    callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
                    callbackCustomErrorFunction: 'customPaymentErrorResponse',
                    botonPagarId: 'MY_btnConfirmarPago',
                    botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
                    modalCssClass: 'modal-class',
                    modalContentCssClass: 'modal-content',
                    beforeRequest: 'initLoading',
                    afterRequest: 'stopLoading'
                });
                
                /************* SETEO UN ITEM PARA COMPRAR ******************/
                window.TPFORMAPI.hybridForm.setItem({
                    publicKey: '<?php echo $response_sar["PublicRequestKey"]; ?>',
                    defaultNombreApellido: '<?php echo $full_name; ?>',
                    defaultNumeroDoc: '',
                    defaultMail: '<?php echo $email; ?>',
                    defaultTipoDoc: 'DNI'
                });
		
		function validationCollector(parametros) {
			console.log("My validator collector");
			console.log(parametros.field + " ==> " + parametros.error);
			//Si está "limpio" puede ser porque ya se ejecutó el método _clean_errors() o porque no hubo cambios desde la vez anterior en la que se tocó el botón, en ese caso el div pending_errors debería contener los errores previos
			var input = parametros.field;

	        if (input.search("Txt") !== -1) {
	            label = input.replace("Txt", "Lbl");
	        } else {
	            label = input.replace("Cbx", "Lbl");
	        }

	        if (document.getElementById(label) != null) {
				document.getElementById(label).innerHTML = parametros.error;
	        }

			if ($('#errors_clean').val() == "true" && $("#pending_errors").children().length == 0) {
				$('.woocommerce-error').append("<li>"+parametros.error+"</li>");
				$('.woocommerce-error').show();
				$('#'+parametros.field).parent().addClass("woocommerce-invalid woocommerce-invalid-required-field").removeClass("woocommerce-validated");
			}
			//Agrego los errores al div de errores pendientes, siempre y cuando no estén aún (Esto puede ocurrir si el usuario volvió a intentar pagar sin hacer cambios en los campos del formulario)
			if ($("#error_"+parametros.field).length == 0) {
				$("#pending_errors").append('<input type="hidden" id="error_'+parametros.field+'" value="'+parametros.error+'" data-element="#'+parametros.field+'" />');
			}
		}
		function billeteraPaymentResponse(response) {
			console.log(response.ResultCode + " : " + response.ResultMessage);
			if (response.AuthorizationKey){
				if (response.ResultCode == -1 ){
					document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
				
				} else{
					document.location = "<?php echo "$return_URL_ERROR&Answer="; ?>" + response.AuthorizationKey;	
				
				}  

			} else{
				document.location = "<?php echo $return_URL_ERROR ?>&Error="+response.ResultMessage;
				
			}
		}

		function customPaymentSuccessResponse(response) {
			//console.log(response.ResultCode + " : " + response.ResultMessage);
			document.location = "<?php echo "$return_URL_OK&Answer="; ?>" + response.AuthorizationKey;
		}
		function customPaymentErrorResponse(response) {
			//console.log(response.ResultCode + " : " + response.ResultMessage);
			if (response.AuthorizationKey){
				document.location = "<?php echo "$return_URL_ERROR&Answer="; ?>" + response.AuthorizationKey;
			} else{
				document.location = "<?php echo $return_URL_ERROR ?>&Error="+response.ResultMessage;
			}
		}
		function initLoading() {
			console.log('Cargando');
		}
		function stopLoading() {
			console.log('Stop loading...');
		}
	</script>

	</html>
