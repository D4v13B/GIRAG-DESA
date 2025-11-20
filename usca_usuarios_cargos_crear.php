<?php include('conexion.php');
$i_usca_nombre=$_POST['i_usca_nombre'];
$i_ucsa_gerente=$_POST['i_ucsa_gerente'];
$qsql = "insert into usuarios_cargos 
(
usca_nombre
, 
ucsa_gerente
) 
values (
'$i_usca_nombre', 
'$i_ucsa_gerente')";
mysql_query($qsql);
?>

