<?php include('conexion.php');
$i_usec_email=$_POST['i_usec_email'];
$i_usec_password=$_POST['i_usec_password'];
$i_usmd_id=$_POST['i_usmd_id'];
$i_usec_estado=$_POST['i_usec_estado'];
$i_usec_smtp=$_POST['i_usec_smtp'];
$qsql = "insert into usuarios_email_config 
(
usec_email, 
usec_password, 
usec_smtp
) 
values (
'$i_usec_email', 
'$i_usec_password', 
'$i_usec_smtp')";
mysql_query($qsql);
?>

