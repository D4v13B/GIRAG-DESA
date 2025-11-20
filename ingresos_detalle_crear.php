<?php include('conexion.php');
$i_prod_id=$_POST['prod_id'];
$i_inde_cantidad=$_POST['i_cantidad'];
$i_inti_id=$_POST['inti_id'];
$i_ingr_precio=$_POST['i_precio'];
$i_inde_temp_code=$_POST['h_codigo'];
$qsql = "insert into ingresos_detalle 
(
prod_id, 
inde_cantidad, 
inti_id, 
ingr_precio, 
inde_temp_code
) 
values (
'$i_prod_id', 
'$i_inde_cantidad', 
'$i_inti_id', 
'$i_ingr_precio', 
'$i_inde_temp_code')";
mysql_query($qsql);
?>

