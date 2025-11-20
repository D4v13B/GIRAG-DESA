<?php include('conexion.php');
$i_case_nombre=$_POST['i_case_nombre'];
$i_cadt_id=$_POST['i_cadt_id'];
$i_case_monto=$_POST['i_case_monto'];
$i_case_itbms=$_POST['i_case_itbms'];
$i_case_reembolsable=$_POST['i_case_reembolsable'];
$i_case_peso_minimo=$_POST['i_case_peso_minimo'];

$i_case_cuenta=$_POST['i_case_cuenta'];
$i_case_ait=$_POST['i_case_ait'];
$i_case_es_ait=$_POST['i_case_es_ait'];
$i_case_monto_max=$_POST['i_case_monto_max'];
$i_liae_id=$_POST['i_liae_id'];

$qsql = "insert into carga_servicios 
(
case_nombre, 
cadt_id, 
case_monto, 
case_itbms, 
case_reembolsable, 
case_peso_minimo,
case_cuenta,
case_ait,
case_es_ait,
case_monto_max,
liae_id
) 
values (
'$i_case_nombre', 
'$i_cadt_id', 
'$i_case_monto', 
'$i_case_itbms', 
'$i_case_reembolsable', 
'$i_case_peso_minimo',
'$i_case_cuenta',
'$i_case_ait',
'$i_case_es_ait',
'$i_case_monto_max',
'$i_liae_id'
)";
mysql_query($qsql);
?>

