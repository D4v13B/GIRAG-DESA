<?php
// Conexión a la base de datos
include '../conexion.php';
include '../funciones.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$current_cade_tipo_id = ''; // Default empty
if (isset($_GET['carg_id']) && isset($_GET['cade_id'])) {
    $carg_id = intval($_GET['carg_id']);
    $cade_id = intval($_GET['cade_id']);

    $qsql = "SELECT 
        cd.cade_id,
        cd.carg_id,
        cd.cade_tipo_id,
        cdt.cade_descripcion AS tipo_carga
    FROM carga_detalles cd
    LEFT JOIN carga_detalle_tipo cdt ON cd.cade_tipo_id = cdt.cade_tipo_id
    WHERE cd.carg_id = $carg_id AND cd.cade_id = $cade_id";

    $result = mysql_query($qsql);

    if ($result && $row = mysql_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Registro no encontrado', 'sql_error' => mysql_error()]);
    }
} else {
    echo json_encode(['error' => 'IDs no proporcionados']);
}
?>