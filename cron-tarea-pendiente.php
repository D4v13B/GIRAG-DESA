<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "vendor/autoload.php";

// VARIABLES PRINCIPALES------------------------------
// $smtp_username = "";
// $smtp_password = "";

$smtp_password = "ymfipffnphkrfrbg";
$smtp_username = "girag.gerencia.seg.op@gmail.com";
$smtp_host = "smtp.gmail.com";

// Conexion a la base de datos-----------------------
$server = "143.198.137.170";
// $usuario = "dunderio_usr_girag";
// $password = "Girag_2024!";
// $db_nombre = "dunderio_girag";

$usuario = "dunderio_usr_girag";
$password = "Girag_2024!";
$db_nombre = "dunderio_girag";

// Realizar conexion
$con = new mysqli($server, $usuario, $password, $db_nombre);

if ($con->connect_error) {
   die("Error de conexion: " . $con->connect_error);
}

// Extraer la plantilla del Gmail
$sql = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'task-pending'";
$res = $con->query($sql);
$bodyEmail = $res->fetch_all()[0][0];

// echo $bodyEmail;

$sql = "SELECT 
    cs.caso_id, 
    caso_descripcion,
    (SELECT GROUP_CONCAT(usua_mail SEPARATOR ';')
     FROM usuarios us 
     WHERE us.usua_id IN (ct.usua_id, ct.usua_id_2, ct.usua_id_3)) AS email_tarea_asignada,
    (SELECT usua_mail FROM usuarios us2 WHERE us2.usua_id = cs.usua_id_asignado) AS email_caso_asignado,
    cate_nombre, 
    cate_fecha_cierre, 
    cate_estado 
FROM casos_tareas ct 
INNER JOIN casos cs ON cs.caso_id = ct.caso_id
WHERE cate_estado = 3 
AND cate_fecha_cierre BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";

$res = $con->query($sql);

print_r($res);

if ($res->num_rows > 0) {

   try {
      $mail = new PHPMailer(true);
      // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      $mail->isSMTP();
      $mail->Host = $smtp_host;
      $mail->SMTPAuth = true;
      $mail->Username = $smtp_username;
      $mail->Password = $smtp_password;
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;
      $mail->CharSet = "UTF-8";

      // Guarda la plantilla original fuera del bucle
      $bodyEmailTemplate = $bodyEmail;

      while ($fila = $res->fetch_assoc()) {
         // Limpiar destinatarios al inicio de cada iteración
         $mail->clearAddresses();
         $mail->clearCCs();

         $correo_caso_asignado = $fila["email_caso_asignado"];
         $email = $fila["email_tarea_asignada"];
         $caso_nombre = $fila["caso_descripcion"];
         $tarea_nombre = $fila["cate_nombre"];
         $caso_id = $fila["caso_id"];
         $fecha_cierre_tarea = $fila["cate_fecha_cierre"];

         $mail->setFrom($smtp_username, 'SISTEMA GIRAG - NOTIFICACIÓN');
         $mail->Subject = 'Notificación de tarea pendiente';

         $em = 0;
         if (strpos($email, ';') !== false) {
            $ccs = explode(';', $email);
            foreach ($ccs as $key) {
               if ($em == 0) {
                  $mail->AddAddress($key);
               } else {
                  $mail->AddCC($key, $key);
               }
               $em++;
            }
         } else {
            // Solo añadir si no se procesó como lista
            $mail->AddAddress($email);
         }

         // Añadir el coordinador del caso
         if (!empty($correo_caso_asignado)) {
            $mail->addAddress($correo_caso_asignado, "Coordinador de caso");
         }

         $mail->isHTML(true);

         // Usar la plantilla original en cada iteración
         $currentBody = $bodyEmailTemplate;
         $currentBody = str_replace("[CASO]", $caso_id, $currentBody);
         $currentBody = str_replace("[TAREA]", $tarea_nombre, $currentBody);
         $currentBody = str_replace("[USUA_ASIGNADO_TAREA]", $email, $currentBody);
         $currentBody = str_replace("[FECHA]", $fecha_cierre_tarea, $currentBody);

         $mail->Body = $currentBody;

         // Enviar correo electrónico
         if ($mail->send()) {
            echo 'Correo electrónico enviado correctamente';
         } else {
            echo 'Error al enviar el correo electrónico: ' . $mail->ErrorInfo;
         }
      }
   } catch (Exception $th) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
   }
}

$con->close();
