<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from usuarios_firmas where usfi_id ='$id'";
mysql_query($qsql);
?>

