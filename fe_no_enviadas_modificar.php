<?php
session_start();
$user_check=$_SESSION['login_user'];
if($user_check!='')
{
	
include('conexion.php');
include('funciones.php');

$id=$_POST['id'];
$m_ingr_numero_factura=$_POST['m_ingr_numero_factura'];
$m_ingr_fecha=$_POST['m_ingr_fecha'];
$m_cons_ruc=$_POST['m_cons_ruc'];
$m_cons_dv=$_POST['m_cons_dv'];
$m_cons_nombre=$_POST['m_cons_nombre'];
$m_cons_direccion=$_POST['m_cons_direccion'];
$m_cons_telefono=$_POST['m_cons_telefono'];
$m_cons_email=$_POST['m_cons_email'];
$m_ingr_subtotal=$_POST['m_ingr_subtotal'];
$m_ingr_impuesto=$_POST['m_ingr_impuesto'];
$m_ingr_total=$_POST['m_ingr_total'];
$m_ingr_tipo_cliente_FE=$_POST['m_ingr_tipo_cliente_FE'];
$m_ingr_tipo_contribuyente_FE=$_POST['m_ingr_tipo_contribuyente_FE'];

$qsql = "UPDATE ingresos SET 
ingr_numero_factura='$m_ingr_numero_factura',
ingr_fecha='$m_ingr_fecha',
ingr_subtotal='$m_ingr_subtotal',
ingr_impuesto='$m_ingr_impuesto',
ingr_total='$m_ingr_total',
ingr_tipo_cliente_FE='$m_ingr_tipo_cliente_FE',
ingr_tipo_contribuyente_FE='$m_ingr_tipo_contribuyente_FE'
where ingr_id='$id'";

mysql_query($qsql);

$qsql = "UPDATE consignee SET 
cons_ruc='$m_cons_ruc',
cons_dv='$m_cons_dv',
cons_nombre='$m_cons_nombre',
cons_direccion='$m_cons_direccion',
cons_telefono='$m_cons_telefono',
cons_email='$m_cons_email'
WHERE cons_id=(SELECT clie_id FROM ingresos WHERE ingr_id='$id')";

mysql_query($qsql);

// echo $qsql;
}
?>




