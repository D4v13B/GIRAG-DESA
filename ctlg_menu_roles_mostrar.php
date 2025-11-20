<?php include('conexion.php'); ?> 
<table class=nicetable>
<tr>
<td class=tabla_datos_titulo>Tipo Usuario</td>
<td class=tabla_datos_titulo>Opción</td>
<td class=tabla_datos_titulo_icono>&nbsp;</td>
<td class=tabla_datos_titulo_icono>&nbsp;</td>
</tr>
<?php
$usti_id=$_GET['usti_id'];
$where="";
if($usti_id!='') $where .= " AND a.usti_id in ($usti_id)";

$qsql ="select * from menu_roles a, usuarios_tipos b, menu c, menu_madre d
where a.usti_id=b.usti_id
AND a.menu_id=c.menu_id
AND c.mema_id=d.mema_id
$where
order by usti_nombre, d.mema_id, menu_nombre";

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'usti_nombre'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'mema_nombre'); ?>-<?php echo mysql_result($rs, $i, 'menu_nombre'); ?></td>
<td class=tabla_datos_iconos><a href='javascript:editar(<?php echo mysql_result($rs, $i, 'mero_id'); ?>)';><img src='imagenes/modificar.png' border=0></a></td>
<td class=tabla_datos_iconos><a href='javascript:borrar(<?php echo mysql_result($rs, $i, 'mero_id'); ?>)';><img src='imagenes/trash.png' border=0></a></td>
</tr>
<?php
$i++;
}
?>
</table>