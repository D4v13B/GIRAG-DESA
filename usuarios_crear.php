<?php include('conexion.php');

$i_usua_nombre=$_POST['i_usua_nombre'];

$i_usti_id=$_POST['i_usti_id'];

$i_usua_password=$_POST['i_usua_password'];

$i_usua_nombre_completo=$_POST['i_usua_nombre_completo'];

$i_usua_mail=$_POST['i_usua_mail'];

$i_usua_sms_aprueba=$_POST['i_usua_sms_aprueba'];

$i_usua_administrador_caso=$_POST['i_usua_administrador_caso'];

$i_usca_id=$_POST["i_usca_id"];

$i_usua_cedula=$_POST["i_usua_cedula"];

$qsql = "insert into usuarios 

(

usua_nombre

, 

usti_id

, 

usua_password

, 

usua_nombre_completo

, 

usua_mail

, 

usua_sms_aprueba

,

usua_administrador_caso

,

usca_id,
usua_cedula

) 

values (

'$i_usua_nombre', 

'$i_usti_id', 

'$i_usua_password', 

'$i_usua_nombre_completo', 

'$i_usua_mail', 

'$i_usua_sms_aprueba', 

'$i_usua_administrador_caso',

'$i_usca_id',
'$i_usua_cedula'
)";

mysql_query($qsql);

?>



