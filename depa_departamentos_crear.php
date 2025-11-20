<?php include('conexion.php');
$i_depa_nombre=$_POST['i_depa_nombre'];
$i_depa_correo=$_POST['i_depa_correo'];
$qsql = "insert into departamentos 
(
depa_nombre
, 
depa_correo
) 
values (
'$i_depa_nombre', 
'$i_depa_correo')";
mysql_query($qsql);
?>

