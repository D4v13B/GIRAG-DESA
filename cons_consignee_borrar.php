<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from consignee where cons_id=$id";
mysql_query($qsql);
?>

