<?php 
function login_bitacora($clti_id, $clie_id, $usua_nombre)
{
	$link=mysqli_connect("e-integracionbackend.com","dunderio_admin","Integracion_2229", "dunderio_e_integracion_backend");
	mysqli_query($link, "SET NAMES 'utf8'");

	$ip_remoto = $_SERVER['REMOTE_ADDR'];

	$qsql = "insert into aplicaciones_login (clti_id, clie_id, aplo_usua_nombre, aplo_fecha, aplo_ip) values (
	'$clti_id',
	'$clie_id',
	'$usua_nombre',
	now(),
	'$ip_remoto')";
	mysqli_query($link, $qsql);
	
	//echo $qsql;
	mysqli_close($link);
}
?>