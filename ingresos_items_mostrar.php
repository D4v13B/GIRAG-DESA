<?php 
include('conexion.php'); 
include('funciones.php'); 
$id=$_GET['id'];
?> 
<table class=nicetable>
<tr>
<td class=tabla_datos_titulo>Producto</td>
<td class=tabla_datos_titulo>Detalle</td>
<td class=tabla_datos_titulo>Cantidad</td>
<td class=tabla_datos_titulo_icono>Precio</td>
<td class=tabla_datos_titulo_icono>Total</td>
<td class=tabla_datos_titulo_icono></td>
</tr>
<?php
/* 
$qsql ="select prod_nombre, inde_detalle, inti_nombre, inde_cantidad, ingr_precio, inde_id,ingr_itbms,
(select group_concat(bode_nombre) from piezas_inventario pi, bodegas bo 
	where pi.bode_id=bo.bode_id
	and piez_id=a.prod_id
	and pi.inde_id=a.inde_id
	and inde_temp_code='$id') ubicacion
from ingresos_detalle a, productos b, ingresos_tipos c
where a.prod_id=b.prod_id
AND a.inti_id=c.inti_id
AND inde_temp_code='$id'
";
*/

$qsql ="SELECT ingr_id, inde_temp_code, prod_nombre, inde_detalle, inde_cantidad, ingr_precio, inde_id,ingr_itbms
FROM ingresos_detalle a, productos_tbl b
WHERE a.prod_id=b.prod_id
AND IF(ingr_id='$id', ingr_id='$id', inde_temp_code='$id')
";
$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
$subtotal=0;
$itbms=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'prod_nombre'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'inde_detalle'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'inde_cantidad'); ?></td>
<td class=tabla_datos_iconos style="text-align:right !important"><?php echo number_format(mysql_result($rs, $i, 'ingr_precio'),2); ?></td>
<td class=tabla_datos_iconos style="text-align:right !important"><?php echo number_format(mysql_result($rs, $i, 'ingr_precio')*mysql_result($rs, $i, 'inde_cantidad'),2); ?></td>
<td class=tabla_datos_iconos><a href='javascript:borrar_item(<?php echo mysql_result($rs, $i, 'inde_id'); ?>)';><img src='imagenes/trash.png' border=0></a></td>
</tr>
<?php
//$itbms +=mysql_result($rs, $i, 'ingr_itbms');
$subtotal += (mysql_result($rs, $i, 'ingr_precio')*mysql_result($rs, $i, 'inde_cantidad'));
$i++;
}

$itbms = obtener_valor("SELECT COALESCE(SUM(ingr_itbms),0) itbms FROM ingresos_detalle WHERE inde_temp_code='$id'", "itbms");
?>
<tr>
<td colspan=4></td>
<td>Sub-Total</td>
<td style="text-align:right !important"><?php echo number_format($subtotal,2);?></td>
</tr>
<tr>
<td colspan=4></td>
<td>ITBMS</td>
<td style="text-align:right !important"><?php echo number_format($itbms,2); //por ahora?></td>
</tr>
<tr>
<td colspan=4></td>
<td>Total</td>
<td style="text-align:right !important"><?php echo number_format($subtotal+$itbms,2)?></td>
</tr>
</table>