<?php 
include('conexion.php'); 

if (isset($_GET['id_inspeccion'])) {
    $id = intval($_GET['id_inspeccion']); // Seguridad: asegurar que sea número

    $qsql = "DELETE FROM inspecciones WHERE insp_id = $id";
    $resultado = mysql_query($qsql);

    if ($resultado) {
        echo 'ok';
    } else {
        echo 'Error al eliminar la inspección: ' . mysql_error();
    }
} else {
    echo 'ID de inspección no recibido.';
}
