<?php
include "conexion.php";
require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . "/temp"]);
$caso_id = $_GET["caso"];

// Get template - Cambiado para reporte de gestión
$stmt = "SELECT * FROM contratos WHERE cont_nombre = 'REPORTE-INCIDENTES'";
$plantilla = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

// Get case details - Adaptado para incluir campos de gestión
$stmt = "SELECT caso_cargo_reporta, caso_id, caso_nombre_abierto_por, caso_fecha, caso_descripcion, caso_nota,
(SELECT impe_nombre FROM impacto_personas impe WHERE impe.impe_id = a.impe_id) lesionados,
(SELECT cacl_nombre FROM casos_clasificacion cacl WHERE cacl.cacl_id = a.cacl_id) reporte_asociado,
(SELECT cati_nombre FROM casos_tipos ct WHERE ct.cati_id = a.cati_id) tipo_reporte,
(SELECT depa_nombre FROM departamentos dp WHERE dp.depa_id = a.depa_id_quien_reporta) departamento,
(SELECT caus_nombre FROM cargos_usuarios cu WHERE a.caus_id = cu.caus_id) caus_nombre
FROM casos a WHERE caso_id = $caso_id ";
$res = mysql_query($stmt);
$caso_detalles = mysql_fetch_assoc($res);

// Replace placeholders in template
$plantilla = str_replace("[NO.REPORTE]", $caso_detalles["caso_id"], $plantilla);
$plantilla = str_replace("[NOMBRE_QUIEN_REPORTA]", $caso_detalles["caso_nombre_abierto_por"], $plantilla);
$plantilla = str_replace("[AREA_DEPARTAMENTO]", $caso_detalles["departamento"], $plantilla);
$plantilla = str_replace("[FECHA_HORA_EVENTO]", $caso_detalles["caso_fecha"], $plantilla);
$plantilla = str_replace("[TIPO_REPORTE]", $caso_detalles["tipo_reporte"], $plantilla);
$plantilla = str_replace("[REPORTE_CLASIFICACION]", $caso_detalles["reporte_asociado"], $plantilla);
$plantilla = str_replace("[TITULO_EVENTO]", $caso_detalles["caso_descripcion"], $plantilla);
$plantilla = str_replace("[PERSONAS_LESIONADAS]", $caso_detalles["lesionados"], $plantilla);
$plantilla = str_replace("[DESCRIPCION_EVENTO]", $caso_detalles["caso_nota"], $plantilla);
$plantilla = str_replace("[CARGO_OCUPA]", $caso_detalles["caso_cargo_reporta"], $plantilla);

ob_start();
$output0 = ob_get_clean();
ob_start();
$content = ob_get_clean();

$mpdf->WriteHTML($plantilla);

if(isset($_GET["tipo"]) && $_GET["tipo"] == "guardado"){
    $nombre = time()."-reporte-incidente-caso-$caso_id.pdf";
    $mpdf->Output("img/casos_docs/".$nombre, 'F');
    
    // Insertar el documento y actualizar el estado
    $stmt = "INSERT INTO casos_documentos(cado_nombre, caso_id, cado_ref) 
             VALUES('$nombre', '$caso_id', '$nombre')";
    mysql_query($stmt);
    
    // Actualizar el estado del caso para indicar que el reporte de gestión fue generado
    // $stmt = "UPDATE casos SET reporte_gestion_generado = 1 WHERE caso_id = $caso_id";
    // mysql_query($stmt);
    
    echo json_encode([
        "tipo" => "Guardado hecho",
        "nombre" => $nombre,
        "caso_id" => $caso_id
    ]);
} else {
    $mpdf->Output();
}
?>