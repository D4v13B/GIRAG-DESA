<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from carga_localizaciones_bodega where calb_id=$id";
mysql_query($qsql);
?>

