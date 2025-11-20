<?php include('conexion.php');
$i_inpr_incidencia=$_POST['i_inpr_incidencia'];
$qsql = "insert into incidencia_procesos 
(
inpr_incidencia
) 
values (
'$i_inpr_incidencia')";
mysql_query($qsql);
?>

