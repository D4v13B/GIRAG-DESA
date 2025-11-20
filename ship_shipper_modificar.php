<?php include('conexion.php');
$id=$_GET['id'];
$m_ship_nombre=$_POST['m_ship_nombre'];
$m_ship_ciudad=$_POST['m_ship_ciudad'];
$m_pais_id=$_POST['m_pais_id'];
$m_ship_direccion=$_POST['m_ship_direccion'];
$qsql = "update shipper set 
ship_nombre='$m_ship_nombre', 
ship_ciudad='$m_ship_ciudad', 
pais_id='$m_pais_id', 
ship_direccion='$m_ship_direccion'
where ship_id='$id'";
mysql_query($qsql);
?>

