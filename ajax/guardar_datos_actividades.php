<?php

use Mpdf\Mpdf;
use Mpdf\MpdfException;

require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';

// GUARDAR LOS DATOS DE LA ACTIVIDAD
if (empty($_POST['caso_titulo_actividad']) || empty($_POST['caso_descripcion_actividad']) || empty($_POST['caso_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos para la actividad']);
    exit;
}
$caso_id = (int)$_POST['caso_id'];
if ($caso_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de caso inválido']);
    exit;
}

// Obtenemos y limpiamos los datos - IMPORTANTE: El orden es crucial
$titulo_actividad = $_POST['caso_titulo_actividad'];
$descripcion_actividad = $_POST['caso_descripcion_actividad'];

// Primero escapa para la base de datos
$titulo_actividad_db = mysql_real_escape_string($titulo_actividad);
$descripcion_actividad_db = mysql_real_escape_string($descripcion_actividad);

// Insertar la actividad en la tabla casos_actividades
$sql_insert = "INSERT INTO casos_actividades (
    caac_titulo,
    caac_descripcion,
    caso_id
  ) VALUES (
    '$titulo_actividad_db',
    '$descripcion_actividad_db',
    $caso_id
  )";

$resultado_insert = mysql_query($sql_insert);

if (!$resultado_insert) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar la actividad: ' . mysql_error()
    ]);
    exit;
}

$actividad_id = mysql_insert_id();

// GENERAR EL DOCUMENTO DE LA ACTIVIDAD
$sql_plantilla = "SELECT * FROM contratos WHERE cont_nombre = 'PLANTILLA-EXPEDIENTE'";
$result_plantilla = mysql_query($sql_plantilla);
$plantilla = mysql_fetch_assoc($result_plantilla);
$contenido_plantilla = $plantilla["cont_detalle"];

// Para el PDF, convertimos los saltos de línea a <br> DESPUÉS de escapar para SQL
$descripcion_actividad_html = nl2br($descripcion_actividad);

// Hacemos los reemplazos en la plantilla
$contenido_plantilla = str_replace("[TITULO]", $titulo_actividad, $contenido_plantilla);
$contenido_plantilla = str_replace("[DESCRIPCION]", $descripcion_actividad_html, $contenido_plantilla);

// Generamos el PDF
try {
    $mpdf = new Mpdf([
       'tempDir' => __DIR__ . "/temp",
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'format' => 'Letter'

    ]);
    $mpdf->WriteHTML($contenido_plantilla);

    // Contar cuántas actividades tiene este caso
    $sql_count = "SELECT COUNT(*) as total FROM casos_actividades WHERE caso_id = $caso_id";
    $result_count = mysql_query($sql_count);
    $row_count = mysql_fetch_assoc($result_count);
    $numero_actividad = $row_count['total'];

    // Generar nombre único para el PDF
    $pdf_nombre = "SEPARADOR-ACTIVIDAD " . $numero_actividad . " - CASO " . $caso_id . "_" . date("His") . ".pdf";
    $pdf_ruta = "img/casos_docs/" . $pdf_nombre;

    // Guardar el PDF en la ruta específica
    $mpdf->Output(__DIR__ . "/../" . $pdf_ruta, 'F');

    // Insertar en la tabla casos_documento
    $sql_insert_doc = "INSERT INTO casos_documentos (
        cado_nombre,
        caso_id,
        cado_ref
    ) VALUES (
        '$pdf_nombre',
        $caso_id,
        '$pdf_nombre'
    )";

    $resultado_insert_doc = mysql_query($sql_insert_doc);

    if (!$resultado_insert_doc) {
        throw new Exception('Error al guardar el documento en la base de datos: ' . mysql_error());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Actividad guardada y documento generado correctamente',
        'pdf_file' => $pdf_ruta,
        'actividad_id' => $actividad_id
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
