<?php include('conexion.php');
$i_impe_impacto=$_POST['i_impe_impacto'];
$qsql = "insert into impacto_personas 
(
impe_impacto
) 
values (
'$i_impe_impacto')";
mysql_query($qsql);
?>

