<?php include('conexion.php'); 
$id = $_GET['id']; 
$qsql ="delete from pantalla_roles where paro_id=$id";
mysql_query($qsql);
?>

