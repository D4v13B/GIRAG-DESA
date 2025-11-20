<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from ingresos where ingr_id=$id";
mysql_query($qsql);


//debo liberar los items que ya tenía seleccionados
$qsql ="select inde_id from ingresos_detalle where ingr_id=$id";
$rs=mysql_query($qsql);
$num=mysql_num_rows($rs);
$i=0;
while($i<$num)
{
	$inde_id = mysql_result($rs, $i, 'inde_id');
	
	//debo ingresar el historial de la venta en la bitacora solo si ya estaba vendido
	$qsql ="insert into piezas_inventario_bitacora (piin_id, piib_fecha, pitt_id, ingr_id) 
	(select piin_id, now(), 7, $inde_id from piezas_inventario where piie_id=2 and inde_id='$inde_id')";
	mysql_query($qsql);
	
	$qsql ="update piezas_inventario set inde_temp_code=0, piie_id=1, piin_reembolso=0, piin_fecha_reembolso=null where inde_id=$inde_id";
	mysql_query($qsql);
}
?>