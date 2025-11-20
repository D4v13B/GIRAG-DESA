<?php include('conexion.php');
$i_imec_nombre=$_POST['i_imec_nombre'];
$qsql = "insert into impacto_economico 
(
imec_nombre
) 
values (
'$i_imec_nombre')";
mysql_query($qsql);
?>

