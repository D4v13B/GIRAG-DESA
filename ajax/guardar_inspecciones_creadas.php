<?php
header('Content-Type: application/json');
include('../conexion.php');
include('../funciones.php');
session_start();

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"), true);

$response = array('success' => false, 'message' => '');

// Validar datos básicos
if (empty($data['department_id']) || empty($data['title'])) {
    $response['message'] = 'Faltan datos requeridos';
    echo json_encode($response);
    exit();
}

// Iniciar transacción


// 1. Guardar información básica de la inspección
$title = mysql_real_escape_string($data['title']);
$department_id = intval($data['department_id']);
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$query = "INSERT INTO inspecciones_tipos (inti_nombre, depa_id) VALUES ('$title', '$department_id')";
$result = mysql_query($query);

if (!$result) {
    $response['message'] = 'Error al guardar inspección: ' . mysql_error();
    echo json_encode($response);
    exit();
}

$inspection_id = mysql_insert_id();

// 2. Guardar campos del formulario (si existen)
if (!empty($data['fields'])) {
    foreach ($data['fields'] as $field) {
        $label = mysql_real_escape_string($field['label']);
        $type = mysql_real_escape_string($field['type']);
        
        $query = "INSERT INTO inspecciones_tipo_cabecera (inti_id, intc_etiqueta, intc_tipo_campo) 
                 VALUES ($inspection_id, '$label', '$type')";
        
        if (!mysql_query($query)) {
            mysql_query("ROLLBACK");
            $response['message'] = 'Error al guardar campo: ' . mysql_error();
            echo json_encode($response);
            exit();
        }
    }
}

// 3. Guardar preguntas (si existen)
if (!empty($data['questions'])) {
    foreach ($data['questions'] as $question) {
        $text = mysql_real_escape_string($question['text']);
        $ref = isset($question['ref']) ? "'".mysql_real_escape_string($question['ref'])."'" : "NULL";
        
        $query = "INSERT INTO inspecciones_preguntas (inti_id, inpr_nombre, inpr_ref) 
                 VALUES ($inspection_id, '$text', $ref)";
        
        if (!mysql_query($query)) {
            mysql_query("ROLLBACK");
            $response['message'] = 'Error al guardar pregunta: ' . mysql_error();
            echo json_encode($response);
            exit();
        }
    }
}

// Si todo salió bien, confirmar transacción
mysql_query("COMMIT");

$response['success'] = true;
$response['inspection_id'] = $inspection_id;
$response['message'] = 'Inspección y componentes guardados correctamente';

echo json_encode($response);

// Cerrar conexión
if(isset($conexion)) {
    mysql_close($conexion);
}
?>