<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios_cargos where usca_id=$id";
mysql_query($qsql);
?>

