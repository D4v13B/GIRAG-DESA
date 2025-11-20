<?php
// Conexión a la base de datos
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cade_id = intval($_POST['cade_id']);
    $cade_tipo_id = intval($_POST['cade_tipo_id']);

    // Actualizar el registro
    $qsql = "UPDATE cargas_detalle SET cade_tipo_id = $cade_tipo_id WHERE cade_id = $cade_id";
    $result = mysql_query($qsql);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar el registro']);
    }
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>
