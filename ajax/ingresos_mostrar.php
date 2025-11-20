<?php include('conexion.php'); ?>
<script src='jquery/sorter/tablesort.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.number.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.date.min.js'></script>
<script>
	$(function() {
		new Tablesort(document.getElementById('resultado'));
	});
</script>
<table class="table" id="resultado" align-middle style="width:99%">
	<thead class='thead-dark'>
		<tr>
			<th></th>
			<th class=tabla_datos_titulo>No. Factura</th>
			<th class=tabla_datos_titulo>Clientes</th>
			<th class=tabla_datos_titulo>Correo</th>
			<th class=tabla_datos_titulo>Contacto</th>
			<th class=tabla_datos_titulo>Fecha</th>
			<th class=tabla_datos_titulo>Monto</th>
			<th class=tabla_datos_titulo>Estado</th>
			<th class=tabla_datos_titulo>Opciones</th>
			<!-- <th class=tabla_datos_titulo>FE</th> -->
			<!-- <th class=tabla_datos_titulo>Recurrente</th> -->
			<!-- <th class=tabla_datos_titulo>Recurrente<br>Anual</th> -->
			<!-- <th class=tabla_datos_titulo_icono></th> -->
			<!-- <th class=tabla_datos_titulo_icono></th> -->
		</tr>
	</thead>
	<tbody>
		<?php
		$factura = $_GET['factura'];
		$desde = $_GET['desde'];
		$hasta = $_GET['hasta'];
		$cliente = $_GET['cliente'];
		$estado = $_GET['estado'];

		$where = "";
		if ($factura != '') $where .= " AND ingr_numero_factura=$factura";
		if ($desde != '') $where .= " AND date_format(ingr_fecha, '%Y%m%d')>=$desde";
		if ($hasta != '') $where .= " AND date_format(ingr_fecha, '%Y%m%d')<=$hasta";
		if ($cliente != '') $where .= " AND a.clie_id in ($cliente)";
		if ($estado != '') $where .= " AND a.faes_id in ($estado)";

		$qsql = "SELECT ingr_numero_factura, ingr_fe,
		ingr_id, ingr_fe_cufe,	b.cons_email,	b.cons_nombre,	b.cons_telefono, ingr_fecha, b.cons_ruc, b.cons_dv,
		ingr_fe_fecha,	ingr_total,
		(select faes_nombre from facturas_estados where faes_id=a.faes_id) estado
		FROM ingresos a, consignee b
		WHERE a.clie_id = b.cons_id
		$where
		ORDER BY ingr_id DESC;";
		//echo nl2br($qsql);
		$rs = mysql_query($qsql);
		$num = mysql_num_rows($rs);
		$i = 0;
		while ($i < $num) {
		?>
			<tr class='tabla_datos_tr'>
				<td><?php echo $i + 1 ?></td>
				<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'ingr_numero_factura'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'cons_nombre'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'cons_email'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'cons_telefono'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'ingr_fecha'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'ingr_total'); ?></td>
				<td class=tabla_datos><?php echo mysql_result($rs, $i, 'estado'); ?></td>
				<!-- <td class=tabla_datos_iconos><a href='javascript:editar(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' ;><img src='imagenes/modificar.png' border=0 style="width:25px;height:25px" alt="Editar" title="Editar"></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:imprimir_factura(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' ;><img src='imagenes/invoice.png' style="width:25px;height:25px" border=0 title="Imprimir Factura" alt="Imprimir Factura"></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:enviar_factura(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' ;><img src='imagenes/mail.png' style="width:25px;height:25px" border=0 title="Enviar" alt="Enviar Factura"></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:enviar_factura(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' ;><img src='imagenes/mail.png' style="width:25px;height:25px" border=0 title="Enviar" alt="Enviar Factura"></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:recurrente(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' title='Recurrente Mensual'><i class="far fa-2x fa-calendar-check"></i></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:recurrente_anual(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' title='Recurrente Anual'><i class="far fa-2x fa-calendar-check"></i></a></td> -->
				<!-- <td class=tabla_datos_iconos><a href='javascript:borrar(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)' ;><img src='imagenes/trash.png' border=0 style="width:25px;height:25px" title="Eliminar" alt="Eliminar"></a></td> -->
				<td>
					<div class="dropdown">
						<button class="btn btn-light btn-sm" type="button" id="dropdownMenu<?php echo mysql_result($rs, $i, 'ingr_id'); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fas fa-ellipsis-v"></i>
						</button>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?php echo mysql_result($rs, $i, 'ingr_id'); ?>">
							<a class="dropdown-item" href='javascript:editar(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)'>
								<i class="fa-solid fa-eye mr-2 text-warning"></i> Editar
							</a>
							<!-- <a class="dropdown-item" href='javascript:imprimir_factura(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)'>
								<i class="fa-solid fa-print mr-2 text-info"></i> Imprimir Factura
							</a> -->
							<a class="dropdown-item" href='javascript:borrar(<?php echo mysql_result($rs, $i, 'ingr_id'); ?>)'>
								<i class="fa-solid fa-trash mr-2 text-danger"></i> Eliminar
							</a>
							<?php if (mysql_result($rs, $i, 'ingr_fe') == ""): ?>
								<button class="dropdown-item" onclick="enviarFiscal('<?php echo mysql_result($rs, $i, 'cons_ruc'); ?>', '<?php echo mysql_result($rs, $i, 'cons_dv'); ?>', <?php echo mysql_result($rs, $i, 'ingr_id'); ?>)">
									<i class="fa-solid fa-file-invoice mr-2 text-success"></i> Enviar Fiscal
								</button>
							<?php endif ?>
						</div>
					</div>
				</td>
			</tr>
		<?php
			$tmonto += mysql_result($rs, $i, 'ingr_total');
			// $tcosto += $costo;
			// $tmargen += $margen;

			$i++;
		}
		?>
		<tr data-sort-method='none'>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td style="text-align:center !important"><?php echo number_format($tmonto, 2) ?></td>
			<td></td>
			<td></td>
			<!-- <td></td> -->
			<!-- <td></td> -->
			<!-- <td></td> -->
			<!-- <td></td> -->
			<!-- <td></td> -->
		</tr>
	</tbody>
</table>

<script>
	function enviarFiscal(ruc, dv, ingr_id) {

		if (ruc == null || ruc == "" || dv == null || dv == "") {
			alert("Numero de RUC o DV, no válido")
			return
		}

		$.post(`./ajax/cargos_servicios.php?ruc=${ruc}&tipo=2&dv=${dv}&ingr_id=${ingr_id}`, {
			a: "facturar"
		})
	}
</script>