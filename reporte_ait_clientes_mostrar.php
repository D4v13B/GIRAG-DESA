<?php include('conexion.php'); ?> 

<table class=nicetable style="width:99%">
<tr>
<td class=tabla_datos_titulo>Recibo</td>
<td class=tabla_datos_titulo>Guia</td>
<td class=tabla_datos_titulo>Aerolinea</td>
<td class=tabla_datos_titulo>Forma de Pago</td>
<td class=tabla_datos_titulo>AIT Monto</td>
<td class=tabla_datos_titulo>DK</td>
<?php
$desde=$_GET['desde'];
$hasta=$_GET['hasta'];
$aerolinea=$_GET['aerolinea'];
$where="";

if($desde!='') $where .= " AND DATE_FORMAT(ingr_fecha, '%Y-%m-%d')>='$desde'";
if($hasta!='') $where .= " AND DATE_FORMAT(ingr_fecha, '%Y-%m-%d')<='$hasta'";
if($aerolinea!='') $where .= " AND lin.liae_id in ($aerolinea)";


$qsql ="SELECT car.carg_id, a.cade_guia, liae_nombre, cons_nombre, caca_monto, ingr_numero_factura, liae_dk, GROUP_CONCAT(fopa_nombre) AS fopa_nombre
FROM carga_cargos a
INNER JOIN carga_servicios b ON a.case_id=b.case_id
INNER JOIN carga car ON a.carg_id=car.carg_id
INNER JOIN lineas_aereas lin ON car.liae_id=lin.liae_id
LEFT JOIN consignee con ON car.cons_id=con.cons_id
LEFT JOIN ingresos ing ON a.carg_id = ing.carg_id
LEFT JOIN facturas_pagos fp ON ing.ingr_id = fp.fact_id
LEFT JOIN forma_pago forp ON fp.fopa_id=forp.fopa_id
where b.case_es_ait=1
$where
GROUP BY car.carg_id, a.cade_guia, liae_nombre, cons_nombre, caca_monto, ingr_numero_factura, liae_dk";

// echo nl2br($qsql);

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'ingr_numero_factura'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'cade_guia'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'liae_nombre'); ?></td>
<td class=tabla_datos style="text-align:center"><?php echo mysql_result($rs, $i, 'fopa_nombre'); ?></td>
<td class=tabla_datos style="text-align:right"><?php echo number_format(mysql_result($rs, $i, 'caca_monto'),2); ?></td>
<td class=tabla_datos style="text-align:right"><?php echo mysql_result($rs, $i, 'liae_dk'); ?></td>
</tr>
<?php
$ttotal += mysql_result($rs, $i, 'caca_monto');
$i++;
}
?>
<tr>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo></td>
<td class=tabla_datos_titulo style="text-align:right">TOTAL:</td>
<td class=tabla_datos_titulo style="text-align:right"><?php echo number_format($ttotal, 2)?></td>
<td class=tabla_datos_titulo style="text-align:right"></td>
</tr>
</table>

