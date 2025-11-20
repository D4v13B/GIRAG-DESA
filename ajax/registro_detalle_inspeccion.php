
<?php
//REGISTRA LAS PREGUNTAS DE LA INSPECCIÓN
ob_start();
include('../conexion.php');
include('../funciones.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

$response = ["status" => "error", "message" => "Ocurrió un error desconocido"];

// Función para enviar la respuesta JSON y terminar la ejecución
function sendJsonResponse($response) {
    ob_clean();
    echo json_encode($response);
    exit;
}

// Obtener el ID de la inspección desde GET o POST
$id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;
if ($id === 0 && isset($_POST['insp_id'])) {
    $id = intval($_POST['insp_id']);
}

if ($id === 0) {
    sendJsonResponse(["status" => "error", "message" => "ID de inspección no válido"]);
}

$usuario = $_SESSION['login_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar si se han recibido las respuestas de las selecciones
    $hay_respuestas = false;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'seleccion') === 0) {
            $hay_respuestas = true;
            break;
        }
    }

    if (!$hay_respuestas) {
        sendJsonResponse(["status" => "error", "message" => "No se recibieron datos de la inspección."]);
    }

    // Obtener las preguntas relacionadas
    $inti_query = mysql_query("SELECT inti_id FROM inspecciones WHERE insp_id = '$id'");
    if (!$inti_query) {
        sendJsonResponse(["status" => "error", "message" => "Error en la consulta: " . mysql_error()]);
    }

    $inti_result = mysql_fetch_assoc($inti_query);
    $inti_id = $inti_result['inti_id'];

    if (!$inti_id) {
        sendJsonResponse(["status" => "error", "message" => "No se encontró la inspección."]);
    }

    $preguntas = mysql_query("SELECT inpr_id FROM inspecciones_preguntas WHERE inti_id = '$inti_id'");
    if (!$preguntas) {
        sendJsonResponse(["status" => "error", "message" => "Error al obtener preguntas: " . mysql_error()]);
    }

    $respuestas = [];

    // Recorremos todas las preguntas y almacenamos las respuestas
    while ($pregunta = mysql_fetch_assoc($preguntas)) {
        $pregunta_id = $pregunta['inpr_id'];
        $respuesta_seleccionada = isset($_POST['seleccion' . $pregunta_id]) ? $_POST['seleccion' . $pregunta_id] : null;
        $comentario = isset($_POST['comentarios_' . $pregunta_id]) ? $_POST['comentarios_' . $pregunta_id] : '';

        if ($respuesta_seleccionada !== null) {
            $respuestas[] = [
                'pregunta_id' => $pregunta_id,
                'respuesta_seleccionada' => $respuesta_seleccionada,
                'comentario' => $comentario
            ];
        }
    }

    // Insertamos las respuestas
    $error_occurred = false;
    foreach ($respuestas as $respuesta) {
        $pregunta_id = mysql_real_escape_string($respuesta['pregunta_id']);
        $respuesta_seleccionada = mysql_real_escape_string($respuesta['respuesta_seleccionada']);
        $comentario = mysql_real_escape_string($respuesta['comentario']);

        $sql = "INSERT INTO inspecciones_detalles (insp_id, inpr_id, inse_id, inde_comentario) 
                VALUES ('$id', '$pregunta_id', '$respuesta_seleccionada', '$comentario')";

        if (!mysql_query($sql)) {
            $error_occurred = true;
            break;
        }
    }

    if ($error_occurred) {
        sendJsonResponse(["status" => "error", "message" => "Error al insertar respuestas: " . mysql_error()]);
    } else {
        sendJsonResponse(["status" => "success", "message" => "Inspección registrada correctamente."]);
    }
} else {
    sendJsonResponse(["status" => "error", "message" => "Método de solicitud no válido."]);
}

// Si llegamos hasta aquí, algo salió mal
sendJsonResponse($response);
?>