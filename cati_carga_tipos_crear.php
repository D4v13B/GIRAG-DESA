<?php include('conexion.php');
$i_cati_nombre=$_POST['i_cati_nombre'];
$qsql = "insert into carga_tipos 
(
cati_nombre
) 
values (
'$i_cati_nombre')";
mysql_query($qsql);
?>

