<?php
session_start();
$user_check=$_SESSION['login_user'];
if($user_check!='')
{
	
include('conexion.php');
include('funciones.php');
$i_clie_id=$_POST['i_clie_id'];
$i_clie_id = $i_clie_id[0]; //[0] para que tomar el primer valor del array

$h_codigo=$_POST['h_codigo'];
$i_ingr_fecha=$_POST['i_ingr_fecha'];
$i_numero_factura=$_POST['i_numero_factura'];

//saco la secuencia en lugar de usar el que se asignó
$i_numero_factura = obtener_valor("select inse_numeracion from ingresos_secuencia", "inse_numeracion");

// $i_infp_id=$_POST['i_infp_id'];

// $i_entregado=$_POST['i_entregado'];
// $i_pagado=$_POST['i_pagado'];

//saco el subtotal, itbms y total
$qsql = "SELECT COALESCE(SUM(inde_cantidad*ingr_precio),0) subt, COALESCE(SUM(ingr_itbms),0) itbms 
FROM ingresos_detalle 
WHERE inde_temp_code='$h_codigo'
";
$rs=mysql_query($qsql);
$i=0;
$subtotal = mysql_result($rs, $i, 'subt');
$itbms = mysql_result($rs, $i, 'itbms');
$ttotal = $subtotal+$itbms;

$qsql = "insert into ingresos 
(clie_id, 
ingr_fecha,
ingr_numero_factura,
usua_id,
ingr_subtotal,
ingr_impuesto,
ingr_total
) 
values (
'$i_clie_id', 
'$i_ingr_fecha',
'$i_numero_factura',
'$user_check',
'$subtotal',
'$itbms',
'$ttotal'
)";
mysql_query($qsql);

//despues que la creo debo ponerle el código final a todos los detalles
//saco el código recien creado
$ingr_id = mysql_insert_id();
$qsql ="update ingresos_detalle set ingr_id='$ingr_id' where inde_temp_code='$h_codigo'";
mysql_query($qsql);

//le sumo a la secuencia 
$qsql = "update ingresos_secuencia set inse_numeracion=inse_numeracion+1";
mysql_query($qsql);

}
?>