<?php
//Correo que es para enviar notificaciones

   $stmt = "SELECT * FROM usuarios_email_config WHERE usmd_id = 2";
   $mail_detail = mysql_fetch_assoc(mysql_query($stmt));

   $smtp_password = $mail_detail["usec_password"];
   $smtp_username = $mail_detail["usec_email"];
   $smtp_host = "smtp.gmail.com";


