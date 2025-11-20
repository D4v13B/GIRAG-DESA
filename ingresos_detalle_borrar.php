<?php include('conexion.php'); 
$id = $_GET['id'];
$qsql ="delete from ingresos_detalle where inde_id=$id";
mysql_query($qsql);

//debo ingresar el historial de la venta en la bitacora solo si ya estaba vendido
$qsql ="insert into piezas_inventario_bitacora (piin_id, piib_fecha, pitt_id, ingr_id) 
(select piin_id, now(), 7, $id from piezas_inventario where piie_id=2 and inde_id='$id')";
mysql_query($qsql);

//debo liberar los items que ya tenía seleccionados
$qsql ="UPDATE piezas_inventario SET inde_temp_code=0, piie_id=1, piin_reembolso=0, piin_fecha_reembolso=NULL WHERE inde_id='$id'";
mysql_query($qsql);

//debo actualizar el total de la factura

?>

