<?php

use Mpdf\Mpdf;
use Mpdf\MpdfException;

require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';

// Determinar el tipo de solicitud y procesarla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // GUARDAR LOS DATOS DEL EXPEDIENTE
    if (empty($_POST['caso_titulo_portada']) || empty($_POST['cadt_id']) || empty($_POST['caso_id'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        exit;
    }

    $titulo_portada = nl2br($_POST['caso_titulo_portada']); // Convertir saltos de línea en <br>
    $titulo_portada = mysql_real_escape_string($titulo_portada); // Escapar para SQL
    // $cadt_id = (int)$_POST['cadt_id'];
    $caso_id = (int)$_POST['caso_id'];

    // Actualizamos la tabla casos
    $sql_update = "UPDATE casos SET
                   caso_titulo_portada = '$titulo_portada'
                   WHERE caso_id = $caso_id";
    $resultado_update = mysql_query($sql_update);

    if (!$resultado_update) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar los datos: ' . mysql_error()
        ]);
        exit;
    }

    // GENERAR LA PORTADA DEL EXPEDIENTE
    $sql_plantilla = "SELECT * FROM contratos WHERE cont_nombre = 'PORTADA-EXPEDIENTE'";
    $result_plantilla = mysql_query($sql_plantilla);

    if (!$result_plantilla || mysql_num_rows($result_plantilla) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la plantilla de portada'
        ]);
        exit;
    }

    $plantilla = mysql_fetch_assoc($result_plantilla);
    $contenido_plantilla = $plantilla["cont_detalle"];

    // Obtenemos los datos del caso para hacer los reemplazos
    $sql_caso = "SELECT * FROM casos WHERE caso_id = $caso_id";
    $result_caso = mysql_query($sql_caso);

    if (!$result_caso || mysql_num_rows($result_caso) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron datos del caso'
        ]);
        exit;
    }

    $datos_caso = mysql_fetch_assoc($result_caso);

    // Hacemos los reemplazos en la plantilla
    $contenido_plantilla = str_replace("[TITULO]", $datos_caso["caso_titulo_portada"], $contenido_plantilla);
    $contenido_plantilla = str_replace("[NUMERO]", $caso_id, $contenido_plantilla);

    // Generamos el PDF
    try {
        $tempDir = __DIR__ . "/temp";

        // Verificar que el directorio temp exista, si no, crearlo
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio temporal');
            }
        }

        $mpdf = new Mpdf([
            'tempDir' => $tempDir,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'format' => 'Letter'
        ]);

        $mpdf->WriteHTML($contenido_plantilla);

        // Generar nombre único para el PDF
        $pdf_nombre = "portada_caso_" . $caso_id . "_" . date("YmdHis") . ".pdf";
        $directorio_destino = __DIR__ . "/../img/casos_docs/";

        // Verificar que el directorio destino exista, si no, crearlo
        if (!is_dir($directorio_destino)) {
            if (!mkdir($directorio_destino, 0755, true)) {
                throw new Exception('No se pudo crear el directorio para guardar PDFs');
            }
        }

        $pdf_ruta = "img/casos_docs/" . $pdf_nombre;
        $pdf_ruta_completa = $directorio_destino . $pdf_nombre;

        // Guardar el PDF en la ruta específica
        $mpdf->Output($pdf_ruta_completa, 'F');

        if (!file_exists($pdf_ruta_completa)) {
            throw new Exception('No se pudo guardar el archivo PDF');
        }

        // Insertar en la tabla casos_documento
        $sql_insert = "INSERT INTO casos_documentos (
            cado_nombre,
            caso_id,
            cado_ref
        ) VALUES (
            '$pdf_nombre',
            $caso_id,
            '$pdf_nombre'
        )";

        $resultado_insert = mysql_query($sql_insert);

        if (!$resultado_insert) {
            throw new Exception('Error al guardar en la base de datos: ' . mysql_error());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Datos actualizados y PDF guardado correctamente',
            'pdf_file' => $pdf_ruta
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verificar que el parámetro 'caso' esté presente en la solicitud
    if (!isset($_GET['caso']) || empty($_GET['caso'])) {
        echo json_encode(['success' => false, 'message' => 'ID del caso no proporcionado.']);
        exit;
    }

    $caso_id = intval($_GET['caso']);

    try {
        // Obtener documentos del expediente
        $sql_docs = "SELECT cado_id, cado_ref FROM casos_documentos WHERE caso_id = $caso_id ORDER BY cado_id ASC";
        $result_docs = mysql_query($sql_docs);

        if (!$result_docs) {
            throw new Exception('Error al consultar documentos: ' . mysql_error());
        }

        $documentos = [];
        while ($row = mysql_fetch_assoc($result_docs)) {
            $documentos[] = $row;
        }

        // Obtener separadores relacionados con el expediente
        $sql_separadores = "SELECT * FROM casos_documentos_separadores ORDER BY cads_id ASC";
        $result_separadores = mysql_query($sql_separadores);

        if (!$result_separadores) {
            throw new Exception('Error al consultar separadores: ' . mysql_error());
        }

        $separadores = [];
        while ($row = mysql_fetch_assoc($result_separadores)) {
            $separadores[] = $row;
        }

        // Obtener documentos de las tareas
        $sql_tareas = " SELECT DISTINCT
    ct.caso_id,
    td.tado_ref,
    td.tado_id,
    td.cate_id
FROM casos_tareas AS ct
INNER JOIN tareas_documentos AS td 
    ON ct.cate_id = td.cate_id
WHERE ct.caso_id = $caso_id
ORDER BY td.tado_id ASC";
        $result_tareas = mysql_query($sql_tareas);

        if (!$result_tareas) {
            throw new Exception('Error al consultar documentos de las tareas: ' . mysql_error());
        }

        $tareas = [];
        while ($row = mysql_fetch_assoc($result_tareas)) {
            $tareas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'documentos' => $documentos,
            'separadores' => $separadores,
            'tareas' => $tareas
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener los documentos.',
            'error' => $e->getMessage()
        ]);
    }
}
