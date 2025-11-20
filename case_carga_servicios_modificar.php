<?php include('conexion.php');
$id=$_GET['id'];
$m_case_nombre=$_POST['m_case_nombre'];
$m_cadt_id=$_POST['m_cadt_id'];
$m_case_monto=$_POST['m_case_monto'];
$m_case_itbms=$_POST['m_case_itbms'];
$m_case_reembolsable=$_POST['m_case_reembolsable'];
$m_case_peso_minimo=$_POST['m_case_peso_minimo'];
$m_case_cuenta=$_POST['m_case_cuenta'];
$m_case_ait=$_POST['m_case_ait'];
$m_case_monto_max=$_POST['m_case_monto_max'];
$m_liae_id=$_POST['m_liae_id'];

// i_case_cuenta
$qsql = "update carga_servicios set 
case_nombre='$m_case_nombre', 
cadt_id='$m_cadt_id', 
case_monto='$m_case_monto', 
case_itbms='$m_case_itbms', 
case_reembolsable='$m_case_reembolsable', 
case_peso_minimo='$m_case_peso_minimo',
case_cuenta='$m_case_cuenta',
case_ait='$m_case_ait',
case_monto_max='$m_case_monto_max',
liae_id='$m_liae_id'
where case_id='$id'";
mysql_query($qsql);
?>

