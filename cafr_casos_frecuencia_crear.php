<?php include('conexion.php');
$i_cafr_nombre=$_POST['i_cafr_nombre'];
$qsql = "insert into casos_frecuencia 
(
cafr_nombre
) 
values (
'$i_cafr_nombre')";
mysql_query($qsql);
?>

