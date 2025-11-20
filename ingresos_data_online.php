<?php
// Conexión a la base de datos
include('conexion.php');
include('funciones.php');

// Obtener el ID de la factura desde la URL
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID de factura no proporcionado.";
    exit;
}

// --- 1. Obtener Datos de la Factura Principal ---
$query = "SELECT ingr_numero_factura, ingr_fe_qr,
    ingr_id, ingr_fe_cufe, b.cons_email, b.cons_nombre, b.cons_telefono, 
    ingr_fecha, b.cons_ruc, b.cons_dv,
    ingr_fe_fecha, ingr_total, ingr_subtotal,
    (SELECT faes_nombre FROM facturas_estados WHERE faes_id=a.faes_id) estado
    FROM ingresos a, consignee b
    WHERE ingr_id='$id' 
    AND a.clie_id = b.cons_id
    ORDER BY ingr_id DESC";

$result = mysql_query($query);
$datos_factura = mysql_fetch_assoc($result);

if (!$datos_factura) {
    echo "No se encontraron datos para la factura ID: " . htmlspecialchars($id);
    exit;
}

// --- 2. Obtener la Plantilla HTML ---
$plantilla = obtener_valor("SELECT cont_detalle FROM contratos WHERE cont_nombre='Factura_No_Fiscal'", 'cont_detalle');

if (empty($plantilla)) {
    echo "Error: No se pudo cargar la plantilla de la factura (Factura_No_Fiscal).";
    exit;
}

// --- 3. Obtener Detalles/Ítems de la Factura ---
$query_items = "SELECT inde_cantidad, inde_detalle, ingr_itbms, ingr_precio, ingr_precio * inde_cantidad as monto
                FROM ingresos_detalle 
                WHERE ingr_id='$id';";
$result_items = mysql_query($query_items);

$conceptos_servicios_html = '';
$subtotal_calculado = 0;
$itbms_calculado = 0;

if (mysql_num_rows($result_items) > 0) {
    $conceptos_servicios_html = '
    <table align="center" border="1" cellpadding="8" cellspacing="0" style="width:100%">
        <tbody>
            <tr>
                <td style="text-align:center; width:12%"><strong>CANTIDAD</strong></td>
                <td style="text-align:center; width:50%"><strong>DESCRIPCI&Oacute;N</strong></td>
                <td style="text-align:center; width:19%"><strong>PRECIO UNITARIO</strong></td>
                <td style="text-align:center; width:19%"><strong>MONTO</strong></td>
            </tr>';

    while ($item = mysql_fetch_assoc($result_items)) {
        $monto_item = $item['inde_cantidad'] * $item['ingr_precio'];
        $subtotal_calculado += $monto_item;
        $itbms_calculado += $item['ingr_itbms'];

        $conceptos_servicios_html .= '
            <tr>
                <td style="text-align:center; width:12%">' . htmlspecialchars($item['inde_cantidad']) . '</td>
                <td style="text-align:left; width:50%">' . htmlspecialchars($item['inde_detalle']) . '</td>
                <td style="text-align:right; width:19%">$' . number_format($item['ingr_precio'], 2, '.', ',') . '</td>
                <td style="text-align:right; width:19%">$' . number_format($monto_item, 2, '.', ',') . '</td>
            </tr>';
    }

    $conceptos_servicios_html .= '
        </tbody>
    </table>';
    $bloque_items_encabezado = '
			<table align="center" border="1" cellpadding="8" cellspacing="0" style="width:100%">
				<tbody>
					<tr>
						<td style="text-align:center; width:12%"><strong>CANTIDAD</strong></td>
						<td style="text-align:center; width:50%"><strong>DESCRIPCI&Oacute;N</strong></td>
						<td style="text-align:center; width:19%"><strong>PRECIO UNITARIO</strong></td>
						<td style="text-align:center; width:19%"><strong>MONTO</strong></td>
					</tr>
				</tbody>
			</table>
';
    // Se usa trim() para ayudar a que la sustitución encuentre el bloque, eliminando espacios y saltos de línea extra.
    $plantilla = str_replace(trim($bloque_items_encabezado), '', $plantilla);

} else {
    $conceptos_servicios_html = '<p style="text-align:center;">No hay &iacute;tems de factura.</p>';
}

// --- 4. Preparar y Formatear Variables ---
$numero_factura = htmlspecialchars($datos_factura['ingr_numero_factura']);
$fecha_formato = date('d/m/Y', strtotime($datos_factura['ingr_fecha'])); 
$cliente = htmlspecialchars($datos_factura['cons_nombre']);
$tipo_servicio = 'SERVICIOS PRESTADOS'; // Valor por defecto.

// Usamos los totales de la base de datos si están disponibles.
$subtotal = number_format($datos_factura['ingr_subtotal'] ?? $subtotal_calculado, 2, '.', ',');
$itbms = $itbms_calculado > 0 ? number_format($itbms_calculado, 2, '.', ',') : '0.00';
$total = number_format($datos_factura['ingr_total'] ?? ($datos_factura['ingr_subtotal'] + $datos_factura['ingr_itbms'] - $datos_factura['ingr_descuento']), 2, '.', ',');


// --- 5. Reemplazar Marcadores en la Plantilla ---
$marcadores = array(
    '[NUMERO_FACTURA]',
    '[CLIENTE]',
    '[FECHA]',
    '[TIPO_SERVICIO]',
    '[CONCEPTOS_SERVICIOS]',
    '[SUBTOTAL]',
    '[ITBMS]',
    '[DESCUENTO]',
    '[TOTAL]'
);

$reemplazos = array(
    $numero_factura,
    $cliente,
    $fecha_formato,
    $tipo_servicio,
    $conceptos_servicios_html,
    '$' . $subtotal,
    '$' . $itbms,
    '$' . $descuento,
    '$' . $total
);

$html_final = str_replace($marcadores, $reemplazos, $plantilla);

// --- 6. Devolver el HTML Final ---
echo $html_final;
?>
