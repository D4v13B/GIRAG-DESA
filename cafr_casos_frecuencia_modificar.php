<?php include('conexion.php');
$id=$_GET['id'];
$m_cafr_nombre=$_POST['m_cafr_nombre'];
$qsql = "update casos_frecuencia set 
cafr_nombre='$m_cafr_nombre'
where cafr_id='$id'";
mysql_query($qsql);
?>

