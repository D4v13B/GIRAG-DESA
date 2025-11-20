<?php
//header('Content-Type: application/pdf');
//header('Content-Disposition: inline; filename="' . $pdf_file . '"');
// Habilitar visualización de errores
ob_start();
ob_clean(); // Limpia cualquier salida previa
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include "conexion.php";
include "funciones.php";

use Mpdf\Mpdf;
use Mpdf\MpdfException;

require "vendor/autoload.php";
// header('Content-Type: application/pdf');

// https://mpdf.github.io/
// http://192.168.1.176/dashboard/

$carg_id = $_GET["carg_id"];
$cade_id = isset($_GET["cade_id"]) ? $_GET["cade_id"] : null; // Parámetro opcional
// $cade_guia = obtener_valor("SELECT * FROM carga_detalles WHERE cade_id = $cade_id", "cade_guia");

// Creamos la condición del filtro que usaremos en las subconsultas
$filtro_detalle = $cade_id !== null ? " AND cd.cade_id = $cade_id" : "";

// Obtener la plantilla
$sql = "SELECT * FROM contratos WHERE cont_nombre = 'RECIBO-CARGA-EXPORT'";
$recibo = mysql_query($sql);
$plantilla = mysql_fetch_assoc($recibo);
$plantilla = $plantilla["cont_detalle"];

// Consulta principal con los totales filtrados

// Primero hacemos la consulta principal de carga
$sql = "SELECT carga.*,
    (SELECT usua_nombre FROM usuarios WHERE usua_id = carga.usua_id_creador) usua_creador,
    (SELECT ship_nombre FROM shipper WHERE ship_id = carga.ship_id) shipper,
    (SELECT cons_nombre FROM consignee WHERE cons_id = carga.cons_id) consignee,
    (SELECT vuel_codigo FROM vuelos WHERE vuel_id = carga.vuel_id) vuel_codigo,
    (SELECT aeco_nombre FROM aereopuertos_codigos WHERE aeco_id = carga.aeco_id_destino_final) AS aeco_destino,
    (SELECT SUM(cade_peso) FROM carga_detalles cd WHERE cd.carg_id = carga.carg_id $filtro_detalle) peso_total,
    (SELECT SUM(cade_piezas) FROM carga_detalles cd WHERE cd.carg_id = carga.carg_id $filtro_detalle) piezas_totales,
    (SELECT caes_nombre FROM carga_estado WHERE caes_id = carga.caes_id) caes_nombre,
    (SELECT tran_nombre FROM transportes WHERE tran_id = carga.tran_id) tran_nombre,
    (SELECT cati_nombre FROM carga_tipos WHERE cati_id = carga.cati_id) direccion,
    (SELECT ROUND(SUM((cd.cade_alto / 100) * (cd.cade_ancho / 100) * (cd.cade_largo / 100)), 4) 
     FROM carga_detalles cd WHERE cd.carg_id = carga.carg_id $filtro_detalle) AS volumen_total,
    (SELECT liae_ref FROM lineas_aereas WHERE liae_id = (SELECT liae_id FROM vuelos WHERE vuel_id = carga.vuel_id)) AS liae_ref

FROM carga WHERE carg_id = $carg_id";


$res = mysql_fetch_assoc(mysql_query($sql));

$cati_id = obtener_valor("SELECT cati_id FROM carga WHERE carg_id = '$carg_id'", "cati_id");

// Si hay un cade_id específico, sobrescribimos shipper, consignee y descripción
if ($cade_id !== null) {
  // Consulta para obtener shipper, consignee y descripción de carga_detalles
  $sql_details = "SELECT 

        (SELECT ship_nombre FROM shipper WHERE ship_id = cd.ship_id) as shipper,
        (SELECT cons_nombre FROM consignee WHERE cons_id = cd.cons_id) as consignee,
        cd.cade_desc as descripcion,
        cade_guia
    FROM carga_detalles cd 
    WHERE cd.cade_id = $cade_id";

  $details_result = mysql_fetch_assoc(mysql_query($sql_details));

  // Sobrescribimos los valores solo si la consulta devolvió resultados
  if ($details_result) {
    $res["shipper"] = $details_result["shipper"];
    $res["consignee"] = $details_result["consignee"];
    $res["carg_desc"] = $details_result["descripcion"]; // Sobrescribimos carg_desc con cade_desc
    $res["carg_guia"] = $details_result["cade_guia"]; // Sobrescribimos carg_desc con cade_desc
  }
}

// print_r($res);

// Generar la tabla del transportista solo si el nombre del transportista existe
$tabla_transportista_html = "";

// Usamos el campo carg_transportista para determinar si hay datos de transportista.
if (!empty($res["carg_transportista"])) {
    $tabla_transportista_html = '
<table cellspacing="0" style="border-collapse:collapse; margin:10px auto; text-align:left; width:99%">
	<thead>
		<tr>
			<th style="background-color:#101a21; text-align:center"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px"><span style="color:#ffffff">Transportista</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px"><span style="color:#ffffff">C&eacute;dula/RUC</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px"><span style="color:#ffffff">Tipo de Transporte</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px"><span style="color:#ffffff">Matr&iacute;cula/Placa</span></span></span></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="background-color:#d3d3d3; height:25px; text-align:center; width:25%"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px">[TRANSPORTISTA]</span></span></td>
			<td style="background-color:#d3d3d3; height:25px; text-align:center; width:25%"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px">[CEDULA]</span></span></td>
			<td style="background-color:#d3d3d3; height:25px; text-align:center; width:25%"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px">[TIPO_TRANSPORTE]</span></span></td>
			<td style="background-color:#d3d3d3; height:25px; text-align:center; width:25%"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="font-size:10px">[MATRICULA]</span></span></td>
		</tr>
	</tbody>
</table>';
}

// Reemplazar el nuevo placeholder en la plantilla.
// Si no hay transportista, reemplazará el placeholder con una cadena vacía.
$plantilla = str_replace("[TABLA_TRANSPORTISTA]", $tabla_transportista_html, $plantilla);

$plantilla = str_replace("[RECIBO_CARGA]", "WHS" . $res["carg_guia"], $plantilla);
$plantilla = str_replace("[FECHA_CREACION]", $res["carg_fecha_registro"], $plantilla);
$plantilla = str_replace("[FECHA_REAL_RECEPCION]", $res["carg_recepcion_real"], $plantilla);
$plantilla = str_replace("[TRANSPORTISTA]", $res["carg_transportista"], $plantilla);
$plantilla = str_replace("[TIPO_TRANSPORTE]", $res["tran_nombre"], $plantilla);
$plantilla = str_replace("[CEDULA]", $res["carg_transportista_cedula"], $plantilla);
$plantilla = str_replace("[MATRICULA]", $res["carg_transportista_matricula"], $plantilla);
$plantilla = str_replace("[NUMERO_GUIA]", $res["carg_guia"], $plantilla);
$plantilla = str_replace("[DIRECCION]", $res["direccion"], $plantilla);
$plantilla = str_replace("[CREADO_POR]", $res["usua_creador"], $plantilla);
$plantilla = str_replace("[SHIPPER]", $res["shipper"], $plantilla);
$plantilla = str_replace("[CONSIGNEE]", $res["consignee"], $plantilla);
$plantilla = str_replace("[VUELO]", $res["vuel_codigo"], $plantilla);
$plantilla = str_replace("[DESTINO_FINAL]", $res["aeco_destino"], $plantilla);
$plantilla = str_replace("[ESTADO]", $res["caes_nombre"], $plantilla);
$plantilla = str_replace("[KILOS_TOTALES]", $res["carg_peso"], $plantilla);
$plantilla = str_replace("[PIEZAS_TOTALES]", $res["piezas_totales"], $plantilla);
$plantilla = str_replace("[NOTAS]", $res["carg_nota"], $plantilla);
$plantilla = str_replace("[VOLUMEN_KILOS]", number_format($res["volumen_total"], 2), $plantilla);
$plantilla = str_replace("[DESCRIPCION]", $res["carg_desc"], $plantilla);
if (!empty($res["liae_ref"])) {
  $liae_ref_image = "<img src='./img/liae_ref/" . $res["liae_ref"] . "' style='width: 75px; height: 75px;' alt='Línea Aérea'>";
} else {
  $liae_ref_image = "<img src='./img/liae_ref/default.png' style='width: 45px; height: 45px;' alt='Línea Aérea'>";
}

$plantilla = str_replace("[LIAE_REF]", $liae_ref_image, $plantilla);
// print_r($plantilla);


$sql = "SELECT *
FROM carga_detalles cd
WHERE cd.carg_id = '$carg_id'";
if ($cade_id !== null) {
  $cade_guia_tmp = obtener_valor("SELECT * FROM carga_detalles WHERE cade_id = $cade_id", "cade_guia");
  $sql .= " AND cd.cade_guia = '$cade_guia_tmp'";
}
$res = mysql_query($sql);

$desc = "";
$style = "<style>
    /* Colores alternados para las filas */
    .row-par { background-color: #D3D3D3; }
    .row-impar { background-color: #ffff; }

    /* Estilos base para todas las tablas */
    .tabla-datos,
    .tabla-detalles {
        width: 100%;
        border-collapse: collapse;
        font-size: 5px;
        margin: 0;
        padding: 0;
    }

    /* Estilos compartidos para celdas (sin background-color aquí) */
    .tabla-datos td,
    .tabla-datos th,
    .tabla-detalles th,
    .tabla-detalles td {
        border: 1px solid black;
        text-align: center;
        vertical-align: middle;
    }
        
    td, tr, th {
        text-align: center;
        boder: solid 1px black;
    }

    /* Contenedor para mantener las tablas alineadas */
    .tablas-container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
</style>
";

$desc .= "<div class='tablas-container'>";

// print_r($res);


$desc .= '<table cellspacing="0" style="border-collapse:collapse; margin:10px auto; text-align:left; width:99%">
	<thead>
		<tr>
		<th style="background-color:#101a21; text-align:center"><span style="font-size:14px"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="color:#ffffff">Piezas</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-size:14px"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="color:#ffffff">Largo CM</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-size:14px"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="color:#ffffff">Ancho CM</span></span></span></th>
			<th style="background-color:#101a21; text-align:center"><span style="font-size:14px"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="color:#ffffff">Alto CM</span></span></span></th>
			
			<th style="background-color:#101a21; text-align:center"><span style="font-size:14px"><span style="font-family:Tahoma,Geneva,sans-serif"><span style="color:#ffffff">VOL</span></span></span></th>
		</tr>
	</thead>
	<tbody>';

//Localización solo se utilizaría en Importación
// Estructura HTML mejorada
// echo "Hola" . $cati_id;

$total_largo = 0;
$total_ancho = 0;
$total_alto = 0;
$total_volumen = 0;
$total_piezas = 0;
foreach ($res as $index => $item) {

  $volumen = ($item["cade_largo"]) * ($item["cade_ancho"]) * ($item["cade_alto"]) / 6000 * $item["cade_piezas"];
  $rowStyle = ($index % 2 === 0) ? 'background-color: #ffffff;' : 'background-color: #D3D3D3;';
  $desc .= "<tr style='$rowStyle font-family: Tahoma, Geneva, sans-serif;'>
      <td class='tabla-detalles' style='font-size:14px'>" . $item["cade_piezas"] . "</td>
      <td class='tabla-detalles' style='font-size:14px'>" . $item["cade_largo"] . "</td>
      <td class='tabla-detalles' style='font-size:14px'>" . $item["cade_ancho"] . "</td>
      <td class='tabla-detalles' style='font-size:14px'>" . $item["cade_alto"] . "</td>
      
      <td style='width: 50px;font-size:14px'>" . number_format($volumen, 2) . "</td>
      
      </tr>";

  $total_alto += $item["cade_alto"];
  $total_ancho += $item["cade_ancho"];
  $total_largo += $item["cade_largo"];
  $total_volumen += $volumen;
  $total_piezas += $item["cade_piezas"];
}


$desc .= "
    <tr style='background-color:#101a21; font-family: Tahoma, Geneva, sans-serif;'>
      <td style='width: 50px;font-size:14px; color:#ffffff'> " . number_format($total_piezas, 2) . "</td>
      <td style='width: 65px;font-size:14px; color:#ffffff'>" . number_format($total_largo, 2) . "</td>
      <td style='width: 65px;font-size:14px; color:#ffffff'>" . number_format($total_ancho, 2) . "</td>
	    <td style='width: 65px;font-size:14px; color:#ffffff'>" . number_format($total_alto, 2) . "</td>
      <td style='width: 50px;font-size:14px; color:#ffffff'>".number_format($total_volumen, 2)."</td>
    </tr>";


$desc .= "</tbody></table></div>";

// echo$style;
// echo$desc;
// echohtmlspecialchars($desc);

$plantilla = str_replace("[CARGA_DETALLES]", $desc, $plantilla);

// echo$desc;

try {
  $mpdf = new Mpdf([
    'tempDir' => __DIR__ . "/temp",
    'margin_left' => 10,
    'margin_right' => 10
  ]);

  // Escribir el HTML en el PDF
  $mpdf->WriteHTML($plantilla);
  // echo $plantilla;
  // Guardar el PDF en el directorio temporal
  $pdf_file = "recibo_carga_" . $carg_id . ".pdf";
  // header('Content-Disposition: inline; filename="recibo_carga_' . $carg_id . '.pdf"');
  $mpdf->Output();
} catch (MpdfException $e) {
  echo "Ocurrio un error inesperado" . $e->getMessage();
  // exit;
}
