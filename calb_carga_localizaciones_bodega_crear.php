<?php include('conexion.php');

$nombre = $_POST['calb_nombre'];
$seccion = $_POST['calb_seccion'];
$x = $_POST['calb_x'];
$y = $_POST['calb_y'];
$estado = $_POST['calb_estado'];


$qsql = "INSERT INTO carga_localizaciones_bodega 
(calb_nombre, calb_seccion, calb_x, calb_y, calb_estado)
        VALUES ('$nombre', '$seccion', '$x', '$y', '$estado')"; 
mysql_query($qsql);



?>

