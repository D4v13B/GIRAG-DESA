<?php 
include('conexion.php');
include('funciones.php');
$i_prod_id=$_GET['prod_id'];
$i_inde_cantidad=$_POST['i_cantidad'];
// $i_inti_id=$_POST['inti_id'];
$ingr_id=$_POST['ingr_id'];
$r_detalle=$_POST['r_detalle'];
$i_ingr_precio=$_POST['i_precio'];
$i_itbms = $_POST['i_itbms'];
$i_inde_temp_code=$_POST['h_codigo'];

if($r_detalle=='') $r_detalle = obtener_valor("select prod_nombre from productos where prod_id='$i_prod_id'", "prod_nombre");
$tasa_itbms = '0' . obtener_valor("select itbms from productos where prod_id='$i_prod_id'", "itbms");
$reembolsable = obtener_valor("select case_reembolsable from productos_tbl where prod_id='$i_prod_id'", "case_reembolsable");


	$qsql = "insert into ingresos_detalle 
	(
	prod_id, 
	inde_cantidad, 
	ingr_precio, 
	ingr_id,
	inde_detalle,
	inde_temp_code,
	ingr_itbms,
	inde_tasa_itbms,
	inde_reembolsable
	) 
	values (
	'$i_prod_id', 
	'$i_inde_cantidad', 
	'$i_ingr_precio', 
	'$ingr_id',
	'$r_detalle',
	'$i_inde_temp_code',
	'$i_itbms',
	'$tasa_itbms',
	'$reembolsable'
	)";
	mysql_query($qsql);


?>