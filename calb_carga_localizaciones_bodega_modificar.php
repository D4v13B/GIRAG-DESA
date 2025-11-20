<?php include('conexion.php');



// Recibir id por GET
$id = $_GET['id'];

// Recibir los datos por POST
$nombre  = $_POST['calb_nombre'];
$seccion = $_POST['calb_seccion'];
$x       = $_POST['calb_x'];
$y       = $_POST['calb_y'];
$estado  = $_POST['calb_estado'];

// Query de actualización
$qsql = "UPDATE carga_localizaciones_bodega SET 
            calb_nombre  = '$nombre',
            calb_seccion = '$seccion',
            calb_x       = '$x',
            calb_y       = '$y',
            calb_estado  = '$estado'
         WHERE calb_id = '$id'";

// Ejecutar
if (mysql_query($qsql)) {
    echo "Registro actualizado correctamente";
} else {
    echo "Error al actualizar: " . mysql_error();
}
?>



