<?php
include('conexion.php');
include('funciones.php');
$ingr_id = $_GET['id'];
//busco el último código temporal y le sumo 1
$qsql ="select inde_temp_code from ingresos_detalle where ingr_id='$ingr_id'";
$rs=mysql_query($qsql);
$num=mysql_num_rows($rs);
$i=0;
if($num>0)
{
	$codigo=mysql_result($rs,$i,'inde_temp_code');
}
else
{
	$qsql ="select cote_id from codigos_temporales";
	$codigo=obtener_valor($qsql,'cote_id');
	mysql_query("update codigos_temporales set cote_id=cote_id+1");
}
//ahora le sumo 1
echo $codigo;
?>