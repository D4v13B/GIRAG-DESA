<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from casos_subclasificacion where casu_id=$id";
mysql_query($qsql);
?>

