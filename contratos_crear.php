<?php 
include('conexion.php');
include('funciones.php'); 

$nombre=$_POST['i_nombre'];
$descripcion=$_POST['i_descripcion'];
$clase=$_POST['i_clase'];
$iversion=$_POST['i_version'];


$qsql = "insert into contratos (cont_nombre, 
cont_detalle, 
coti_id, 
proy_id, 
cocl_id, 
cont_version) 
values (
'$nombre', 
'$descripcion',
'$tipo',
'$proyecto',
'$clase',
'$iversion')";

//echo $qsql;

mysql_query($qsql);
?>