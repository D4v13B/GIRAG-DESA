<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from codigo_interlineal where coin_id=$id";
mysql_query($qsql);
?>

