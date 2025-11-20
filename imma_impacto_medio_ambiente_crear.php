<?php include('conexion.php');
$i_imma_impacto=$_POST['i_imma_impacto'];
$qsql = "insert into impacto_medio_ambiente 
(
imma_impacto
) 
values (
'$i_imma_impacto')";
mysql_query($qsql);
?>

