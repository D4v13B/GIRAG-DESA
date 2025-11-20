<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from incidencia_procesos where inpr_id=$id";
mysql_query($qsql);
?>

