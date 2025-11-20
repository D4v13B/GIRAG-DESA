<?php include('conexion.php'); 
$i_paro_pantalla=$_POST['i_paro_pantalla'];
$i_paro_nombre=$_POST['i_paro_nombre'];
$i_paro_descripcion=$_POST['i_paro_descripcion'];
$i_paro_item_id=$_POST['i_paro_item_id'];
$i_paro_item_tipo=$_POST['i_paro_item_tipo'];
$qsql = "insert into pantalla_roles 
(
paro_pantalla, 
paro_nombre, 
paro_descripcion, 
paro_item_id, 
paro_item_tipo
) 
values (
'$i_paro_pantalla', 
'$i_paro_nombre', 
'$i_paro_descripcion', 
'$i_paro_item_id', 
'$i_paro_item_tipo')";
mysql_query($qsql);
?>

