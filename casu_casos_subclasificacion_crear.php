<?php include('conexion.php');
$i_casu_descripcion=$_POST['i_casu_descripcion'];
$i_cacl_id=$_POST['i_cacl_id'];
$qsql = "insert into casos_subclasificacion 
(
casu_descripcion
, 
cacl_id
) 
values (
'$i_casu_descripcion', 
'$i_cacl_id')";
mysql_query($qsql);
?>

