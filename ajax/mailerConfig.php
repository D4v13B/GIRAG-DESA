<?php
//Correo que es para enviar notificaciones

$tipo_email = 1;
if($tipo_email == 1 or 1 == 1){
   $stmt = "SELECT * FROM usuarios_email_config";
   $mail_detail = mysql_fetch_assoc(mysql_query($stmt));

   $smtp_password = $mail_detail["usec_password"];
   $smtp_username = $mail_detail["usec_email"];
   $smtp_host = "smtp.gmail.com";
   $smtp_security = "ssl";
   $smtp_port = 465;
}


