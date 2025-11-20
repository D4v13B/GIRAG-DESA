<?php include('conexion.php');
$id=$_GET['id'];
$m_usmd_nombre=$_POST['m_usmd_nombre'];
$qsql = "update usuarios_email_dedicado_a set 
usmd_nombre='$m_usmd_nombre'
where usmd_id='$id'";
mysql_query($qsql);
?>

