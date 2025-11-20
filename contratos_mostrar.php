<?php include('conexion.php'); ?>
<div class=nicetable>
<table style="width:99%">
<tr>
<td class=tabla_datos_titulo>Clase</td>
<td class=tabla_datos_titulo>Versi&oacute;n</td>
<td class=tabla_datos_titulo>Descripci&oacute;n</td>
<td class=tabla_datos_titulo_icono>Editar</td> 
<td class=tabla_datos_titulo_icono>Borrar</td>
</tr>
<?php
$nombre=$_GET['nombre'];
$cocl_id=$_GET['cocl_id'];

$where = '';
if ($nombre!='' && $nombre!='') $where .= " and cont_nombre like '%$nombre%'";
if ($cocl_id!='') $where .= " and a.cocl_id in ($cocl_id) ";

$qsql ="SELECT cont_nombre, cont_id, cocl_nombre, cont_version
FROM contratos a, contratos_clases b
WHERE cont_id<>0
AND a.cocl_id=b.cocl_id
$where
ORDER BY cocl_nombre, cont_nombre";

$rs_proy = mysql_query($qsql);
$num_proy = mysql_num_rows($rs_proy);
$i=0;

//echo $qsql;


while ($i<$num_proy)
    {
?>
<tr class="tabla_datos_tr">
<td class=tabla_datos><?php echo mysql_result($rs_proy, $i, 'cocl_nombre'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs_proy, $i, 'cont_version'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs_proy, $i, 'cont_nombre'); ?></td>
<td class=tabla_datos_iconos><a href="#" onclick=editar(<?php echo mysql_result($rs_proy, $i, 'cont_id'); ?>);><img src="imagenes/modificar.png" border=0 title="Editar Programa"></a></td>
<td class=tabla_datos_iconos><a href="#" onclick=borrar(<?php echo mysql_result($rs_proy, $i, 'cont_id'); ?>);><img src="imagenes/trash.png" border=0 title="Eliminar Programa"></a></td>
</tr>
<?php
$i++;
}
?>
</table>
</div>