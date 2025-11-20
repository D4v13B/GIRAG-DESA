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

	function modificar() {
		if (validar_campos() == 1) {
			$('#result').load('fe_no_enviadas_modificar.php', {
					'id': $('#h2_id').val(),
					'm_ingr_numero_factura': $('#m_ingr_numero_factura').val(),
					'm_ingr_fecha': $('#m_ingr_fecha').val(),
					'm_cons_ruc': $('#m_cons_ruc').val(),
					'm_cons_dv': $('#m_cons_dv').val(),
					'm_cons_nombre': $('#m_cons_nombre').val(),
					'm_cons_direccion': $('#m_cons_direccion').val(),
					'm_cons_telefono': $('#m_cons_telefono').val(),
					'm_cons_email': $('#m_cons_email').val(),
					'm_ingr_subtotal': $('#m_ingr_subtotal').val(),
					'm_ingr_impuesto': $('#m_ingr_impuesto').val(),
					'm_ingr_total': $('#m_ingr_total').val(),
					'm_ingr_tipo_cliente_FE': $('#m_ingr_tipo_cliente_FE').val(),
					'm_ingr_tipo_contribuyente_FE': $('#m_ingr_tipo_contribuyente_FE').val()
				},
				function(data) {
					$('#modal2').hide('slow');
					$('#overlay2').hide();
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

	function editar(id) 
	{
		$('#modal2').show();
    $('#overlay2').show();
    $('#modal2').center();
    $('#h2_id').val(id);
		$.get('fe_no_enviadas_datos.php?id=' + id, function(data) {
			var resp = data;
			r_array = resp.split('||');
			$('#m_ingr_numero_factura').val(r_array[3]);
			$('#m_ingr_fecha').val(r_array[2]);
			$('#m_cons_ruc').val(r_array[9]);
			$('#m_cons_dv').val(r_array[10]);
			$('#m_cons_nombre').val(r_array[11]);
			$('#m_cons_direccion').val(r_array[8]);
			$('#m_cons_telefono').val(r_array[12]);
			$('#m_cons_email').val(r_array[13]);
			$('#m_ingr_subtotal').val(r_array[14]);
			$('#m_ingr_impuesto').val(r_array[16]);
			$('#m_ingr_total').val(r_array[15]);
			$('#m_ingr_tipo_cliente_FE').val(r_array[17]);
			$('#m_ingr_tipo_contribuyente_FE').val(r_array[18]);
			$('#modal2').show();
			$('#overlay2').show();
			$('#modal2').center();
		});
	}
	function mostrar() {
		$('#datos_mostrar').load('fe_no_enviadas_mostrar.php?id=1' +
			"&factura=" + $('#f_factura').val() +
			"&desde=" + $('#f_desde').val() +
			"&hasta=" + $('#f_hasta').val() +
			"&cliente=" + $('#f_cons_id').val() +
			"&estado=" + $('#f_estado').val() +
			"&pagado=" + $('#f_pagado').val() 
		);
	}

function imprimir_factura(id) {
    // Get al archivo PHP que genera el HTML de la factura
    $.get('fe_no_enviadas_data_online.php?id=' + id, function(htmlContent) {
        generarPDFOnline(htmlContent, 'Factura_' + id + '.pdf', 800);
    }).fail(function() {
        alert("Error al obtener los datos de la factura.");
    });
}
</script>
<?php
?>
<?php
?>
<div id='separador'>
	<table class=filtros>
		<tr>
			<?php echo entrada('input', 'Factura', 'f_factura', 150) ?>
			<?php echo entrada('input', 'Desde', 'f_desde', 150) ?>
			<?php echo entrada('input', 'Hasta', 'f_hasta', 150) ?>
		</tr>
		<tr>
			<?php echo catalogo('consignee', 'Clientes', 'cons_nombre', 'f_cons_id', 'cons_id', 'cons_nombre', '0', '1', '100') ?>
			<?php echo catalogo('facturas_estados', 'Estado', 'faes_nombre', 'f_estado', 'faes_id', 'faes_nombre', 0, 1, 150) ?>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
		</tr>
	</table>
</div>
<div id='columna6'>
	<div id='datos_mostrar'></div>
</div>

<div id='overlay2'></div>
<div id='modal2'>
	<div id='content2'>
		<input type=hidden id=h2_id>
		<table>
			<tr>
				<?php echo entrada('input', 'Factura', 'm_ingr_numero_factura', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Fecha', 'm_ingr_fecha', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<td>
					<select name="m_ingr_tipo_cliente_FE" id="m_ingr_tipo_cliente_FE">
						<option value=1>Contribuyente</option>
						<option value=2>Consumidor Final</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<select name="m_ingr_tipo_contribuyente_FE" id="m_ingr_tipo_contribuyente_FE">
						<option value="1">Natural</option>
						<option value="2">Jurídico</option>
					</select>
				</td>
			</tr>
			<tr>
				<?php echo entrada('input', 'RUC', 'm_cons_ruc', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'DV', 'm_cons_dv', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Razón Social', 'm_cons_nombre', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Dirección', 'm_cons_direccion', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Teléfono', 'm_cons_telefono', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Correo', 'm_cons_email', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Subtotal', 'm_ingr_subtotal', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'ITBMS', 'm_ingr_impuesto', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<?php echo entrada('input', 'Total', 'm_ingr_total', '150', '', '', '', '') ?>
			</tr>
			<tr>
				<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
			</tr>
		</table>
	</div>
	<a href='javascript:void(0);' id='close2'>close</a>
</div>
<div id=result style="visibility:hidden"></div>
<div id=escondido style="visibility:hidden"></div>