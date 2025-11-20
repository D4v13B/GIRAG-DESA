<?php
require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';
use Mpdf\Mpdf;
use PhpOffice\PhpWord\TemplateProcessor;
// ID del caso y documento a convertir
$caso_id = $_POST['caso_id'] ?? null;
$documento_id = $_POST['documento_id'] ?? null; // Puede ser "doc_123" o "tado_456"

if (!$caso_id || !$documento_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Obtener plantilla CONVERSOR (solo una vez)
$sql_plantilla = "SELECT * FROM contratos WHERE cont_nombre = 'CONVERSOR'";
$result_plantilla = mysql_query($sql_plantilla);
if (!$result_plantilla || mysql_num_rows($result_plantilla) == 0) {
    echo json_encode(['success' => false, 'message' => 'No se encontró la plantilla CONVERSOR']);
    exit;
}
$plantilla = mysql_fetch_assoc($result_plantilla);

$directorio_archivos = __DIR__ . "/../img/casos_docs/";
$resultado = [];

try {
    // Determinar si es documento regular o de tarea
    if (strpos($documento_id, 'doc_') === 0) {
        // Documento regular
        $doc_id = str_replace('doc_', '', $documento_id);
        $sql = "SELECT * FROM casos_documentos WHERE cado_id = " . intval($doc_id);
        $result = mysql_query($sql);
        $documento = mysql_fetch_assoc($result);
        
        if (!$documento) {
            throw new Exception('Documento no encontrado');
        }
        
        $nombre_original = $documento['cado_ref'];
        $ruta_original = $directorio_archivos . $nombre_original;
    } 
    elseif (strpos($documento_id, 'tado_') === 0) {
        // Documento de tarea
        $tarea_id = str_replace('tado_', '', $documento_id);
        $sql = "SELECT * FROM tareas_documentos WHERE tado_id = " . intval($tarea_id);
        $result = mysql_query($sql);
        $documento = mysql_fetch_assoc($result);
        
        if (!$documento) {
            throw new Exception('Documento de tarea no encontrado');
        }
        
        $nombre_original = $documento['tado_ref'];
        $ruta_original = $directorio_archivos . $nombre_original;
    } 
    else {
        throw new Exception('Formato de ID inválido');
    }

    // Verificar que el archivo exista
    if (!file_exists($ruta_original)) {
        throw new Exception('Archivo original no encontrado en el servidor');
    }

    // Determinar tipo de archivo por extensión
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    $contenido_html = '';

    // Procesar según el tipo de archivo
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
        case 'png':
            // Para imágenes, incrustar directamente en el PDF
            $contenido_html = str_replace(
                "[DOCUMENTO]", 
                '<img src="' . $ruta_original . '" style="max-width: 250px; width: 80%; height: auto; display: block; margin: 5px auto;">', 
                $plantilla["cont_detalle"]
            );
            break;
            
            case 'docx':
                // Para Word, usamos PHPWord para convertir a HTML y luego MPDF para PDF
                try {
                    
                    // Cargar el documento Word
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($ruta_original);
                    
                    // Primero guardar como HTML temporal
                    $tempHtmlPath = $directorio_archivos . "temp_" . time() . ".html";
                    $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
                    $htmlWriter->save($tempHtmlPath);
                    
                    // Leer el contenido HTML generado
                    $htmlContent = file_get_contents($tempHtmlPath);
                    
                    // Normalizar el HTML eliminando tags innecesarios o problemáticos
                    $htmlContent = str_replace(['<w:sym', '<w:br', '</w:p>', '</w:sym>', '</w:br>'], '', $htmlContent);
                    $htmlContent = preg_replace('/<div[^>]*>/i', '<div>', $htmlContent); // Limpiar tags div
                    
                    // Aplicar CSS personalizado para ajustar el formato
                    $customCss = "
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }
                        h1, h2, h3, h4, h5, h6 { page-break-before: avoid; }
                        p { page-break-inside: avoid; margin: 0 0 12pt 0; }
                        table { border-collapse: collapse; width: 100%; }
                        table, th, td { border: 1px solid #ddd; }
                        th, td { padding: 8px; text-align: left; }
                    </style>";
                    
                    $htmlContent = $customCss . $htmlContent;
                    
                    // Insertar en la plantilla
                    $contenido_html = str_replace(
                        "[DOCUMENTO]", 
                        $htmlContent, 
                        $plantilla["cont_detalle"]
                    );
                    
                    // Eliminar archivo temporal HTML
                    unlink($tempHtmlPath);
                    
                } catch (Exception $e) {
                    // En caso de error, mostrar el contenido básico
                    $contenido_html = str_replace(
                        "[DOCUMENTO]", 
                        '<p>Documento Word: ' . htmlspecialchars($nombre_original) . '</p>' .
                        '<p>Error al convertir: ' . htmlspecialchars($e->getMessage()) . '</p>', 
                        $plantilla["cont_detalle"]
                    );
                }
                break;
            
                case 'eml':
                    // Leer el archivo EML con codificación UTF-8
                    $contenido_eml = file_get_contents($ruta_original);
                    
                    
                    $encoding_detectada = mb_detect_encoding($contenido_eml, 'UTF-8, ISO-8859-1', true);
                    if ($encoding_detectada !== 'UTF-8') {
                        $contenido_eml = mb_convert_encoding($contenido_eml, 'UTF-8', $encoding_detectada);
                    }
                
                    
                    if (preg_match("/\r?\n\r?\n(.*)/s", $contenido_eml, $matches)) {
                        $contenido_limpio = trim($matches[1]);
                
                        
                        $contenido_limpio = html_entity_decode($contenido_limpio, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                        
                        $contenido_limpio = strip_tags($contenido_limpio);
                    } else {
                        $contenido_limpio = 'Contenido no disponible';
                    }
                
                    // Convertir a HTML para mostrar con formato
                    $contenido_html = str_replace(
                        "[DOCUMENTO]",
                        '<pre>' . nl2br(htmlspecialchars($contenido_limpio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</pre>',
                        $plantilla["cont_detalle"]
                    );
                    break;
                
                    
                default:
                    throw new Exception('Tipo de archivo no soportado para conversión');
    }

    // Generar PDF
    $tempDir = __DIR__ . "/temp";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    $mpdf = new Mpdf([
        'tempDir' => $tempDir, 
        'format' => 'Letter',
        'default_font' => 'arial'
    ]);
    
    $mpdf->WriteHTML($contenido_html);
    
    // Nombre del PDF resultante
    $pdf_nombre = "conv_" . time() . "_" . pathinfo($nombre_original, PATHINFO_FILENAME) . ".pdf";
    $pdf_ruta = $directorio_archivos . $pdf_nombre;
    $mpdf->Output($pdf_ruta, 'F');

    if (!file_exists($pdf_ruta)) {
        throw new Exception('No se pudo guardar el PDF generado');
    }

    // Insertar nuevo documento PDF en la base de datos
    $sql_insert = "INSERT INTO casos_documentos 
                  (cado_nombre, caso_id, cado_ref) 
                  VALUES (
                      '" . mysql_real_escape_string($pdf_nombre) . "', 
                      " . intval($caso_id) . ", 
                      '" . mysql_real_escape_string($pdf_nombre) . "'
                  )";
    
    $resultado_insert = mysql_query($sql_insert);
    if (!$resultado_insert) {
        throw new Exception('Error al guardar el PDF en la base de datos: ' . mysql_error());
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Documento convertido a PDF correctamente',
        'nombre_original' => $nombre_original,
        'nombre_pdf' => $pdf_nombre,
        'ruta_pdf' => $pdf_ruta
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}