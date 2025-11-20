<?php include('conexion.php'); ?> 

<table class=nicetable>
<tr>
<td class=tabla_datos_titulo>Tipo de incidencia</td>
<td class=tabla_datos_titulo_icono>&nbsp;</td>
<td class=tabla_datos_titulo_icono>&nbsp;</td>
</tr>
<?php
$nombre=$_GET['nombre'];

$qsql ='select * from incidencia_procesos';

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'inpr_incidencia'); ?></td>
<td class=tabla_datos_iconos><a href='javascript:editar(<?php echo mysql_result($rs, $i, 'inpr_id'); ?>)';><img src='imagenes/modificar.png' border=0></a></td>
<td class=tabla_datos_iconos><a href='javascript:borrar(<?php echo mysql_result($rs, $i, 'inpr_id'); ?>)';><img src='imagenes/trash.png' border=0></a></td>
</tr>
<?php
$i++;
}
?>
</table>

