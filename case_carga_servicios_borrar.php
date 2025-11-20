<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from carga_servicios where case_id=$id";
mysql_query($qsql);
?>

