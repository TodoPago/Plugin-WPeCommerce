<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
	<html>

	<head>
		<title>Formulario Híbrido</title>
		<meta charset="UTF-8">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src="<?php echo "$env_url/resources/TPHybridForm-v0.1.js"; ?>"></script>
		<link href="<?php echo "$form_dir/todopago-formulario.css " ?>" rel="stylesheet" type="text/css">
		<script>
			$(window).load(function() {
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
			<input id="errors_clean" type="hidden" value="true" />
			<div id="pending_errors" hidden></div>
			<ul class="woocommerce-error" hidden="true">
			</ul>
			<div id="tp-content-form" class="col2-set tp-content-form">
				<div id="tp-logo"><img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/logo.png" /></div>
			<table style="width:500px;">
				<div class="col-1">	
					<tr>
						<td rowspan="3" style="width:205px;" >
							<div class="form-row validate-required" data-validate_classes="validate-required">
							<label for="formaDePagoCbx">Forma de pago <abbr class="required" title="obligatorio">*</abbr></label>
						</td>
						<td><select id="formaDePagoCbx" class="form-field"></select></td>
						</div>
					</tr>
					<tr>	
						<td><div class="form-row tp-no-cupon" data-validate_classes="validate-required">
							<select id="bancoCbx" class="form-field"></select>
							</div>
						</td>
					</tr>
					<tr>
						<td><div class="form-row tp-no-cupon" data-validate_classes="validate-required">
								<select id="promosCbx" class="form-field"></select>
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
							<input id="peiCbx"/>

							</div>
						</td>
					</tr>

					<tr>
						<td colspan="1"><div class="form-row tp-no-cupon" data-validate_classes="validate-required">
							<label for="numeroTarjetaTxt">N&uacute;mero <abbr class="required" title="obligatorio">*</abbr></label>
						</td>
						<td><input id="numeroTarjetaTxt" class="input-text form-field" />
						    </div>
						</td>
					</tr>
					<tr>
						<td><div class="form-row form-row-first dateFields tp-no-cupon" data-validate_classes="validate-required">
						<label for="mesTxt">Fecha de Vto. <abbr class="required" title="obligatorio">*</abbr></label></td>
						<td>
							<input id="mesTxt" class=" left input-text form-field" style="width:60px;" >
							<input id="anioTxt" class=" left input-text form-field" style="width:60px;">
							<div class="clear"></div>
							</div>
						</td>
					</tr>
					<tr>
						<td><div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
							<label for="codigoSeguridadTxt">C&oacute;d. de seguridad <abbr class="required" title="obligatorio">*</abbr></label>
						</td>
						<td>
							<input id="codigoSeguridadTxt" class="left input-text form-field" /></br>
							<span id="labelCodSegTextId" class="left tp-label"></span>
							<div class="clear"></div>
							</div>
						</td>
					</tr>
				</div>

				<div class="col-2" data-validate_classes="validate-required">
				<tr>
					<div class="form-row">
					<td><label for="apynTxt">Nombre y Apellido <abbr class="required" title="obligatorio">*</abbr></label></td>
					<td><input id="apynTxt" class="input-text form-field" /></br>
						<span id="labelApynTextId" class="left tp-label tp-no-cupon">Tal c&oacute;mo aparece en la tarjeta</span>
					</td>
					</div>
				</tr>
				<tr>
					<div class="form-row form-row-first tp-no-cupon"  data-validate_classes="validate-required">
					<td><label for="tipoDocCbx">Tipo de documento<abbr class="required" title="obligatorio">*</abbr></label></td>
					<td><select id="tipoDocCbx" class="form-field"></select></td>
					</div>
				</tr>
				<tr>	
					<div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
					<td><label for="nroDocTxt">Nro. de documento <abbr class="required" title="obligatorio">*</abbr></label></td>
					<td><input id="nroDocTxt" class="input-text form-field" /></td>
					</div>
				</tr>
				<tr>
					<div class="form-row form-row-wide" data-validate_classes="validate-required validate-email">
					<td><label>E-mail <abbr class="required" title="obligatorio">*</abbr></label></td>
					<td><input id="emailTxt" class="input-text form-field" />
						<br/>
					</td>
					</div>
				</tr>

				<tr>
					<div class="form-row form-row-last tp-no-cupon" data-validate_classes="validate-required">
					<td><label id="labelPeiTokenTextId"></label></td>
					<td><input id="peiTokenTxt"/></td>
					</div>
				</tr>

				</div>
			</table>
			</div>
		</form>
		
			<div id="tp-bt-wrapper" style="margin-left: 360px;" >
				<button id="MY_btnConfirmarPago" class="tp-button button alt"></button>
				<button id="MY_btnPagarConBilletera" class="tp-button button btn-sm btn btn-success"/>Pagar con Billetera</button>
			</div>
	</body>
	<script>
		/************* CONFIGURACION DEL API ************************/
		window.TPFORMAPI.hybridForm.initForm({
			callbackValidationErrorFunction: 'validationCollector',
			callbackBilleteraFunction: 'billeteraPaymentResponse',
			botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
			modalCssClass: 'tp-modal-class',
			modalContentCssClass: 'tp-modal-content',
			beforeRequest: 'initLoading',
			afterRequest: 'stopLoading',
			callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
			callbackCustomErrorFunction: 'customPaymentErrorResponse',
			botonPagarId: 'MY_btnConfirmarPago',
			//botonPagarConBilleteraId: 'btn_Billetera',
		});
		window.TPFORMAPI.hybridForm.setItem({
			publicKey: '<?php echo "$prk"; ?>',
			defaultNombreApellido: '<?php echo "$firstname $lastname"; ?>',
			defaultMail: '<?php echo "$email"; ?>'
		});
		function validationCollector(parametros) {
			console.log("My validator collector");
			console.log(parametros.field + " ==> " + parametros.error);
			//Si está "limpio" puede ser porque ya se ejecutó el método _clean_errors() o porque no hubo cambios desde la vez anterior en la que se tocó el botón, en ese caso el div pending_errors debería contener los errores previos
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