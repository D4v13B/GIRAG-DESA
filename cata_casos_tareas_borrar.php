<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from casos_tareas where cate_id=$id";
mysql_query($qsql);
?>

