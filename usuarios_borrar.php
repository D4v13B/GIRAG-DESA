<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios where usua_id=$id";
mysql_query($qsql);
?>

