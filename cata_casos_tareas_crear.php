<?php include('conexion.php');
$i_cate_nombre=$_POST['i_cate_nombre'];
$i_cate_descripcion=$_POST['i_cate_descripcion'];
$i_cate_fecha_cierre=$_POST['i_cate_fecha_cierre'];
$i_cate_estado=$_POST['i_cate_estado'];
$i_depa_id=$_POST['i_depa_id'];
$i_usua_id=$_POST['i_usua_id'];
$qsql = "insert into casos_tareas 
(
cate_nombre
, 
cate_descripcion
, 
cate_fecha_cierre
, 
cate_estado
, 
depa_id
, 
usua_id
) 
values (
'$i_cate_nombre', 
'$i_cate_descripcion', 
'$i_cate_fecha_cierre', 
'$i_cate_estado', 
'$i_depa_id', 
'$i_usua_id')";
mysql_query($qsql);
?>

