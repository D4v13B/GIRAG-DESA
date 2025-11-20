<?php
include '../conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'verificar_estado') {
        
        $carg_id = intval($_POST['carg_id']);
        
        if ($carg_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de carga inválido'
            ]);
            exit;
        }
        
        // Consultar el estado actual de la carga
        $sql = "SELECT caes_id FROM carga WHERE carg_id = $carg_id";
        $qsql = mysql_query($sql);
        
        if ($qsql) {
            $row = mysql_fetch_assoc($qsql);
            if ($row) {
                echo json_encode([
                    'success' => true,
                    'estado' => intval($row['caes_id'])
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Carga no encontrada'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error en consulta: ' . mysql_error()
            ]);
        }
    }
}
?>