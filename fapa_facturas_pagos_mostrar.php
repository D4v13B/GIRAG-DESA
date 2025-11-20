<?php include('conexion.php'); ?> 

<table class=nicetable style="width:99%">
<tr>
<td></td>
<td class=tabla_datos_titulo>Cliente</td>
<td class=tabla_datos_titulo>Guia</td>
<td class=tabla_datos_titulo>Factura</td>
<td class=tabla_datos_titulo>Monto</td>
<td class=tabla_datos_titulo>Fecha</td>
<td class=tabla_datos_titulo>Forma de pago</td>
<td class=tabla_datos_titulo>Registrado por</td>
<td class=tabla_datos_titulo>Fecha de registro</td>
</tr>
<?php
$desde=$_GET['desde'];
$hasta=$_GET['hasta'];
$cliente=$_GET['cliente'];
$where="";

if($desde!='') $where .= " AND date_format(fapa_fecha, '%Y-%m-%d)>='$desde'";
if($hasta!='') $where .= " AND date_format(fapa_fecha, '%Y-%m-%d)<='$hasta'";
if($vendedor!='') $where .= " AND clie_vendedor in ($vendedor)";
if($cliente!='') $where .= " AND b.clie_id in ($cliente)";


$qsql ="SELECT cade_guia, cons_nombre, ingr_numero_factura, fapa_monto, DATE_FORMAT(fapa_fecha, '%Y-%m-%d') fecha, fopa_nombre, 
 b.ingr_id,  a.fapa_id, 
(SELECT usua_nombre_completo FROM usuarios WHERE usua_id=a.usua_id) registrado, fapa_fecha_creacion 
FROM facturas_pagos a, ingresos b, forma_pago c, consignee d
WHERE a.fact_id=b.ingr_id
AND a.fopa_id=c.fopa_id
AND b.clie_id=d.cons_id
$where
ORDER BY fapa_fecha, cons_nombre";

//echo nl2br($qsql);

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'fapa_id'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'cons_nombre'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'cade_guia'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'ingr_numero_factura'); ?></td>
<td class=tabla_datos style="text-align:right"><?php echo number_format(mysql_result($rs, $i, 'fapa_monto'),2); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'fecha'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'fopa_nombre'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'registrado'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'fapa_fecha_creacion'); ?></td>
</tr>
<?php
$ttotal += mysql_result($rs, $i, 'fapa_monto');
$i++;
}
?>
<tr>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo style="text-align:right"><?php echo number_format($ttotal, 2)?></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo>  </td>
<td class=tabla_datos_titulo> </td>
<td class=tabla_datos_titulo>  </td>
</tr>
</table>

