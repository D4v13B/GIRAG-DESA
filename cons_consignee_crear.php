<?php include('conexion.php');
$i_cons_nombre=$_POST['i_cons_nombre'];
$i_pais_id=$_POST['i_pais_id'];
$i_cons_ciudad=$_POST['i_cons_ciudad'];
$i_cons_direccion=$_POST['i_cons_direccion'];
$qsql = "insert into consignee 
(
cons_nombre
, 
pais_id
, 
cons_ciudad
, 
cons_direccion
) 
values (
'$i_cons_nombre', 
'$i_pais_id', 
'$i_cons_ciudad', 
'$i_cons_direccion')";
mysql_query($qsql);
?>

