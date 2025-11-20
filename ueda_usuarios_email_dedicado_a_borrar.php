<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios_email_dedicado_a where usmd_id=$id";
mysql_query($qsql);
?>

