<?php include('conexion.php');
$i_cons_nombre=$_POST['i_cons_nombre'];
$i_cons_telefono=$_POST['i_cons_telefono'];
$i_cons_direccion=$_POST['i_cons_direccion'];
$qsql = "insert into consignee 
(
cons_nombre
, 
cons_telefono
, 
cons_direccion
) 
values (
'$i_cons_nombre', 
'$i_cons_telefono', 
'$i_cons_direccion')";
mysql_query($qsql);
?>

