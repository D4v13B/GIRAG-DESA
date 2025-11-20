<?php
session_start();
$user_check=$_SESSION['login_user'];
if($user_check!='')
{
	
include('conexion.php');
include('funciones.php');

$id=$_POST['id'];
$m_clie_id=$_POST['i_clie_id'];
$m_clie_id = $m_clie_id[0];

$m_ingr_fecha=$_POST['i_ingr_fecha'];
$i_numero_factura=$_POST['i_numero_factura'];
$i_infp_id=$_POST['i_infp_id'];

$i_entregado=$_POST['i_entregado'];
$i_pagado=$_POST['i_pagado'];

$qsql = "select coalesce(sum(inde_cantidad*ingr_precio),0) subt, coalesce(sum(ingr_itbms),0) itbms 
from ingresos_detalle 
where ingr_id='$id'
";
$rs=mysql_query($qsql);
$i=0;
$subtotal = mysql_result($rs, $i, 'subt');
$itbms = mysql_result($rs, $i, 'itbms');
$ttotal = $subtotal+$itbms;


$qsql = "update ingresos set 
clie_id='$m_clie_id', 
ingr_fecha='$m_ingr_fecha',
ingr_numero_factura='$i_numero_factura',
infp_id='$i_infp_id',
inen_id='$i_entregado',
inpa_id='$i_pagado',
ingr_subtotal='$subtotal',
ingr_impuesto='$itbms',
ingr_total='$ttotal'
where ingr_id='$id'";

echo $qsql;

mysql_query($qsql);

$h_codigo = obtener_valor("select inde_temp_code from ingresos_detalle where ingr_id=$id","inde_temp_code");

//debo ingresar el historial de la venta en la bitacora
$qsql ="insert into piezas_inventario_bitacora (piin_id, piib_fecha, pitt_id, ingr_id) 
(select piin_id, now(), 2, $id from piezas_inventario where piie_id=1 and inde_temp_code='$h_codigo')";
mysql_query($qsql);
	
//ahora debo marcar cada pieza como vendida
$qsql ="update piezas_inventario set piie_id=2, piin_fecha_salida='$m_ingr_fecha' where inde_temp_code=$h_codigo and piie_id=1";
mysql_query($qsql);
}
?>




