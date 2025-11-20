<?php include('conexion.php');
$i_usua_id=$_POST['i_usua_id'];
$qsql = "insert into usuarios_firmas 
(
usua_id
) 
values (
'$i_usua_id')";
mysql_query($qsql);
?>

