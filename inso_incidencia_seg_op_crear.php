<?php include('conexion.php');
$i_inso_incidencia=$_POST['i_inso_incidencia'];
$qsql = "insert into incidencia_seg_op 
(
inso_incidencia
) 
values (
'$i_inso_incidencia')";
mysql_query($qsql);
?>

