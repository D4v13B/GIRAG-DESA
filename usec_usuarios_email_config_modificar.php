<?php include('conexion.php');
$id=$_GET['id'];
$m_usec_email=$_POST['m_usec_email'];
$m_usec_password=$_POST['m_usec_password'];
$m_usmd_id=$_POST['m_usmd_id'];
$m_usec_estado=$_POST['m_usec_estado'];
$m_usec_smtp=$_POST['m_usec_smtp'];
$qsql = "update usuarios_email_config set 
usec_email='$m_usec_email', 
usec_password='$m_usec_password', 
usec_smtp='$m_usec_smtp'
where usec_id='$id'";
mysql_query($qsql);
?>

