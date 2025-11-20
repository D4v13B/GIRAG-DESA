<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from impacto_economico where imec_id=$id";
mysql_query($qsql);
?>

