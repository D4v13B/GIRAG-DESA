<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios_email_config where usec_id=$id";
mysql_query($qsql);
?>

