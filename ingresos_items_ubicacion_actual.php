<?php include('conexion.php'); 
$id=$_GET['id'];
?> 
<script>
$(function () {
        $('.arrow_down').hide();
    });
$("#tbl_ubicacion_actual tr").click(function() {
	//alert($(this).closest('tr').attr('id'));
	$elid = $(this).closest('tr').attr('id');
	//DEBO MOSTRAR LA FLECHA DEL QUE ACABO DE SELECCIONAR
	//oculto todas las down
	//alert($elid);
	$('.arrow_down').hide();
	$('#ad_' + $elid).show();
	$('#h_actual').val($elid);
});
</script>
<input type=hidden id=h_actual>
<table class=nicetable style="width:300px">
<tr>
<td class=tabla_datos_titulo>Ubicaci&oacute;n Actual</td>
</tr>
</table>
<table class=nicetable style="width:300px" id="tbl_ubicacion_actual">
<tr>
<td class=tabla_datos_titulo>Bodega</td>
<td class=tabla_datos_titulo>Costo</td>
<td class=tabla_datos_titulo>Comentario</td>
<td class=tabla_datos_iconos></td>
</tr>
<?php
$nombre=$_GET['nombre'];

$qsql ="select piin_id, bode_nombre, piin_costo, piin_comentario
from piezas_inventario a, bodegas c 
where inde_id='$id'
and a.bode_id=c.bode_id
order by bode_nombre";

//echo $qsql;

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr' id="<?php echo mysql_result($rs, $i, 'piin_id');?>">
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'bode_nombre'); ?></td>
<td class=tabla_datos style="text-align:right"><?php echo number_format(mysql_result($rs, $i, 'piin_costo'),2); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'piin_comentario'); ?></td>
<td class=tabla_datos_iconos><img src="imagenes/arrow_down.png" style="width:15px;height:15px" class="arrow_down" id="ad_<?php echo mysql_result($rs, $i, 'piin_id');?>"></td>
</tr>
<?php
$i++;
}
?>
</table>

