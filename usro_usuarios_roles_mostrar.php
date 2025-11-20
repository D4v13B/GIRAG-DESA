<?php include('conexion.php'); ?>
<table class=nicetable>
<tr>
<td class=tabla_datos_titulo>Pantalla rol</td>
<td class=tabla_datos_titulo_icono>&nbsp;</td>
</tr>
<?php
$user_id=$_GET['user_id'];

$qsql ="select paro_nombre, usro_id
from usuarios_roles a, pantalla_roles b
where a.paro_id=b.paro_id
and usua_id=$user_id
";

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'paro_nombre'); ?></td>
<td class=tabla_datos_iconos><a href='javascript:borrar_rol(<?php echo mysql_result($rs, $i, 'usro_id'); ?>)';><img src='imagenes/trash.png' border=0 style="width:20px;height:20px"></a></td>
</tr>
<?php
$i++;
}
?>
</table>