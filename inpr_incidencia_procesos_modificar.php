<?php include('conexion.php');
$id=$_GET['id'];
$m_inpr_incidencia=$_POST['m_inpr_incidencia'];
$qsql = "update incidencia_procesos set 
inpr_incidencia='$m_inpr_incidencia'
where inpr_id='$id'";
mysql_query($qsql);
?>

