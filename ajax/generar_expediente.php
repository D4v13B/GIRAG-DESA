<?php
use Mpdf\Mpdf;
use Mpdf\MpdfException;
require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$caso_id = isset($_POST['caso_id']) ? intval($_POST['caso_id']) : 0;
if ($caso_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de caso inválido.']);
    exit;
}
// Obtener los documentos seleccionados en el orden especificado
$documentos_seleccionados = isset($_POST['documentos_seleccionados']) ? $_POST['documentos_seleccionados'] : '';
if (empty($documentos_seleccionados)) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron documentos seleccionados.']);
    exit;
}
error_log("Documentos seleccionados: " . print_r($_POST['documentos_seleccionados'], true));

// Convertir la cadena de IDs a un array
// Obtener los documentos y separadores en el orden especificado
$documentos_ids = explode(',', $documentos_seleccionados);
$archivos = [];
foreach ($documentos_ids as $doc_id) {
    if (strpos($doc_id, 'sep_') === 0) {
        // Es un separador
        $sep_id = intval(str_replace('sep_', '', $doc_id));
        if ($sep_id > 0) {
            $sql_sep = "SELECT cads_ref FROM casos_documentos_separadores WHERE cads_id = $sep_id";
            $result_sep = mysql_query($sql_sep);
           
            if ($result_sep && mysql_num_rows($result_sep) > 0) {
                $sep = mysql_fetch_assoc($result_sep);
                $ruta_archivo = __DIR__ . '/../img/casos_docs/' . $sep['cads_ref'];
                if (file_exists($ruta_archivo)) {
                    $archivos[] = $ruta_archivo;
                }
            }
        }
    } elseif (strpos($doc_id, 'doc_') === 0) {
        // Es un documento
        $doc_id = intval(str_replace('doc_', '', $doc_id));
        if ($doc_id > 0) {
            $sql_doc = "SELECT cado_ref FROM casos_documentos WHERE cado_id = $doc_id AND caso_id = $caso_id";
            $result_doc = mysql_query($sql_doc);
           
            if ($result_doc && mysql_num_rows($result_doc) > 0) {
                $doc = mysql_fetch_assoc($result_doc);
                $ruta_archivo = __DIR__ . '/../img/casos_docs/' . $doc['cado_ref'];
                if (file_exists($ruta_archivo)) {
                    $archivos[] = $ruta_archivo;
                }
            }
        }
    }elseif (strpos($doc_id, 'tado_') === 0) {
        // Es un documento de tarea
        $tado_id = intval(str_replace('tado_', '', $doc_id));
        if ($tado_id > 0) {
            // Escribir en el log para depuración
            error_log("Procesando documento de tarea con ID: $tado_id para el caso: $caso_id");
            
            // Consulta SQL para buscar el documento de tarea
            $sql_tado = "SELECT td.tado_ref 
                         FROM tareas_documentos td 
                         INNER JOIN casos_tareas ct ON td.cate_id = ct.cate_id 
                         WHERE td.tado_id = $tado_id AND ct.caso_id = $caso_id";
            
            error_log("SQL ejecutado: $sql_tado");
            $result_tado = mysql_query($sql_tado);
            
            if ($result_tado && mysql_num_rows($result_tado) > 0) {
                $tado = mysql_fetch_assoc($result_tado);
                // Ruta del archivo (en casos_docs)
                $ruta_archivo = __DIR__ . '/../img/casos_docs/' . $tado['tado_ref'];
                error_log("Ruta de archivo a buscar: $ruta_archivo");
                
                if (file_exists($ruta_archivo)) {
                    error_log("Archivo encontrado y añadido: $ruta_archivo");
                    $archivos[] = $ruta_archivo;
                } else {
                    error_log("Archivo no encontrado: $ruta_archivo");
                }
            } else {
                error_log("No se encontró el documento de tarea con ID: $tado_id para el caso: $caso_id");
                error_log("Error SQL: " . mysql_error());
            }
        }
    }
}
if (empty($archivos)) {
    echo json_encode(['success' => false, 'message' => 'No se encontraron documentos para combinar.']);
    exit;
}
try {
    $tempDir = __DIR__ . "/temp";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Configurar mPDF con opciones que permiten mantener los formatos originales
    $mpdf = new Mpdf([
        'tempDir' => $tempDir,
        'format' => [210, 297], // A4 size in mm
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'autoPageBreak' => false, // Importante: desactivar el salto de página automático
        'keepColumns' => true,    // Mantener columnas del PDF original
        'keepFormFields' => true  // Mantener campos de formulario
    ]);

    $isFirstPage = true;
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            // Importar el archivo fuente
            $pageCount = $mpdf->SetSourceFile($archivo);
            
            for ($i = 1; $i <= $pageCount; $i++) {
                if (!$isFirstPage) {
                    $mpdf->AddPage();
                }

                // Importar la página
                $tplId = $mpdf->ImportPage($i);
                
                // Obtener el tamaño original de la página
                $size = $mpdf->GetTemplateSize($tplId);
                
                // Ajustar el tamaño de la página actual al tamaño del template original
                $mpdf->_setPageSize([$size['width'], $size['height']], $mpdf->CurOrientation);
                
                // Usar el template manteniendo las dimensiones originales y la posición
                $mpdf->UseTemplate($tplId, 0, 0, $size['width'], $size['height'], true);
                
                $isFirstPage = false;
            }
        }
    }

    $pdf_nombre = "expediente_caso_{$caso_id}_" . date("YmdHis") . ".pdf";
    $pdf_ruta = "img/casos_docs/" . $pdf_nombre;
    
    // Configurar opciones de compresión para mantener la calidad
    $mpdf->SetCompression(false);
    $mpdf->SetDisplayMode('fullpage');
    
    // Guardar el PDF
    $mpdf->Output(__DIR__ . "/../" . $pdf_ruta, 'F');

    // Insertar en la base de datos
    $sql_insert = "INSERT INTO casos_documentos (cado_nombre, caso_id, cado_ref) VALUES ('$pdf_nombre', $caso_id, '$pdf_nombre')";
    if (!mysql_query($sql_insert)) {
        throw new Exception('Error al insertar en la base de datos: ' . mysql_error());
    }

    echo json_encode(['success' => true, 'message' => 'PDF combinado generado y guardado correctamente', 'pdf_file' => $pdf_ruta]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

?>