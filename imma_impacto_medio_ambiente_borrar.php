<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from impacto_medio_ambiente where imma_id=$id";
mysql_query($qsql);
?>

