<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from casos where caso_id=$id";
mysql_query($qsql);
?>

