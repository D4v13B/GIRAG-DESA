<?php include('conexion.php');
$i_redg_nombre=$_POST['i_redg_nombre'];
$i_redg_nivel=$_POST['i_redg_nivel'];
$i_redg_padre=$_POST['i_redg_padre'];
$qsql = "insert into reportes_documentos_gerarquia 
(
redg_nombre
, 
redg_nivel
, 
redg_padre
) 
values (
'$i_redg_nombre', 
'$i_redg_nivel', 
'$i_redg_padre')";
mysql_query($qsql);
?>

