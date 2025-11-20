<?php
ob_start();
include('../conexion.php');
include('../funciones.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

$response = ["status" => "error", "message" => "Ocurrió un error desconocido"];

// Función para enviar la respuesta JSON y terminar la ejecución
function sendJsonResponse($response)
{
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
    // Verificar si tenemos datos de cabecera
    if (!isset($_POST['cabecera']) || !is_array($_POST['cabecera'])) {
        sendJsonResponse(["status" => "error", "message" => "No se recibieron datos de cabecera"]);
    }

    // Iniciar transacción
    mysql_query("START TRANSACTION");

    $errors = [];
    foreach ($_POST['cabecera'] as $intc_id => $inca_respuesta) {
        $intc_id = intval($intc_id);
        $inca_respuesta = mysql_real_escape_string(trim($inca_respuesta));
        
        // Solo procesar si hay una respuesta
        if (!empty($inca_respuesta)) {
            // Primero verificamos si ya existe un registro para este campo
            $checkSql = "SELECT inca_id FROM inspecciones_cabecera 
                         WHERE insp_id = '$id' AND intc_id = '$intc_id'";
            $checkResult = mysql_query($checkSql);
            
            if ($checkResult && mysql_num_rows($checkResult) > 0) {
                // Existe registro, hacemos UPDATE
                  // Existe este campo específico, hacemos UPDATE
            $updateSql = "UPDATE inspecciones_cabecera
                         SET inca_respuesta = '$inca_respuesta'
                         WHERE insp_id = '$id' AND intc_id = '$intc_id'";
                
                if (!mysql_query($updateSql)) {
                    $errors[] = "Error al actualizar campo $intc_id: " . mysql_error();
                }
            } else {
                // No existe registro, hacemos INSERT
                $insertSql = "INSERT INTO inspecciones_cabecera 
                             (insp_id, intc_id, inca_respuesta) 
                             VALUES ('$id', '$intc_id', '$inca_respuesta')";
                
                if (!mysql_query($insertSql)) {
                    $errors[] = "Error al insertar campo $intc_id: " . mysql_error();
                }
            }
        } 
    }

    // Verificar si hubo errores
    if (!empty($errors)) {
        mysql_query("ROLLBACK");
        sendJsonResponse(["status" => "error", "message" => implode("\n", $errors)]);
    }

    // Si todo fue bien, confirmamos la transacción
    mysql_query("COMMIT");

    sendJsonResponse(["status" => "success", "message" => "Cabecera de inspección guardada correctamente"]);
}