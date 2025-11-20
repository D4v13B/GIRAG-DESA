<?php 
include('conexion.php');
include('funciones.php');

$id = $_GET['id'];

$qsql = "SELECT ingr_recurrente_anual FROM ingresos WHERE ingr_id='$id'";
$recurrente = obtener_valor($qsql, "ingr_recurrente");

if($recurrente==0)
{
	$qsql = "UPDATE ingresos SET ingr_recurrente_anual=1 WHERE ingr_id='$id'";
	mysql_query($qsql);
}
else
{
	$qsql = "UPDATE ingresos SET ingr_recurrente_anual=0 WHERE ingr_id='$id'";
	mysql_query($qsql);
}

?>