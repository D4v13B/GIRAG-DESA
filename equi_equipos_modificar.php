<?php include('conexion.php');
$id=$_GET['id'];
$m_equi_nombre=$_POST['m_equi_nombre'];
$qsql = "update equipos set 
equi_nombre='$m_equi_nombre'
where equi_id='$id'";
mysql_query($qsql);
?>

