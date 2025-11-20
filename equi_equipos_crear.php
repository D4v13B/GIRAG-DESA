<?php include('conexion.php');
$i_equi_nombre=$_POST['i_equi_nombre'];
$qsql = "insert into equipos 
(
equi_nombre
) 
values (
'$i_equi_nombre')";
mysql_query($qsql);
?>

