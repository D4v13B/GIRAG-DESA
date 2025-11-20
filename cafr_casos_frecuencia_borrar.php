<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from casos_frecuencia where cafr_id=$id";
mysql_query($qsql);
?>

