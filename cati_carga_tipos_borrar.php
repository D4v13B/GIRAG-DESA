<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from carga_tipos where cati_id=$id";
mysql_query($qsql);
?>

