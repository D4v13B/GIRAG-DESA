<?php include('conexion.php');
$i_coin_codigo=$_POST['i_coin_codigo'];
$i_coin_descripcion=$_POST['i_coin_descripcion'];
$qsql = "insert into codigo_interlineal 
(
coin_codigo
, 
coin_descripcion
) 
values (
'$i_coin_codigo', 
'$i_coin_descripcion')";
mysql_query($qsql);
?>

