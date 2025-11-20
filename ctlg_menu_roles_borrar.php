<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from menu_roles where mero_id=$id";
mysql_query($qsql);
?>