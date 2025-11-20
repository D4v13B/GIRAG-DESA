<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.js"></script>
<script src="funciones.js"></script>
<script>
	$(function() {
		$("#i_ingr_fecha").datepicker({
			dateFormat: 'yymmdd'
		});
		$("#f_desde").datepicker({
			dateFormat: 'yymmdd'
		});
		$("#f_hasta").datepicker({
			dateFormat: 'yymmdd'
		});
		$('#div_modificar').hide();
	});

	function validar_campos() {
		var $forma_pago = $('#i_infp_id').val();
		var $entregado = $('#i_entregado_id').val();
		// var $pagado = $('#i_pagado_id').val();
		/*
		alert($forma_pago);
		alert($entregado);
		alert($pagado);
		*/
		if ($forma_pago != '' && $entregado != '') {
			return 1;
		} else {
			return 0;
		}
	}

	function crear() {
		if (validar_campos() == 1) {
			$('#result').load('ingresos_crear.php', {
					'i_clie_id': $('#i_clie_id').val(),
					'h_codigo': $('#h_codigo').val(),
					'i_ingr_fecha': $('#i_ingr_fecha').val(),
					'i_numero_factura': $('#i_numero_factura').val(),
					// 'i_infp_id': $('#i_infp_id').val(),
					// 'i_entregado': $('#i_entregado_id').val(),
					// 'i_pagado': $('#i_pagado_id').val()
				},
				function() {
					//$('#f_cliente').val($('#i_clie_id').val());
					$('#f_desde').val('<?php echo date('Ymd') ?>');
					$('#modal').hide('slow');
					$('#overlay').hide();
					mostrar();
				}
			);
		} else {
			alert('Debe llenar todos los campos!');
		}
	}

	function modificar() {
		if (validar_campos() == 1) {
			//alert($('#i_clie_id').val());
			$('#result').load('ingresos_modificar.php', {
					'id': $('#h_id').val(),
					'i_clie_id': $('#i_clie_id').val(),
					'i_ingr_fecha': $('#i_ingr_fecha').val(),
					'i_numero_factura': $('#i_numero_factura').val(),
					'i_infp_id': $('#i_infp_id').val(),
					'i_entregado': $('#i_entregado_id').val(),
					'i_pagado': $('#i_pagado_id').val()
				},
				function(data) {
					//alert(data);
					$('#modal').hide('slow');
					$('#overlay').hide();
					mostrar();
				}
			);
		} else {
			alert('Debe ingresar todos los campos!');
		}
	}

	function borrar(id) {
		var agree = confirm('¿Está seguro?');
		if (agree) {
			$('#result').load('ingresos_borrar.php?id=' + id,
				function() {
					mostrar();
				}
			);
		}
	}

	function editar(id) {
		$('#h_id').val(id);
		$('#div_crear').hide();
		$('#div_modificar').show();
		$.get('codigo_temporal_modificar.php?id=' + id,
			function(data) {
				$('#h_codigo').val(data);
				//ahora muestro los items para esa factura
				mostrar_items();
				$.get('ingresos_datos.php?id=' + id, function(data) {
					var resp = data;
					r_array = resp.split('||');
					//alert(r_array[0]);
					$('#i_clie_id').val(r_array[1]);
					$("#i_clie_id").multipleSelect("refresh");
					$('#i_ingr_fecha').val(r_array[2]);
					$('#i_numero_factura').val(r_array[3]);
					$('#i_infp_id').val(r_array[4]);
					$('#i_entregado_id').val(r_array[5]);
					$('#i_pagado_id').val(r_array[6]);
					$('#modal').show();
					$('#overlay').show();
					$('#modal').center();
				});
			}
		);
	}

	function mostrar() {
		$('#datos_mostrar').load('ingresos_mostrar.php?id=1' +
			"&factura=" + $('#f_factura').val() +
			"&desde=" + $('#f_desde').val() +
			"&hasta=" + $('#f_hasta').val() +
			"&cliente=" + $('#f_cons_id').val() +
			"&estado=" + $('#f_estado').val() +
			"&pagado=" + $('#f_pagado').val() +
			"&guia=" + $('#f_guia').val()
		);
	}

	function precio() {
		$.get('ingresos_producto_precio.php?id=' + $('#m_prod_id').val() + "&clienid=" + $("#i_clie_id").val(),
			function(data) {
				r_array = data.split('|');
				data_precio = r_array[0];
				lleva_itbms = r_array[1];
				
				$('#i_precio').val(data_precio);
				if(lleva_itbms==1) $('#inc_itbms').val(1);
				if(lleva_itbms!=1) $('#inc_itbms').val(0);
				if ($('#inc_itbms').val()==1) {
					v_itbms = data_precio * .07;
					v_itbms = v_itbms.toFixed(2);
					$('#i_itbms').val(v_itbms);
				} else {
					$("#i_itbms").val("0.00");
				}
			}
		);
	}

	function nuevo() {
		$('#div_crear').show();
		$('#div_modificar').hide();
		$('#h_id').val('');
		$('#i_infp_id').val('');
		$('#i_entregado_id').val('');
		$('#i_pagado_id').val('');
		$.get('codigo_temporal.php?',
			function(data) {
				var resp = data;
				r_array = resp.split('||');
				$('#h_codigo').val(r_array[0]);
				$('#i_numero_factura').val(r_array[1]);
				$('#i_ingr_fecha').val(<?php echo date('Ymd') ?>);
				//ahora muestro los items para esa factura
				mostrar_items();
				$('#modal').show();
				$('#overlay').show();
				$('#modal').center();
				$('#i_clie_id').val('');
				$("#i_clie_id").multipleSelect("refresh");
			}
		);
	}

	function mostrar_items() {
		$('#i_detalle').load('ingresos_items_mostrar.php?id=' + $('#h_codigo').val());
	}

	function agregar_item() {
		$('#dvd_mensaje').text(""); //limpio el mensaje
		$('#result').load('ingresos_items_crear.php?prod_id=' + $('#m_prod_id').val(), {
				'inti_id': $('#inti_id').val(),
				'i_precio': $('#i_precio').val(),
				'i_itbms': $('#i_itbms').val(),
				'r_detalle': $('#r_detalle').val(),
				'h_codigo': $('#h_codigo').val(),
				'i_cantidad': $('#i_cantidad').val(),
				'ingr_id': $('#h_id').val()
			},
			function(data) {
				if (data != '') $('#dvd_mensaje').text(data); //normalmente si no hay en existencia aquí lo dira
				mostrar_items();
			}
		);
	}

	function imprimir_recibo(id) {
		$("#result").load("exportar_pdf_v5.php?contenido=documento_recibo.php&id=" + id,
			function() {
				//abro el documento
				window.open('recibos/recibo_' + id + '.pdf');
			});
	}

	function imprimir_factura(id) {
    // Get al archivo PHP que genera el HTML de la factura
    $.get('ingresos_data_online.php?id=' + id, function(htmlContent) {
        generarPDFOnline(htmlContent, 'Factura_' + id + '.pdf', 800);
    }).fail(function() {
        alert("Error al obtener los datos de la factura.");
    });
	}

	function editar_item(id) {
		$('#modal2').show();
		$('#overlay2').show();
		$('#modal2').center();
		$('#h2_id').val(id);
		$.get('ingresos_detalle_datos.php?id=' + id, function(data) {
			var resp = data;
			r_array = resp.split('||');
			//alert(r_array[0]);
			$('#m_prod_id').val(r_array[1]);
			$('#m_inde_cantidad').val(r_array[2]);
			$('#m_inti_id').val(r_array[3]);
			$('#m_ingr_precio').val(r_array[4]);
			$('#m_inde_temp_code').val(r_array[5]);
			$('#m_inde_detalle').val(r_array[6]);
		});
	}

	function borrar_item(id) {
		var agree = confirm('¿Está seguro?');
		if (agree) {
			$('#result').load('ingresos_detalle_borrar.php?id=' + id,
				function() {
					mostrar_items();
				}
			);
		}
	}

	function modificar_item() {
		$('#result').load('ingresos_detalle_modificar.php', {
				'id': $('#h2_id').val(),
				'm_prod_id': $('#m_prod_id').val(),
				'm_inde_detalle': $('#m_inde_detalle').val(),
				'm_inde_cantidad': $('#m_inde_cantidad').val(),
				'm_inti_id': $('#m_inti_id').val(),
				'm_ingr_precio': $('#m_ingr_precio').val()
			},
			function() {
				$('#modal2').hide('slow');
				$('#overlay2').hide();
				mostrar_items();
			}
		);
	}

	function actualizar_ubicacion(id) {
		$('#dv_ubicacion_actual').load('ingresos_items_ubicacion_actual.php?id=' + id,
			function(data) {
				$('#dv_ubicacion_disponible').load('ingresos_items_ubicacion_disponible.php?id=' + id,
					function(data) {
						$('#modal3').show();
						$('#overlay3').show();
						$('#modal3').center();
						$('#h3_id').val(id);
					}
				);
			}
		);
	}

	function calcular_itbms() {
		if ($('#inc_itbms').val()==1) {
			v_itbms = $('#i_precio').val() * $('#i_cantidad').val() * .07;
			v_itbms = v_itbms.toFixed(2);
			$('#i_itbms').val(v_itbms);
		} else {
			$("#i_itbms").val("0.00");
		}
	}

	function recurrente(id) {
		$.get('ingresos_recurrente.php?id=' + id,
			function() {
				mostrar();
			}

		);
	}

	function recurrente_anual(id) {
		$.get('ingresos_recurrente_anual.php?id=' + id,
			function() {
				mostrar();
			}

		);
	}

	function enviar_factura(id) {
		$("#result").load("ingresos_enviar_factura.php?id=" + id,
			function(data) {
				alert('Factura Enviada');
				//abro el documento
			});
	}
</script>
<?php //include('tipo_de_producto_script.php')
?>
<?php //include('combo_box_generar_script.php');
?>
<div id='separador'>
	<table class=filtros>
		<tr>
			<?php echo entrada('input', 'Factura', 'f_factura', 150) ?>
			<?php echo entrada('input', 'Desde', 'f_desde', 150) ?>
			<?php echo entrada('input', 'Hasta', 'f_hasta', 150) ?>
		</tr>
		<tr>
			<?php echo catalogo('consignee', 'Clientes', 'cons_nombre', 'f_cons_id', 'cons_id', 'cons_nombre', '0', '1', '100'); ?>
			<?php echo catalogo('facturas_estados', 'Estado', 'faes_nombre', 'f_estado', 'faes_id', 'faes_nombre', 0, 1, 150) ?>
			<?php echo entrada('input', 'N° Guia', 'f_guia', 150) ?>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
			<td><a href='javascript:nuevo()' class=botones>Nuevo</a><input type=hidden id=h_codigo></td>
		</tr>
	</table>
</div>
<div id='columna6'>
	<div id='datos_mostrar'></div>
</div>
<!--MODAL-->
<div id='overlay'></div>
<div id='modal'>
	<div id='content'>
		<input type=hidden id=h_id>
		<input type="hidden" id="inc_itbms" name="inc_itbms" style="width:80px !important" autocomplete="off" checked>
		<table>
			<tr>
				<td colspan=6>
					<table class=filtros style="width:1000px">
						<tr>
							<?php echo catalogo('consignee', 'Clientes', 'cons_nombre', 'i_clie_id', 'cons_id', 'cons_nombre', '0', '2', '100'); ?>
							<td class='etiquetas'>Fecha:</td>
							<td><input type='text' id=i_ingr_fecha size=40 class='entradas' value="<?php echo date('Ymd') ?>"></td>
							<?php echo entrada('input', 'No. Factura', 'i_numero_factura', '100'); ?>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=4>
					<table class=filtros style="width:1000px">
						<tr>
							<td colspan=2>
								<div id=dvd_mensaje></div>
							</td>
						</tr>
					</table>
					<table class=filtros style="width:1000px">
						<tr>
							<?php echo catalogo('productos_tbl', 'Item', 'prod_nombre', 'm_prod_id', 'prod_id', 'prod_nombre', 0, 2, 150, '', ' onchange=precio()') ?>
							<?php echo entrada('input', 'Detalle', 'r_detalle') ?>
							<TD></TD>
							<TD></TD>
						</tr>
						<tr>
							<?php echo entrada('input', 'Cantidad', 'i_cantidad', '60', '1', '', " onchange='calcular_itbms()'") ?>
							<?php echo entrada('input', 'Precio', 'i_precio', '80', '', '', " onchange='calcular_itbms()'") ?>
							<?php echo entrada('input', 'Itmbs', 'i_itbms', '80', "", "", "0.00", "") ?>
							<td><a href='javascript:agregar_item()' class=botones>Agregar</a></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=6>
					<div id=i_detalle></div>
				</td>
			</tr>
			<tr>
				<td colspan=2>
					<div id=div_crear><a href='javascript:crear()' class='botones'>Crear</a></div>
					<div id=div_modificar><a href='javascript:modificar()' class='botones'>Modificar</a></div>
				</td>
			</tr>
		</table>
	</div>
	<a href='javascript:void(0)' id='close'>close</a>
</div>
<div id='overlay2'></div>
<div id='modal2'>
	<div id='content2'>
		<input type=hidden id=h2_id>
		<table>
			<tr>
				<?php //echo catalogo('productos_tbl', 'Item', 'prod_nombre', 'm_prod_id', 'prod_id', 'prod_nombre', 0, 0, 150, '', ' onchange="precio();"') 
				?>;
				<?php echo catalogo('productos_tbl', 'Item', 'prod_nombre', 'm_prod_id', 'prod_id', 'prod_nombre', 0, 0, 150) ?>;
			</tr>
			<tr>
				<td class='etiquetas'>Detalle:</td>
				<td><input type='text' id=m_inde_detalle size=40 class='entradas'></td>
			</tr>
			<tr>
				<td class='etiquetas'>Cantidad:</td>
				<td><input type='text' id=m_inde_cantidad size=40 class='entradas'></td>
			</tr>
			<tr>
				<?php echo catalogo('ingresos_tipos', 'Tipo', 'inti_nombre', 'm_inti_id', 'inti_id', 'inti_nombre', 0, 0, 150); ?>
			</tr>
			<tr>
				<td class='etiquetas'>precio:</td>
				<td><input type='text' id=m_ingr_precio size=40 class='entradas'></td>
			</tr>
			<tr>
				<td colspan=2><a href='javascript:modificar_item()' class='botones'>Modificar</a></td>
			</tr>
		</table>
	</div>
	<a href='javascript:modificar_item();' id='close2'>close</a>
</div>
<!--EDITAR UBICACION-->
<div id='overlay3'></div>
<div id='modal3'>
	<div id='content3'>
		<input type=hidden id=h3_id>
		<table>
			<tr>
				<td class='etiquetas'>
					<div id="dv_ubicacion_actual"></div>
				</td>
			</tr>
			<tr>
				<td class='etiquetas'>
					<div id="dv_ubicacion_disponible"></div>
				</td>
			</tr>
		</table>
	</div>
	<a href='javascript:void(0);' id='close3'>close</a>
</div>
<div id=result style="visibility:hidden"></div>
<!--<div id=result></div>-->
<div id=escondido style="visibility:hidden"></div>