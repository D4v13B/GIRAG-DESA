<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from departamentos where depa_id=$id";
mysql_query($qsql);
?>

