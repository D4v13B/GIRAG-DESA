<?php include('conexion.php');
$i_usti_nombre=$_GET['i_usti_nombre'];
$qsql = "insert into usuarios_tipos 
(
usti_nombre
) 
values (
'$i_usti_nombre'
)";
mysql_query($qsql);
?>