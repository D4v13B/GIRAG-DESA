<?php include('conexion.php');
$id = $_GET['id']; 
$qsql ="delete from usuarios_roles where usro_id=$id";
mysql_query($qsql);
?>