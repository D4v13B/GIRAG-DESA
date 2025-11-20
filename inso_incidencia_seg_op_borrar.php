<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from incidencia_seg_op where inso_id=$id";
mysql_query($qsql);
?>

