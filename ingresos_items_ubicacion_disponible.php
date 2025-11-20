<?php 
include('conexion.php'); 
include('funciones.php'); 

$id=$_GET['id'];
//con esto puedo sacar que pieza es
$pieza = obtener_valor("select piez_id from piezas_inventario where inde_id=$id", "piez_id");
$inde_temp_code = obtener_valor("select inde_temp_code from piezas_inventario where inde_id=$id", "inde_temp_code");
?> 
<script>
$(function () {
        $('.arrow_up').hide();
    });
$("#tbl_ubicacion_disponible tr").click(function() {
	$elid2 = $(this).closest('tr').attr('id');
	$('.arrow_up').hide();
	$('#ad2_' + $elid2).show();
	
	$('#h_disponible').val($elid2);
});

function mover_item()
{
	$.get('ingresos_items_ubicacion_mover.php?idm=1'
	+ "&actual=" + $('#h_actual').val()
	+ "&disponible=" + $('#h_disponible').val()
	,
	function(data)
		{
		//alert(data);
		actualizar_ubicacion(<?php echo $id?>);
		mostrar_items();		
		}
	);
}
</script>
<input type=hidden id=h_disponible>
<table class=nicetable style="width:300px">
<tr>
<td class=tabla_datos_titulo>Ubicaci&oacute;n Disponible</td>
</tr>
</table>
<table class=nicetable style="width:300px" id="tbl_ubicacion_disponible">
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
where inde_id<>'$id'
and a.piez_id='$pieza'
and a.inde_temp_code<>'$inde_temp_code'
and a.bode_id=c.bode_id
and a.piie_id in (1,3)
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
<td class=tabla_datos_iconos><a href="javascript:mover_item()">
	<img src="imagenes/arrow_up.png" style="width:15px;height:15px" class="arrow_up" id="ad2_<?php echo mysql_result($rs, $i, 'piin_id');?>">
	</a>
</td>
</tr>
<?php
$i++;
}
?>
</table>

