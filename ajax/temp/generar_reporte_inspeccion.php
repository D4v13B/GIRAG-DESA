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

// Function to send JSON response
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

    // Obtener la plantilla HTML desde la tabla contratos
    $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'ASISTENCIA_PASAJEROS'";
    $result = mysql_query($stmt);
    if (!$result) {
        logError("MySQL Error: " . mysql_error());
        sendJsonResponse(["status" => "error", "message" => "Error al obtener la plantilla"]);
    }
    $plantillaHtml = mysql_fetch_assoc($result)["cont_detalle"];

    // Obtener los datos de la inspección
    $stmt = "SELECT * FROM inspecciones
                JOIN inspecciones_detalles ON inspecciones.insp_id = inspecciones_detalles.insp_id
                JOIN inspecciones_tipos ON inspecciones.inti_id = inspecciones_tipos.inti_id
                WHERE inspecciones.insp_id = $id";
    $formulario = mysql_fetch_assoc(mysql_query($stmt));

    // Obtener el nombre del usuario que inspeccionó
    $sql = "SELECT i.*, u.usua_nombre
            FROM inspecciones i
            JOIN usuarios u ON i.usua_id_inspeccion = u.usua_id
            WHERE i.insp_id = $id";
    $result = mysql_query($sql);
    $usuario = mysql_fetch_assoc($result);

    // Reemplazar los valores en la plantilla HTML
    $plantillaHtml = str_replace('[FECHA]', $formulario["insp_fecha"], $plantillaHtml);
    $plantillaHtml = str_replace('[INSPECCIONADO_POR]', $usuario["usua_nombre"], $plantillaHtml);
    $plantillaHtml = str_replace('[P_ENTRANDO]', $formulario["insp_pasajeros_entrando"], $plantillaHtml);
    $plantillaHtml = str_replace('[P_SALIENDO]', $formulario["insp_pasajeros_saliendo"], $plantillaHtml);
    $plantillaHtml = str_replace('[AEROLINEA]', $formulario["insp_numero_aerolinea"], $plantillaHtml);
    $plantillaHtml = str_replace('[VUELO]', $formulario["insp_numero_vuelo"], $plantillaHtml);
    $plantillaHtml = str_replace('[MATRICULA]', $formulario["insp_matricula"], $plantillaHtml);
    $plantillaHtml = str_replace('[A/C]', $formulario["insp_tipo"], $plantillaHtml);
    $plantillaHtml = str_replace('[HORA_LLEGADA]', $formulario["insp_hora_llegada"], $plantillaHtml);
    $plantillaHtml = str_replace('[HORA_SALIDA]', $formulario["insp_hora_salida"], $plantillaHtml);
    $plantillaHtml = str_replace('[SUPERVISOR]', $formulario["insp_supervisor_servicio"], $plantillaHtml);

    // Obtener los agentes de servicios desde la base de datos
    $agentesServicios = $formulario["insp_agentes_servicios"];

    // Verificar si el campo tiene datos
    if (!empty($agentesServicios)) {
        // Separar los nombres en un array y preparar la tabla
        $agentesArray = explode(',', $agentesServicios);
        $agentesEnTabla = "<table style='border-collapse: collapse; width: 100%;'>";

        // Crear filas de 4 celdas
        $contador = 0;
        foreach ($agentesArray as $agente) {
            // Iniciar una nueva fila si el contador es 0
            if ($contador % 4 === 0) {
                $agentesEnTabla .= "<tr>";
            }

            // Añadir una celda con estilo de borde y espacio interno
            $agenteLimpio = trim($agente);
            $agentesEnTabla .= "<td style='border: 1px solid #000; padding: 8px; text-align: center; width: 25%;'>$agenteLimpio</td>";

            $contador++;

            // Cerrar la fila después de 4 celdas
            if ($contador % 4 === 0) {
                $agentesEnTabla .= "</tr>";
            }
        }

        // Si el último grupo de celdas no cierra la fila (menos de 4 celdas), cerrar la fila
        if ($contador % 4 !== 0) {
            $agentesEnTabla .= "</tr>";
        }

        // Cerrar la tabla
        $agentesEnTabla .= "</table>";

        // Reemplazar en la plantilla
        $plantillaHtml = str_replace("[AGENTES]", $agentesEnTabla, $plantillaHtml);
    } else {
        // Si no hay agentes, dejar el campo vacío
        $plantillaHtml = str_replace("[AGENTES]", '', $plantillaHtml);
    }

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

    // Ejecutar la consulta y almacenar el resultado
    $res = mysql_query($sql);

// Inicializar la variable que almacenará las filas dinámicas de la tabla
$preguntas = "<table style='width: 100%; border-collapse: collapse; margin: auto; padding: 0;'>
                <thead>
                    <tr style='background-color: #f0f0f0;'>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>VERIFICAR/REVISAR</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>REF.</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>CUMPLIMIENTO </th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>OBSERVACIONES O
COMENTARIOS</th>
                    </tr>
                </thead>
                <tbody>";

// Verificar que hay resultados
if ($res) {
    // Recorrer los resultados y construir las filas de la tabla
    while ($item = mysql_fetch_assoc($res)) {
        // Construir cada fila con la pregunta, referencia, respuesta y comentario
        $preguntas .= "<tr style='background-color: #ffffff;'>
                        <td style='border: 1px solid #ddd; padding: 8px;'>" . $item["inpr_nombre"] . "</td> 
                        <td style='border: 1px solid #ddd; padding: 8px;'>" . $item["inpr_ref"] . "</td> 
                        <td style='border: 1px solid #ddd; padding: 8px;'>" . $item["inse_nombre"] . "</td> 
                        <td style='border: 1px solid #ddd; padding: 8px;'>" . $item["inde_comentario"] . "</td> 
                      </tr>";
    }
} else {
    // Si no hay resultados, puedes manejar el error o poner un mensaje
    echo "No se encontraron resultados.";
}

// Cerrar la tabla
$preguntas .= "</tbody></table>";

// Reemplazar la variable en la plantilla
$plantillaHtml = str_replace("[PREGUNTAS]", $preguntas, $plantillaHtml);
// Realizar la consulta para obtener imágenes y comentarios
$sql = "SELECT 
            i.*, 
            ir.*, 
            ifo.*
        FROM 
            inspecciones i
        LEFT JOIN 
            inspecciones_referencias ir ON i.insp_id = ir.insp_id
        LEFT JOIN 
            inspecciones_fotos ifo ON i.insp_id = ifo.insp_id
        WHERE 
            i.insp_id = $id";

// Ejecutar la consulta
$res = mysql_query($sql);

// Inicializar variables
$tablaHtml = "<table style='width: 100%; border-collapse: collapse; margin: 0; padding: 0;'>
                <thead>
                    <tr style='background-color: #f0f0f0;'>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Evidencias</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Acción Correctiva</th>
                    </tr>
                </thead>
                <tbody>";
$currentRefId = 0;
$imagenesRef = [];
$imagenesAccion = [];

// Verificar si hay resultados
if ($res && mysql_num_rows($res) > 0) {
    while ($item = mysql_fetch_assoc($res)) {
        // Si es una nueva referencia, procesar la actual y reiniciar las listas
        if ($item['inre_id'] != $currentRefId) {
            // Si ya hay imágenes de la referencia anterior, agregarlas a la tabla
            if ($currentRefId != 0) {
                $tablaHtml .= "<tr>";

                // Columna para "Evidencias" (tipo 1)
                $tablaHtml .= "<td>";
                if (!empty($imagenesRef)) {
                    $tablaHtml .= "<div style='text-align: center;'><strong>Evidencias</strong></div>";
                    foreach ($imagenesRef as $img) {
                        $tablaHtml .= "<div style='text-align: center;'><img src='../img/referencias/" . htmlspecialchars($img) . "' style='width: 200px; height: 150px;'></div>";
                    }
                } else {
                    $tablaHtml .= "<div style='text-align: center;'>No hay imágenes de evidencias</div>";
                }
                $tablaHtml .= "</td>";

                // Columna para "Acción Correctiva" (tipo 2)
                $tablaHtml .= "<td>";
                if (!empty($imagenesAccion)) {
                    $tablaHtml .= "<div style='text-align: center;'><strong>Acción Correctiva</strong></div>";
                    foreach ($imagenesAccion as $img) {
                        $tablaHtml .= "<div style='text-align: center;'><img src='../img/referencias/" . htmlspecialchars($img) . "' style='width: 200px; height: 150px;'></div>";
                    }
                } else {
                    $tablaHtml .= "<div style='text-align: center;'>No hay imágenes de acción correctiva</div>";
                }
                $tablaHtml .= "</td>";

                $tablaHtml .= "</tr>";
            }

            // Actualizar la referencia actual y reiniciar las listas de imágenes
            $currentRefId = $item['inre_id'];
            $imagenesRef = [];
            $imagenesAccion = [];
        }

        // Clasificar las imágenes según el tipo
        if ($item['ifot_tipo'] == 1) {
            $imagenesRef[] = $item['ifot_nombre']; // Imágenes de tipo "Evidencias"
        } elseif ($item['ifot_tipo'] == 2) {
            $imagenesAccion[] = $item['ifot_nombre']; // Imágenes de tipo "Acción Correctiva"
        }
    }

    // Procesar la última referencia después del bucle
    if ($currentRefId != 0) {
        $tablaHtml .= "<tr>";

        // Columna para "Evidencias" (tipo 1)
        $tablaHtml .= "<td>";
        if (!empty($imagenesRef)) {
            $tablaHtml .= "<div style='text-align: center;'><strong>Evidencias</strong></div>";
            foreach ($imagenesRef as $img) {
                $tablaHtml .= "<div style='text-align: center;'><img src='../img/referencias/" . htmlspecialchars($img) . "' style='width: 200px; height: 150px;'></div>";
            }
        } else {
            $tablaHtml .= "<div style='text-align: center;'>No hay imágenes de evidencias</div>";
        }
        $tablaHtml .= "</td>";

        // Columna para "Acción Correctiva" (tipo 2)
        $tablaHtml .= "<td>";
        if (!empty($imagenesAccion)) {
            $tablaHtml .= "<div style='text-align: center;'><strong>Acción Correctiva</strong></div>";
            foreach ($imagenesAccion as $img) {
                $tablaHtml .= "<div style='text-align: center;'><img src='../img/referencias/" . htmlspecialchars($img) . "' style='width: 200px; height: 150px;'></div>";
            }
        } else {
            $tablaHtml .= "<div style='text-align: center;'>No hay imágenes de acción correctiva</div>";
        }
        $tablaHtml .= "</td>";

        $tablaHtml .= "</tr>";
    }
} else {
    // No se encontraron resultados
    $tablaHtml .= "<tr><td colspan='2' style='text-align: center;'>No se encontraron imágenes o comentarios para esta inspección</td></tr>";
}

// Cerrar la tabla
$tablaHtml .= "</tbody></table>";

// Reemplazar la variable en la plantilla
$plantillaHtml = str_replace("[IMAGENES_Y_COMENTARIOS]", $tablaHtml, $plantillaHtml);

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
        '<img src="../firmas-electronicas/' . $firma_usuario_encargado . '" alt="Firma del usuario" style="width: 200px; height: 100px;" />',
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
        '<img src="../firmas-electronicas/' . $firma_usuario_ . '" alt="Firma del usuario" style="width: 200px; height: 100px;" />',
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
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h1, h2, h3 {
            text-align: center;
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
