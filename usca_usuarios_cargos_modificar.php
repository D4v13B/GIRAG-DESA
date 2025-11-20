<?php include('conexion.php');
$id=$_GET['id'];
$m_usca_nombre=$_POST['m_usca_nombre'];
$m_ucsa_gerente=$_POST['m_ucsa_gerente'];
$qsql = "update usuarios_cargos set 
usca_nombre='$m_usca_nombre', 
ucsa_gerente='$m_ucsa_gerente'
where usca_id='$id'";
mysql_query($qsql);
?>

