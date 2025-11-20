<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from lineaa_aereas where cade_id=$id";
mysql_query($qsql);
?>

