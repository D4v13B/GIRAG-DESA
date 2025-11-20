<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from impacto_personas where impe_id=$id";
mysql_query($qsql);
?>

