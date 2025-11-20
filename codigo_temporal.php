<?php 
include('conexion.php');
include('funciones.php');
//busco el último código temporal y le sumo 1
$qsql ="select cote_id from codigos_temporales";
$codigo=obtener_valor($qsql,'cote_id');
//saco el siguiente número de factura
//$factura = obtener_valor("select max(ingr_numero_factura)+ 1 factura from ingresos", "factura");
$factura = obtener_valor("select inse_numeracion from ingresos_secuencia", "inse_numeracion");
//ahora le sumo 1
mysql_query("update codigos_temporales set cote_id=cote_id+1");
echo $codigo . '||';
echo $factura . '||';
?>