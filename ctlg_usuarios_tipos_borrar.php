<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios_tipos where usti_id=$id";
mysql_query($qsql);
?>