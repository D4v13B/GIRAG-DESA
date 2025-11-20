<?php 
include('conexion.php');
include('funciones.php');

$cid=$_POST['h_aid'];
$nombre=$_POST['m_nombre']; 
$descripcion=$_POST['m_descripcion'];
$tipo=$_POST['m_tipo'];
$proyecto=$_POST['m_proyecto'];
$clase=$_POST['m_clase'];
$mversion=$_POST['m_version'];



$qsql = "update contratos set
cont_nombre='$nombre',
coti_id='$tipo',
cocl_id='$clase',
proy_id='$proyecto',
cont_version='$mversion',
cont_detalle='$descripcion'
where cont_id='$cid'";

//echo $qsql;
mysql_query($qsql);
?>