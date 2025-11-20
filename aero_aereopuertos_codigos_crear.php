<?php include('conexion.php');
$i_pais_id=$_POST['i_pais_id'];
$i_aeco_codigo=$_POST['i_aeco_codigo'];
$i_aeco_nombre=$_POST['i_aeco_nombre'];
$qsql = "insert into aereopuertos_codigos 
(
pais_id
, 
aeco_codigo
, 
aeco_nombre
) 
values (
'$i_pais_id', 
'$i_aeco_codigo', 
'$i_aeco_nombre')";
mysql_query($qsql);
?>

