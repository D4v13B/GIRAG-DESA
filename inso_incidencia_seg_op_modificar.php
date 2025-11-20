<?php include('conexion.php');
$id=$_GET['id'];
$m_inso_incidencia=$_POST['m_inso_incidencia'];
$qsql = "update incidencia_seg_op set 
inso_incidencia='$m_inso_incidencia'
where inso_id='$id'";
mysql_query($qsql);
?>

