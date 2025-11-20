<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from equipos where equi_id=$id";
mysql_query($qsql);
?>

