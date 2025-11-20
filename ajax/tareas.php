<?php
session_start();

include "../conexion.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../vendor/autoload.php';
$tipo_email = 1;
require "mailerConfig.php";
$administrador_caso = $_SESSION["administrador_caso"];
$user_id = $_SESSION["login_user"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Registrar una tarea nueva
   $error = "";
   $last_id;

   $cate_nombre = $_POST["nombre"];
   $usua_id = $_POST["usuario"];
   $fecha_fin = $_POST["fecha_fin"];
   $fecha_inicio = $_POST["fecha_inicio"];
   $cate_descripcion = $_POST["descripcion"];
   $caso_id = $_POST["caso_id"]; //Registrar tarea a este caso
   $cate_recursos = $_POST["recursos"];
   $cate_observaciones = $_POST["observaciones"];
   $usua_id_asignado_2 = !empty($_POST["usua_asignado_2"]) ? $_POST["usua_asignado_2"] : 0;
   $usua_id_asignado_3 = !empty($_POST["usua_asignado_3"]) ? $_POST["usua_asignado_3"] : 0;

   if (isset($_POST["cate_id"]) and !empty($_POST["cate_id"])) {
      $cate_id = $_POST["cate_id"];
      //BLOQUE DE CODIGO QUE ME EDITA LA INFO GENERAL DE LA TAREA
      $stmt = "UPDATE casos_tareas SET
         cate_nombre = '$cate_nombre',
         usua_id = '$usua_id',
         cate_fecha_inicio = '$fecha_inicio',
         cate_fecha_cierre = '$fecha_fin',
         cate_descripcion = '$cate_descripcion',
         cate_recursos = '$cate_recursos',
         cate_observaciones = '$cate_observaciones',
         usua_id_2 = '$usua_id_asignado_2',
         usua_id_3 = '$usua_id_asignado_3'
         WHERE cate_id = '$cate_id'
      ";
      mysql_query($stmt);

      if (mysql_errno()) {
         echo json_encode(["success" => false]);
      } else {
         echo json_encode(["success" => true]);
      }
   } else { //Aqui vamos a insertar
      if (empty($cate_nombre) or empty($cate_descripcion) or empty($fecha_inicio) or empty($fecha_fin)) {
         echo $error = "Llenar todos los campos correctamente";
         if ($usua_id == 0) {
            echo $error = "Asignar la tarea a un departamento o usuario";
         }
      }

      // $depa_id = $depa_id > 0 ? $depa_id : (NULL);
      // $usua_id = $usua_id > 0 ? $usua_id : (NULL);

      if (empty($error)) {
         $stmt = "INSERT INTO casos_tareas(cate_nombre, cate_descripcion, cate_estado, caso_id, usua_id, cate_fecha_inicio, cate_fecha_cierre, cate_observaciones, cate_recursos, usua_id_2, usua_id_3) VALUES('$cate_nombre', '$cate_descripcion', 3, $caso_id, '$usua_id', '$fecha_inicio', '$fecha_fin', '$cate_observaciones', '$cate_recursos', '$usua_id_asignado_2', '$usua_id_asignado_3')";
         mysql_query($stmt);

         $last_id = mysql_insert_id();

         $stmt = "INSERT INTO casos_tareas_bitacora(cate_id, catb_descripcion, catb_avance, catb_fecha) VALUES($last_id, 'Apertura de tareas', '0', now())";
         mysql_query($stmt);

         //Insertar en la tabla de notificaciones para el usuario
         $fecha_actual = date("Y-m-d");
         $fecha_vencimiento = date("Y-m-d", strtotime("+5 days", strtotime($fecha_actual)));

         $query = "INSERT INTO usuarios_notificaciones (
         usua_id, 
         usno_mensaje, 
         usno_fecha_vencimiento, 
         usno_ref, 
         usno_tabla, 
         usno_tabla_id, 
         usno_tabla_campo) 
         VALUES (
         '$usua_id', 
         'TAREA PENDIENTE PARA EL CASO#$caso_id <br>Descripción: $cate_descripcion', 
         '$fecha_vencimiento', 
         'index.php?p=detalle-caso&caso=$caso_id', 
         'casos', 
         '$caso_id', 
         'caso_id')";

         mysql_query($query);

         echo json_encode(["success" => "Tarea registrada"]);
      } else {
         http_response_code(400);
         echo json_encode(["error" => "Ha ocurrido un error"]);
      }

      print_r($_FILES);

      if (!empty($_FILES["archivos"]["name"][0]) and empty($error)) {
         foreach ($_FILES["archivos"]["name"] as $key => $value) {
            $new_ref = time() . "-" . $_FILES["archivos"]["name"][$key];
            $nombre = $_FILES["archivos"]["name"][$key];

            if (move_uploaded_file($_FILES["archivos"]["tmp_name"][$key], "../img/casos_docs/" . $new_ref)) {
               $stmt = "INSERT INTO tareas_documentos(tado_ref, cate_id, tado_nombre) VALUES('$new_ref', '$last_id', '$nombre')";
               $res = mysql_query($stmt);
            }

            echo "Documentos subidos<br>";
         }
      }

      //Notificar tarea ----------------------
      // pedir la plantilla de correo de notificiacion de tarea
      $stmt = "SELECT * FROM contratos WHERE cont_nombre = 'NOTIFICACION-TAREA'";
      $plantilla = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

      //Pedir la info del usuario
      $usuaEmailArray = [];
      $stmt = "SELECT * FROM usuarios WHERE usua_id IN ($usua_id, $usua_id_asignado_2, $usua_id_asignado_3)";

      $res = mysql_query($stmt);
      $usuario = mysql_fetch_assoc($res);
      
      $plantilla = str_replace("[USUARIO]", $usuario["usua_nombre"], $plantilla);
      $plantilla = str_replace("[CASO_ID]", $caso_id, $plantilla);
      $plantilla = str_replace("[DESCRIPCION]", $cate_descripcion, $plantilla);
      $plantilla = str_replace("[FECHA_CIERRE]", $fecha_fin, $plantilla);
      
      while($fila = mysql_fetch_assoc($res)){
         array_push($usuaEmailArray, $fila["usua_mail"]);
      }

      // echo $usuario["usua_mail"] . PHP_EOL . $usuario["usua_nombre"] . PHP_EOL;

      require "mailerConfig.php";
      require "../funciones.php";
      if (!empty($usuaEmailArray)) {
         enviar_email($smtp_username, "NOTIFICACIONES DE SMS Y CALIDAD", "NUEVA TAREA PARA EL CASO $caso_id", $plantilla, $usuaEmailArray, $smtp_username, $smtp_password, new PHPMailer());
      }

   }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
   // Seleccionar todas las tareas seleccionadas de cada caso
   // En la sección del GET donde manejas cate_id específico
if (isset($_GET["cate_id"])) {
   $cate_id = $_GET["cate_id"];
   
   $stmt = "SELECT 
       ct.*,
       u1.usua_nombre as usuario_principal,
       u2.usua_nombre as usuario_2_nombre,
       u3.usua_nombre as usuario_3_nombre
   FROM casos_tareas ct
   LEFT JOIN usuarios u1 ON ct.usua_id = u1.usua_id
   LEFT JOIN usuarios u2 ON ct.usua_id_2 = u2.usua_id
   LEFT JOIN usuarios u3 ON ct.usua_id_3 = u3.usua_id
   WHERE ct.cate_id = $cate_id";
   
   $result = mysql_query($stmt);
   $res = mysql_fetch_assoc($result);
   
   // Preparar la respuesta con toda la información necesaria
   $response = array(
       'cate_id' => $res['cate_id'],
       'cate_nombre' => $res['cate_nombre'],
       'usua_id' => $res['usua_id'],
       'usua_id_2' => $res['usua_id_2'],
       'usua_id_3' => $res['usua_id_3'],
       'cate_fecha_inicio' => $res['cate_fecha_inicio'],
       'cate_fecha_cierre' => $res['cate_fecha_cierre'],
       'cate_descripcion' => $res['cate_descripcion'],
       'cate_recursos' => $res['cate_recursos'],
       'cate_observaciones' => $res['cate_observaciones']
   );
   
   echo json_encode($response);

   } else {
      $caso_id = $_GET["caso_id"];
      $response = [];

      if ($administrador_caso == 1) {

         $stmt = "SELECT ct.*, 
         (SELECT usua_nombre FROM usuarios WHERE usua_id = ct.usua_id) as usua_nombre, 
         (SELECT depa_nombre FROM departamentos WHERE depa_id = ct.depa_id) as depa_nombre,
         (SELECT caes_nombre FROM casos_estado WHERE caes_id = cate_estado ) as tarea_estado,
         (SELECT catb_avance FROM casos_tareas_bitacora WHERE cate_id = ct.cate_id ORDER BY catb_id DESC LIMIT 1) as ultimo_avance
         FROM casos_tareas ct
         WHERE ct.caso_id = '$caso_id' 
         ORDER BY ct.cate_id DESC";
      } else {

         $stmt = "SELECT ct.*, 
   (SELECT usua_nombre FROM usuarios WHERE usua_id = ct.usua_id) as usuario_principal, 
   (SELECT usua_nombre FROM usuarios WHERE usua_id = ct.usua_id_2) as usuario_2_nombre, 
   (SELECT usua_nombre FROM usuarios WHERE usua_id = ct.usua_id_3) as usuario_3_nombre, 
   (SELECT depa_nombre FROM departamentos WHERE depa_id = ct.depa_id) as depa_nombre,
   (SELECT caes_nombre FROM casos_estado WHERE caes_id = ct.cate_estado ) as tarea_estado,
   (SELECT catb_avance FROM casos_tareas_bitacora WHERE cate_id = ct.cate_id ORDER BY catb_id DESC LIMIT 1) as ultimo_avance
   FROM casos_tareas ct
   WHERE ct.caso_id = '$caso_id' AND (ct.usua_id = '$user_id' OR ct.usua_id_2 = '$user_id' OR ct.usua_id_3 = '$user_id')
   ORDER BY ct.cate_id DESC";

      }
      $res = mysql_query($stmt);

      while ($fila = mysql_fetch_assoc($res)) {
         array_push($response, $fila);
      }

      echo json_encode($response);
   }
} elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
   // Eliminar un documento de tareo especifico
   $datos = json_decode(file_get_contents("php://input"), true);

   if (isset($datos["tarea_id"])) {
      $tarea_id = $datos["tarea_id"];

      $stmt = "DELETE FROM casos_tareas WHERE cate_id = $tarea_id";
      mysql_query($stmt);

      echo "Tarea correctamente borrada<br>";
   }
}
