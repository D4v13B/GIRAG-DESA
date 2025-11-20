<?php include('conexion.php');
$id=$_GET['id'];
$m_pais_id=$_POST['m_pais_id'];
$m_aeco_codigo=$_POST['m_aeco_codigo'];
$m_aeco_nombre=$_POST['m_aeco_nombre'];
$qsql = "update aereopuertos_codigos set 
pais_id='$m_pais_id', 
aeco_codigo='$m_aeco_codigo', 
aeco_nombre='$m_aeco_nombre'
where aeco_id='$id'";
mysql_query($qsql);
?>

