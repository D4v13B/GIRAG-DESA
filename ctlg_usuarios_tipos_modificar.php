<?php include('conexion.php');
$id=$_GET['id'];
$m_usti_nombre=$_GET['m_usti_nombre'];
$qsql = "update usuarios_tipos set 
usti_nombre='$m_usti_nombre'
where usti_id='$id'";
mysql_query($qsql);
?>