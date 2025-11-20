<?php
include '../conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'recibir_carga') {
       
        $carg_id = intval($_POST['carg_id']);
        
        // DEBUG: Ver qué datos llegan
        error_log("POST recibido: " . print_r($_POST, true));
        error_log("carg_id procesado: " . $carg_id);
       
        if ($carg_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de carga inválido: ' . $carg_id
            ]);
            exit;
        }
       
        $sql = "UPDATE carga SET caes_id = 2 WHERE carg_id = $carg_id";
        
        // DEBUG: Ver la query que se ejecuta
        error_log("Query ejecutada: " . $sql);
        
        $qsql = mysql_query($sql);
       
        if ($qsql) {
            $affected = mysql_affected_rows();
            error_log("Filas afectadas: " . $affected);
            
            if ($affected > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Carga recibida correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya se recibio la Carga'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar la carga: ' . mysql_error()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>