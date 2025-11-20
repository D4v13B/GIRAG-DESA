<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from shipper where ship_id=$id";
mysql_query($qsql);
?>

