<?php include('conexion.php');
$i_dife_descripcion=$_POST['i_dife_descripcion'];
$i_dife_fecha=$_POST['i_dife_fecha'];
$qsql = "insert into dias_feriados 
(
dife_descripcion
, 
dife_fecha
) 
values (
'$i_dife_descripcion', 
'$i_dife_fecha')";
mysql_query($qsql);
?>

