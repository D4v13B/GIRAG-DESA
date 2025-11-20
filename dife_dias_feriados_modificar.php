<?php include('conexion.php');
$id=$_GET['id'];
$m_dife_descripcion=$_POST['m_dife_descripcion'];
$m_dife_fecha=$_POST['m_dife_fecha'];
$qsql = "update dias_feriados set 
dife_descripcion='$m_dife_descripcion', 
dife_fecha='$m_dife_fecha'
where dife_id='$id'";
mysql_query($qsql);
?>

