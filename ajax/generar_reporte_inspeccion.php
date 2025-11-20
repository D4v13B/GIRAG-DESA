<?php
require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';

use PhpOffice\PhpWord\TemplateProcessor;
use Mpdf\Mpdf;

session_start();



// Function to log errors
function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../error.log');
}


function sendJsonResponse($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el ID de la inspección desde GET o POST
    $id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;
    if ($id === 0 && isset($_POST['insp_id'])) {
        $id = intval($_POST['insp_id']);
    }
    if ($id === 0) {
        sendJsonResponse(["status" => "error", "message" => "ID de inspección no válido"]);
    }

    // Obtener los datos de la inspección
    $stmt = "SELECT * FROM inspecciones
JOIN inspecciones_detalles ON inspecciones.insp_id = inspecciones_detalles.insp_id
JOIN inspecciones_tipos ON inspecciones.inti_id = inspecciones_tipos.inti_id
WHERE inspecciones.insp_id = $id";
    $formulario = mysql_fetch_assoc(mysql_query($stmt));

    // Determinar qué plantilla buscar según el inti_id
    if ($formulario['inti_id'] == 1) {
        $nombrePlantilla = 'ASISTENCIA_PASAJEROS';
    } elseif ($formulario['inti_id'] == 2) {
        $nombrePlantilla = 'PLATAFORMA';
    } elseif ($formulario['inti_id'] == 3) {
        $nombrePlantilla = 'BODEGA';
    } elseif ($formulario['inti_id'] == 4) {
        $nombrePlantilla = 'CONDICIONES_GENERALES';
    } else {
        die("Tipo de inspección no válido");
    }

    // Obtener la plantilla HTML desde la tabla contratos
    $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = '$nombrePlantilla'";
    $result = mysql_query($stmt);
    if (!$result) {
        die("Error al obtener la plantilla: " . mysql_error());
    }
    $plantillaHtml = mysql_fetch_assoc($result)["cont_detalle"];

    // Obtener los datos de la inspección
    $stmt = "SELECT * FROM inspecciones
JOIN inspecciones_detalles ON inspecciones.insp_id = inspecciones_detalles.insp_id
JOIN inspecciones_tipos ON inspecciones.inti_id = inspecciones_tipos.inti_id
LEFT JOIN inspecciones_tipo_operacion ON inspecciones.into_id = inspecciones_tipo_operacion.into_id
WHERE inspecciones.insp_id =  $id";
    $formulario = mysql_fetch_assoc(mysql_query($stmt));

    // Obtener el nombre del usuario que inspeccionó
    $sql = "SELECT i.*, u.usua_nombre
            FROM inspecciones i
            JOIN usuarios u ON i.usua_id_inspeccion = u.usua_id
            WHERE i.insp_id = $id";
    $result = mysql_query($sql);
    $usuario = mysql_fetch_assoc($result);



    // 1. Mapeo de etiquetas a placeholders
$mapaEtiquetas = [
    'Pasajeros Entrando' => '[P_ENTRANDO]',
    'Pasajeros Saliendo' => '[P_SALIENDO]',
    'Número de Aerolínea' => '[AEROLINEA]',
    'Número de vuelo' => '[VUELO]',
    'Número de Vuelo' => '[VUELO]',
    'Número de Matrícula' => '[MATRICULA]',
    'Tipo de A/C' => '[A/C]',
    'Hora de llegada' => '[HORA_LLEGADA]',
    'Hora de entrada' => '[HORA_LLEGADA]',
    'Hora de salida' => '[HORA_SALIDA]',
    'Supervisor de Pasajeros' => '[SUPERVISOR]',
    'Agentes de Servicios a pasajeros' => '[AGENTES]',
    'Tipo de Operación' => '[OPERACION]',
];

// 2. Obtener los valores dinámicos desde la base de datos
$dataEtiquetas = [];
$sql = "SELECT 
            tc.intc_etiqueta,
            c.inca_respuesta
        FROM inspecciones_tipo_cabecera tc
        LEFT JOIN inspecciones_cabecera c ON tc.intc_id = c.intc_id
        WHERE c.insp_id = $id
        ORDER BY tc.intc_id";

$result = mysql_query($sql);
while ($row = mysqli_fetch_assoc($result)) {
    $etiqueta = $row['intc_etiqueta'];
    $valor = $row['inca_respuesta'];

    

        // Si es la etiqueta de agentes, genera tabla
        if (isset($mapaEtiquetas[$etiqueta])) {
    $placeholder = $mapaEtiquetas[$etiqueta];
if (isset($mapaEtiquetas[$etiqueta]) && $etiqueta === 'Agentes de Servicios a pasajeros') {
    $placeholder = $mapaEtiquetas[$etiqueta];
    $agentesArray = !empty($valor) ? array_filter(array_map('trim', explode(',', $valor))) : [];
    $totalAgentes = count($agentesArray);
    $filasNecesarias = max(1, ceil($totalAgentes / 4));
    
    $tablaHTML = '<table style="
        border-collapse: collapse;
        border-spacing: 0;
        margin: 0;
        padding: 0;
        font-family: Tahoma, sans-serif;
        font-size: 10pt;
        table-layout: fixed;
        width: 100%;
        line-height: 1;
    ">';
    
    for ($fila = 0; $fila < $filasNecesarias; $fila++) {
        $tablaHTML .= '<tr style="
            height: 35px; 
            padding: 0; 
            margin: 0;
            line-height: 1;
        ">';
        
        for ($col = 0; $col < 4; $col++) {
            $indice = ($fila * 4) + $col;
            $contenido = ($indice < $totalAgentes) ? htmlspecialchars($agentesArray[$indice]) : '&nbsp;';
            
            $tablaHTML .= '<td style="
                width: 130px;
                border: 1px solid #000;
                padding: 6px;
                text-align: center;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                margin: 0;
                vertical-align: middle;
                line-height: 1;
                box-sizing: border-box;
            ">' . $contenido . '</td>';
        }
        $tablaHTML .= '</tr>';
    }
    $tablaHTML .= '</table>';
    
    $plantillaHtml = str_replace($placeholder, $tablaHTML, $plantillaHtml);
}

else {
            // Reemplazo normal
            $plantillaHtml = str_replace($placeholder, $valor, $plantillaHtml);
        }
    }
}

// 3. Reemplazos adicionales fuera de la tabla
$plantillaHtml = str_replace('[FECHA]', $formulario["insp_fecha"], $plantillaHtml);
$plantillaHtml = str_replace('[INSPECCIONADO_POR]', $usuario["usua_nombre"], $plantillaHtml);
$plantillaHtml = str_replace('[HORA]', $formulario["insp_hora"], $plantillaHtml);

    // Preguntas
    // Realizar la consulta
    $sql = "SELECT 
i.*, 
id.*, 
inpr.*, 
isel.*
FROM 
inspecciones i
JOIN 
inspecciones_detalles id ON i.insp_id = id.insp_id
JOIN 
inspecciones_preguntas inpr ON id.inpr_id = inpr.inpr_id
JOIN 
inspecciones_seleccion isel ON id.inse_id = isel.inse_id
WHERE 
i.insp_id = $id";


    $res = mysql_query($sql);
    $preguntas = "<table style='width: 100%; border-collapse: collapse; margin: auto; padding: 0; font-family: Tahoma, Arial, sans-serif; font-size: 12pt;'>
<thead>
    <tr style='background-color: #f0f0f0;'>
        <th style='border: 1px solid #ddd; padding: 8px; text-align: left; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>N°</th>
        <th style='border: 1px solid #ddd; padding: 8px; text-align: left; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>VERIFICAR/REVISAR</th>";

if ($nombrePlantilla != 'CONDICIONES_GENERALES') {
    $preguntas .= "<th style='border: 1px solid #ddd; padding: 8px; text-align: left; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>REF.</th>";
}

$preguntas .= "
        <th style='border: 1px solid #ddd; padding: 8px; text-align: left; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>CUMPLIMIENTO</th>
        <th style='border: 1px solid #ddd; padding: 8px; text-align: left; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>OBSERVACIONES O COMENTARIOS</th>
    </tr>
</thead>
<tbody>";

if ($res) {
    $contador = 1; // Inicializar el contador

    while ($item = mysql_fetch_assoc($res)) {
        $preguntas .= "<tr style='background-color: #ffffff;'>
        <td style='border: 1px solid #ddd; padding: 8px; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>" . $contador . "</td>
        <td style='border: 1px solid #ddd; padding: 8px; text-align: justify; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>" . $item["inpr_nombre"] . "</td>";

        if ($nombrePlantilla != 'CONDICIONES_GENERALES') {
            $preguntas .= "<td style='border: 1px solid #ddd; padding: 8px; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>" . $item["inpr_ref"] . "</td>";
        }

        $preguntas .= "
        <td style='border: 1px solid #ddd; padding: 8px; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>" . $item["inse_nombre"] . "</td>
        <td style='border: 1px solid #ddd; padding: 8px; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;'>" . $item["inde_comentario"] . "</td>
    </tr>";

        $contador++; // Aumentar el contador
    }
}

$preguntas .= "</tbody></table>";

$plantillaHtml = str_replace("[PREGUNTAS]", $preguntas, $plantillaHtml);
 // Modificamos la consulta SQL para asegurarnos de obtener la referencia correcta
    // REFERENCIAS
    $sql = "SELECT 
            i.insp_id,
            ir.inre_id,
            ir.inre_nombre AS nombre_hallazgo,
            ir.inre_comentario,
            ifo.info_tipo,
            ifo.info_ruta
        FROM 
            inspecciones i
        LEFT JOIN 
            inspecciones_referencias ir ON i.insp_id = ir.insp_id
        LEFT JOIN 
            inspecciones_fotos ifo ON ir.inre_id = ifo.inre_id
        WHERE 
            i.insp_id = $id
        ORDER BY 
            ir.inre_id";


    $res = mysql_query($sql);

    $tablaHtml = "";


    if (mysql_num_rows($res) > 0) {

        $referencias_procesadas = array();
        $numero = 1;

        while ($row = mysql_fetch_assoc($res)) {
            $inre_id = $row['inre_id'];


            if (!isset($referencias_procesadas[$inre_id])) {
                $tablaHtml .= '<div class="referencia">';
                $tablaHtml .= '<p style ="border: 1px solid #ddd; padding: 8px; font-family: Tahoma, Arial, sans-serif; font-size: 9pt;"><strong>Referencia ' . $numero . ':</strong> ' .
                    nl2br(htmlspecialchars($row['inre_comentario'])) . '</p>';
                $tablaHtml .= '</div>';


                $referencias_procesadas[$inre_id] = true;
                $numero++;
            }
        }
    } else {
        $tablaHtml = '<p>No se encontraron referencias para esta inspección.</p>';
    }

    $plantillaHtml = str_replace("[REFERENCIAS]", $tablaHtml, $plantillaHtml);

    $sql = "SELECT 
            i.insp_id,
            ir.inre_id,
            ir.inre_nombre AS nombre_hallazgo,
            ir.inre_comentario,
            ifo.info_tipo,
            ifo.info_ruta
        FROM 
            inspecciones i
        LEFT JOIN 
            inspecciones_referencias ir ON i.insp_id = ir.insp_id
        LEFT JOIN 
            inspecciones_fotos ifo ON ir.inre_id = ifo.inre_id
        WHERE 
            i.insp_id = $id
        ORDER BY 
            ir.inre_id";

    // Ejecutar la consulta
    $res = mysql_query($sql);

    // Inicializar variables
    $tablaHtml = "<table style='border-collapse: collapse; width: 100%; background-color: white; color: black;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>";
    $numeroHallazgo = 1;
    $tamanoImagen = "width: 150px; height: 125px;";
    $referencias = [];

    // Primera pasada: Organizar las imágenes por referencia
    while ($item = mysql_fetch_assoc($res)) {
        $refId = $item['inre_id'];

        // Si es una nueva referencia, inicializamos sus arrays
        if (!isset($referencias[$refId])) {
            $referencias[$refId] = [
                'nombre' => $item['nombre_hallazgo'], // Usamos el nombre correcto del hallazgo
                'imagenesRef' => [],
                'imagenesAccion' => []
            ];
        }

        // Añadir la imagen al array correspondiente solo si existe y no está duplicada
        if (isset($item['info_tipo']) && isset($item['info_ruta']) && !empty($item['info_ruta'])) {
            if ($item['info_tipo'] == 1 && !in_array($item['info_ruta'], $referencias[$refId]['imagenesRef'])) {
                $referencias[$refId]['imagenesRef'][] = $item['info_ruta'];
            } elseif ($item['info_tipo'] == 2 && !in_array($item['info_ruta'], $referencias[$refId]['imagenesAccion'])) {
                $referencias[$refId]['imagenesAccion'][] = $item['info_ruta'];
            }
        }
    }

    // Función para generar el contenedor de imágenes
    function generarContenedorImagenes($imagenes, $tamanoImagen)
    {
        if (empty($imagenes)) {
            return "<div>Sin imágenes</div>";
        }

        $imagenes = array_unique($imagenes);

        $html = "<div style='width: 100%;'>";
        foreach ($imagenes as $imagen) {
            if (!empty($imagen)) {
                $html .= "<span style='display: inline-block; margin-right: 10px;'>";
                $html .= "<img src='../img/referencias/" . htmlspecialchars($imagen) . "' style='{$tamanoImagen}'>";
                $html .= "</span>";
            }
        }
        $html .= "</div>";
        return $html;
    }
    if (!empty($referencias)) {
        foreach ($referencias as $refId => $referencia) {
            if (!empty($refId)) { // Asegurarnos de que no procesamos referencias vacías

                // Cabeceras de columnas
                $tablaHtml .= "<tr>
                            <td style='border: 1px solid black; text-align: center; padding: 5px; background-color: #e9ecef;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>Evidencia del Hallazgo #{$numeroHallazgo}: " . htmlspecialchars($referencia['nombre']) . "</td>
                            <td style='border: 1px solid black; text-align: center;padding: 5px; background-color: #e9ecef;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>Acción Correctiva #{$numeroHallazgo}: " . htmlspecialchars($referencia['nombre']) . "</td>
                          </tr>";

                // Fila de imágenes
                $tablaHtml .= "<tr>";
                $tablaHtml .= "<td style='border: 1px solid black; vertical-align: middle; padding: 10px;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>";
                $tablaHtml .= generarContenedorImagenes($referencia['imagenesRef'], $tamanoImagen);
                $tablaHtml .= "</td>";

                $tablaHtml .= "<td style='border: 1px solid black; vertical-align: middle; padding: 10px;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>";
                $tablaHtml .= generarContenedorImagenes($referencia['imagenesAccion'], $tamanoImagen);
                $tablaHtml .= "</td>";
                $tablaHtml .= "</tr>";

                $numeroHallazgo++;
            }
        }
    } else {
        $tablaHtml .= "<tr><td colspan='2' style='border: 1px solid black; text-align: center;font-family: Tahoma, Arial, sans-serif; font-size: 9pt'>No se encontraron resultados.</td></tr>";
    }

    $tablaHtml .= "</table>";

    // Reemplazar el marcador en la plantilla con la tabla generada
    $plantillaHtml = str_replace("[EVIDENCIAS]", $tablaHtml, $plantillaHtml);


    $sql = "SELECT 
    u.*, 
    uc.*, 
    uf.*
FROM usuarios u
JOIN inspecciones i ON u.usua_id = i.usua_id_inspeccion
LEFT JOIN usuarios_cargos uc ON u.usua_id = uc.usca_id  -- Relacionando usuarios con usuarios_cargos a través de usua_id y usca_id
LEFT JOIN usuarios_firmas uf ON u.usua_id = uf.usua_id
WHERE i.insp_id =  $id";

    $usuario_encargado = mysql_fetch_assoc(mysql_query($sql));

    $plantillaHtml = str_replace('[USUA_ENCARGADO_FIRMA]', $usuario_encargado["usua_nombre_completo"], $plantillaHtml);
    $plantillaHtml = str_replace('[USUA_CEDULA]', $usuario_encargado["usua_cedula"] ?? '', $plantillaHtml);
    $plantillaHtml = str_replace('[USUA_CARGO]', $usuario_encargado["usca_nombre"] ?? '', $plantillaHtml);
    $firma_usuario_encargado = $usuario_encargado["usfi_ref"];
    $plantillaHtml = str_replace(
        '[USUA_FIRMA]',
        '<img src="../firmas-electronicas/' . $firma_usuario_encargado . '" alt="Firma del usuario" style="width: 170px; height: 60px;" />',
        $plantillaHtml
    );
    // Para el encargado de Calidad
    $sql = "SELECT 
    u.*, 
    uc.*, 
    uf.*
FROM usuarios u
JOIN inspecciones i ON u.usua_id = '138'
LEFT JOIN usuarios_cargos uc ON u.usua_id = uc.usca_id  -- Relacionando usuarios con usuarios_cargos a través de usua_id y usca_id
LEFT JOIN usuarios_firmas uf ON u.usua_id = uf.usua_id
WHERE i.insp_id =  $id";
    $usuario_ = mysql_fetch_assoc(mysql_query($sql));

    $plantillaHtml = str_replace('[USUARIO]', $usuario_["usua_nombre_completo"], $plantillaHtml);
    $plantillaHtml = str_replace('[USUARIO_CEDULA]', $usuario_["usua_cedula"] ?? '', $plantillaHtml);
    $plantillaHtml = str_replace('[USUARIO_CARGO]', $usuario_["usca_nombre"] ?? '', $plantillaHtml);

    $firma_usuario_ = $usuario_["usfi_ref"];
    $plantillaHtml = str_replace(
        '[USUARIO_FIRMA]',
        '<img src="../firmas-electronicas/' . $firma_usuario_ . '" alt="Firma del usuario" style="width: 170px; height: 60px;" />',
        $plantillaHtml
    );



    // Crear una nueva instancia de mPDF con configuración de márgenes
    $mpdf = new Mpdf([
        'tempDir' => __DIR__ . "/temp",
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_header' => 5,
        'margin_footer' => 5
    ]);

    // Estilo CSS embebido para mejorar la presentación del PDF
    $css = "
    <style>
        body {
               font-family: Tahoma, sans-serif;
            line-height: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-botton
            
        }
        th, td {
            border: 1px solid #000;
            padding-botton: 5px;
            
        }
        th {
            background-color: #f2f2f2;
        }
        h1, h2, h3 {
            
        }
    </style>";

    // Incluir el CSS y la plantilla HTML
    $mpdf->WriteHTML($css, 1);  // Agregar los estilos
    $mpdf->WriteHTML($plantillaHtml, 2);  // Agregar el contenido

    // Definir el nombre del archivo sin la ruta completa
    $nombreArchivo = "reporte_" . $id . ".pdf";
    $rutaReporte = "../inspecciones/" . $nombreArchivo;

    // Guardar el archivo final en la ruta especificada
    try {
        $mpdf->Output($rutaReporte, 'F');
    } catch (Exception $e) {
        logError("Error al guardar el reporte: " . $e->getMessage());
        sendJsonResponse(["status" => "error", "message" => "Error al guardar el reporte"]);
    }

    // Actualizar la referencia del archivo en la base de datos (solo el nombre del archivo)
    $updateSql = "UPDATE inspecciones SET insp_reporte = '$nombreArchivo' WHERE insp_id = $id";
    mysql_query($updateSql);

    // Enviar la respuesta de éxito
    sendJsonResponse(["status" => "success", "message" => "Reporte PDF generado exitosamente", "file" => $nombreArchivo]);
} else {
    // En caso de que no sea POST
    sendJsonResponse(["status" => "error", "message" => "Método de solicitud no válido"]);
}
