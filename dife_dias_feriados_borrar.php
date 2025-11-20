<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from dias_feriados where dife_id=$id";
mysql_query($qsql);
?>

