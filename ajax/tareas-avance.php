<?php
include "../conexion.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$tipo_email = 1;
require "mailerConfig.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   print_r($_POST);
   print_r($_FILES);

   $cate_id = $_POST["cate_id"];
   $avance = $_POST["avance_tarea"];
   $observaciones = $_POST["observaciones"];

   // Obtenemos el caso_id relacionado con la tarea actual
   $stmt = "SELECT caso_id FROM casos_tareas WHERE cate_id = $cate_id";
   $result_caso = mysql_query($stmt);
   if (!$result_caso) {
      die('Error al obtener el caso_id: ' . mysql_error());
   }
   $caso_id = mysql_fetch_assoc($result_caso)['caso_id'];

   // Obtener el último avance registrado antes de insertar el nuevo
   $stmt = "SELECT catb_avance FROM casos_tareas_bitacora WHERE cate_id = $cate_id ORDER BY catb_id DESC LIMIT 1";
   $result = mysql_query($stmt);

   if (!$result) {
      die('Error al consultar el último avance: ' . mysql_error());
   }
   $last_avance = 0;
   if (mysql_num_rows($result) > 0) {
      $row = mysql_fetch_assoc($result);
      $last_avance = $row['catb_avance'];
   }

   // Insertar el avance en la bitácora
   $stmt = "INSERT INTO casos_tareas_bitacora(cate_id, catb_descripcion, catb_avance, catb_fecha) 
             VALUES('$cate_id', '$observaciones', '$avance', now())";
   if (!mysql_query($stmt)) {
      die('Error al insertar el avance en la bitácora: ' . mysql_error());
   }
   $last_insert_id = mysql_insert_id();

   // Modificar el estado según el avance de la tarea
   if ($avance >= 100) {
      $stmt = "UPDATE casos_tareas SET cate_estado = 2 WHERE cate_id = $cate_id";
   } else {
      $stmt = "UPDATE casos_tareas SET cate_estado = 3 WHERE cate_id = $cate_id";
   }
   if (!mysql_query($stmt)) {
      die('Error al actualizar el estado de la tarea: ' . mysql_error());
   }

   echo "Avance subido" . PHP_EOL;

   // Comparar el avance ingresado con el último avance registrado
   if ($avance < $last_avance) {
      // Si el avance ingresado es menor que el último avance registrado, enviar correo de notificación
      $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACION-TAREA-RECHAZO'";
      $plantilla_result = mysql_query($stmt);
      if (!$plantilla_result) {
         die('Error al obtener la plantilla de correo: ' . mysql_error());
      }
      $plantilla = mysql_fetch_assoc($plantilla_result)["cont_detalle"];

      $sql = "SELECT usua_mail, usua_nombre FROM usuarios WHERE usua_id = (SELECT usua_id FROM casos_tareas WHERE cate_id = $cate_id)";
      $usuario_result = mysql_query($sql);
      if (!$usuario_result) {
         die('Error al obtener la información del usuario: ' . mysql_error());
      }
      $usuario = mysql_fetch_assoc($usuario_result);

      $plantilla = str_replace("[USUA_ASIGNADO_TAREA]", $usuario["usua_nombre"], $plantilla);
      $plantilla = str_replace("[TAREA]", $cate_id, $plantilla);
      $plantilla = str_replace("[CASO_ID]", $caso_id, $plantilla);
      $plantilla = str_replace("[COMENTARIO]", $observaciones, $plantilla);
      $plantilla = str_replace("[FECHA]", date('Y-m-d H:i:s'), $plantilla);

      $mail = new PHPMailer(true);
      try {
         $mail->isSMTP();
         $mail->Host = $smtp_host;
         $mail->SMTPAuth = true;
         $mail->Username = $smtp_username;
         $mail->Password = $smtp_password;
         $mail->SMTPSecure = $smtp_security;
         $mail->Port = $smtp_port;
         $mail->CharSet = "UTF-8";

         //Recipients
         $mail->setFrom($smtp_username, 'GIRAG CONTROL DE TAREAS');
         $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);

         //Content
         $mail->isHTML(true);
         $mail->Subject = 'NOTIFICACION DE DEVOLUCION DE TAREA #' . $cate_id;
         $mail->Body    = $plantilla;

         $mail->send();
         echo 'Message has been sent';
      } catch (Exception $e) {
         echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
   } else {
      // Si el avance ingresado es mayor o igual que el último avance registrado, no se enviará correo.
      echo "El avance es igual o mayor. No se enviará correo.";
   }

   // Verificamos si todas las tareas del caso están al 100%
   $stmt = "SELECT COUNT(*) as total_tareas, 
            SUM(CASE WHEN cate_estado = 2 THEN 1 ELSE 0 END) as tareas_completadas 
            FROM casos_tareas 
            WHERE caso_id = $caso_id";
   $result_tareas = mysql_query($stmt);
   if (!$result_tareas) {
      die('Error al verificar las tareas del caso: ' . mysql_error());
   }
   $tareas_info = mysql_fetch_assoc($result_tareas);

   // Si todas las tareas están completadas, enviamos la notificación
   if ($tareas_info['total_tareas'] > 0 && $tareas_info['total_tareas'] == $tareas_info['tareas_completadas']) {
      echo "Todas las tareas del caso #$caso_id están completadas. Enviando notificación..." . PHP_EOL;
      
      // Obtenemos la plantilla de notificación (asumimos que siempre existe)
      $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACION-CASO-COMPLETO'";
      $plantilla_result = mysql_query($stmt);
      if (!$plantilla_result) {
         die('Error al obtener la plantilla de correo para caso completo: ' . mysql_error());
      }
      
      $plantilla = mysql_fetch_assoc($plantilla_result)["cont_detalle"];
      
   // Obtenemos los usuarios con usca_id = 4 para notificar
   $sql = "SELECT usua_mail, usua_nombre FROM usuarios WHERE usca_id = 2";
   $usuario_result = mysql_query($sql);
   if (!$usuario_result) {
      die('Error al obtener la información de los usuarios a notificar: ' . mysql_error());
   }

   // Verificamos si hay al menos un usuario para notificar
   if (mysql_num_rows($usuario_result) == 0) {
      echo "No se encontraron usuarios con usca_id = 2 para notificar." . PHP_EOL;
   } else {
      // Iteramos sobre todos los usuarios encontrados
      while ($usuario = mysql_fetch_assoc($usuario_result)) {
         // Creamos una copia de la plantilla para cada usuario
         $plantilla_usuario = $plantilla;
         
         // Reemplazamos las variables en la plantilla
         $plantilla_usuario = str_replace("[NOMBRE_USUARIO]", $usuario["usua_nombre"], $plantilla_usuario);
         $plantilla_usuario = str_replace("[CASO_ID]", $caso_id, $plantilla_usuario);
         
         // Enviamos el correo
         $mail = new PHPMailer(true);
         try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = $smtp_security;
            $mail->Port = $smtp_port;
            $mail->CharSet = "UTF-8";
         
            // Configuración del remitente y destinatario
            $mail->setFrom($smtp_username, 'GIRAG CONTROL DE TAREAS');
            $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
         
            // Configuración del contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'NOTIFICACIÓN: Caso #' . $caso_id . ' completado al 100%';
            $mail->Body = $plantilla_usuario;
         
            $mail->send();
            echo "Notificación enviada correctamente al gerente SMS: " . $usuario["usua_nombre"] . PHP_EOL;
         } catch (Exception $e) {
            echo "No se pudo enviar la notificación a " . $usuario["usua_nombre"] . ". Error: {$mail->ErrorInfo}" . PHP_EOL;
         }
      }
   }
   } else {
      echo "El caso #$caso_id aún tiene tareas pendientes por completar. No se enviará notificación." . PHP_EOL;
   }


} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
   $tarea_id = $_GET["tarea_id"];
   $bitacora = [];

   $stmt = "SELECT catb_descripcion, catb_avance, catb_fecha,
            (SELECT GROUP_CONCAT(ctdb_ref) FROM casos_tareas_bitacora_documentos WHERE catb_id=a.catb_id) documentos
            FROM casos_tareas_bitacora a
            WHERE cate_id = $tarea_id
            ORDER BY catb_id DESC";

   $res = mysql_query($stmt);
   if (!$res) {
      die('Error al obtener la bitácora: ' . mysql_error());
   }

   while ($fila = mysql_fetch_assoc($res)) {
      array_push($bitacora, $fila);
   }

   echo json_encode($bitacora);
}
?>
