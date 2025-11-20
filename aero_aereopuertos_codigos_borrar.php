<?php
include('conexion.php');

$id = intval($_GET['id']); // Validar que sea número
$qsql = "DELETE FROM aereopuertos_codigos WHERE aeco_id = $id";

$result = mysql_query($qsql);

if ($result) {
    echo "<div class='mensaje_exito'>Aeropuerto eliminado correctamente.</div>";
} else {
    // Error común: restricción de clave foránea
    echo "<div class='mensaje_error'>No se puede borrar este aeropuerto porque tiene registros relacionados.</div>";
}
?>
