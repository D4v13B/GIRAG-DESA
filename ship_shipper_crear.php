<?php include('conexion.php');
$i_ship_nombre=$_POST['i_ship_nombre'];
$i_ship_ciudad=$_POST['i_ship_ciudad'];
$i_pais_id=$_POST['i_pais_id'];
$i_ship_direccion=$_POST['i_ship_direccion'];
$qsql = "insert into shipper 
(
ship_nombre, 
ship_ciudad, 
pais_id, 
ship_direccion
) 
values (
'$i_ship_nombre', 
'$i_ship_ciudad', 
'$i_pais_id', 
'$i_ship_direccion')";
mysql_query($qsql);
?>

