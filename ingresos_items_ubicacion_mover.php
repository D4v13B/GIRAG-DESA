<?php 
include('conexion.php'); 
include('funciones.php'); 

$actual = $_GET['actual'];
$disponible = $_GET['disponible'];

//verifico que no estén en blanco
if($actual!='' && $disponible!='')
{
	//saco el inde_temp_code y el inde_id
	echo "valor de actual:" . $actual;
	echo "valor de disponible:" . $disponible;
	
	$qsql = "select inde_temp_code, inde_id from piezas_inventario where piin_id=$actual";
	$rs=mysql_query($qsql);
	$i=0;
	$inde_temp_code = mysql_result($rs, $i, 'inde_temp_code');
	$inde_id = mysql_result($rs, $i, 'inde_id');
	
	//2021-02-24 agregro piie_id=1, piin_reembolso=0, piin_fecha_reembolso=null
	//al mover se quedaba como vendido el item
	$qsql ="update piezas_inventario
	set inde_id=0, inde_temp_code=0, piie_id=1, piin_reembolso=0, piin_fecha_reembolso=null
	where piin_id=$actual";
	mysql_query($qsql);
	
	$qsql ="update piezas_inventario
	set inde_id=$inde_id, piie_id=2, inde_temp_code=$inde_temp_code, piin_reembolso = IF(prop_id = '1', 1, 0)
	where piin_id=$disponible";
	mysql_query($qsql);
}
?>