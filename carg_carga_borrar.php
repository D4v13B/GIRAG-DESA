<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from carga where carg_id=$id";
mysql_query($qsql);
?>

