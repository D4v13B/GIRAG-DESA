<?php include('conexion.php');
$i_usmd_nombre=$_POST['i_usmd_nombre'];
$qsql = "insert into usuarios_email_dedicado_a 
(
usmd_nombre
) 
values (
'$i_usmd_nombre')";
mysql_query($qsql);
?>

