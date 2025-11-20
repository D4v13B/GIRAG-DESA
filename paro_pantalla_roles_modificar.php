<?php include('conexion.php');
$id=$_GET['id'];
$m_paro_pantalla=$_POST['m_paro_pantalla'];
$m_paro_nombre=$_POST['m_paro_nombre'];
$m_paro_descripcion=$_POST['m_paro_descripcion'];
$m_paro_item_id=$_POST['m_paro_item_id'];
$m_paro_item_tipo=$_POST['m_paro_item_tipo'];
$qsql = "update pantalla_roles set 
paro_pantalla='$m_paro_pantalla', 
paro_nombre='$m_paro_nombre', 
paro_descripcion='$m_paro_descripcion', 
paro_item_id='$m_paro_item_id', 
paro_item_tipo='$m_paro_item_tipo'
where paro_id='$id'";
mysql_query($qsql); 
?>




