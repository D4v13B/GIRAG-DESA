<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from carga_detalles where cade_id=$id";
mysql_query($qsql);
?>

