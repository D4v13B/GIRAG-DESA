<?php
session_start();

include "../conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   if (isset($_POST["fecha_revision"]) and !empty($_POST["fecha_revision"])) {
      // print_r($_POST);
      // Actualizar fecha de revision

      $caso_id = $_POST["caso_id"];
      $fecha_analisis = $_POST["fecha_revision"];
      $stmt = "UPDATE casos 
      SET caso_fecha_analisis = '$fecha_analisis'
      WHERE caso_id =  $caso_id";

      $rs = mysql_query($stmt, $dbh);
      if (!mysql_error()) {
         $msg = "Actualizada correctamente";
      } else {
         $msg = "Error";
      }

      $rs = [
         "fecha" => $fecha_analisis,
         "msg" => $msg
      ];

      echo json_encode($rs);
   } elseif (!empty($_FILES["new_docs"]["name"]) and isset($_FILES["new_docs"]["name"])) {
      foreach ($_FILES["new_docs"]["name"] as $key => $value) {
         $nombre = $_FILES["new_docs"]["name"][$key];
         $ref = time() . "-" . $_FILES["new_docs"]["name"][$key];
         $caso_id = $_POST['caso_id'];

         if (move_uploaded_file($_FILES["new_docs"]["tmp_name"][$key], "../img/casos_docs/" . $ref)) {

            $stmt = "INSERT INTO casos_documentos(cado_nombre, caso_id, cado_ref) VALUES('$nombre', '$caso_id', '$ref')";
            $res = mysql_query($stmt);
            if (!mysql_error()) {
               echo "Subido correctamente $nombre";
            }
         } else {
            http_response_code(400);
            echo "ERROR AL GUARDAR EL ARCHIVO $nombre";
         }
      }
   } else {
      echo "Error";
   }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
   $caso_id = $_GET["caso_id"];
   $res = [];

   $stmt = "SELECT * FROM casos_documentos WHERE caso_id = $caso_id";
   $casos_documentos = mysql_query($stmt);

   while ($fila = mysql_fetch_assoc($casos_documentos)) {
      $fila["cado_ref"] = rawurlencode($fila["cado_ref"]);
      array_push($res, $fila);
   }

   echo json_encode($res);
} elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
   // Eliminar un documento de tareo especifico
   $datos = json_decode(file_get_contents("php://input"), true);
   $cado_id = $datos["cado_id"];

   echo "Hola";
   print_r($datos);

   $stmt = "DELETE FROM casos_documentos WHERE cado_id = $cado_id";
   mysql_query($stmt);
} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
   // print_r(file_get_contents("php://input"));
   //Aprobar los casos
   $_PUT = json_decode(file_get_contents("php://input"), true);
   // print_r($_SESSION["login_user"]);
   $caso_id = $_PUT["caso_id"];
   $observaciones = $_PUT["observaciones"];
   $user_id = $_SESSION["login_user"];

   $stmt = "UPDATE casos SET caes_id = 3, caso_observaciones = '$observaciones' WHERE caso_id = $caso_id";
   mysql_query($stmt);

   if(mysql_errno() == 0){
      echo json_encode(["success" => true]);
   }

}if ($_SERVER["REQUEST_METHOD"] == "PATCH") {
   $_PATCH = json_decode(file_get_contents("php://input"), true);
   $caso_id = $_PATCH["id"];
   
   $result = mysql_query("SELECT cantidad_usua_firmas_aprobado, cantidad_usua_firmas_revisado, 
       usua_id_aprobado, usua_id_aprobado2, usua_id_aprobado3,
       usua_id_revisado, usua_id_revisado2, usua_id_revisado3
       FROM casos WHERE caso_id = '$caso_id'");
   $caso = mysql_fetch_assoc($result);
   
   if (isset($_PATCH["caso_beneficio"])) {
       $caso_beneficio = $_PATCH["caso_beneficio"];
       $sql = "UPDATE casos SET caso_beneficio = '$caso_beneficio' WHERE caso_id = $caso_id";
       mysql_query($sql);
   } elseif (isset($_PATCH["usua_id_aprobado"])) {
       $user_id = $_SESSION["login_user"];
       $success = false;
       
       // Check if user has already approved
       if ($user_id == $caso['usua_id_aprobado'] || 
           $user_id == $caso['usua_id_aprobado2'] || 
           $user_id == $caso['usua_id_aprobado3']) {
           echo json_encode([
               "success" => false,
               "error" => "Ya has aprobado este caso anteriormente"
           ]);
           exit;
       }
       
       // Count current approvals
       $current_approvals = 
           (!empty($caso['usua_id_aprobado']) ? 1 : 0) + 
           (!empty($caso['usua_id_aprobado2']) ? 1 : 0) + 
           (!empty($caso['usua_id_aprobado3']) ? 1 : 0);
       
       // Check if we can add more approvals
       if ($current_approvals < $caso['cantidad_usua_firmas_aprobado']) {
           if (empty($caso['usua_id_aprobado'])) {
               $sql = "UPDATE casos SET usua_id_aprobado = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           } elseif (empty($caso['usua_id_aprobado2'])) {
               $sql = "UPDATE casos SET usua_id_aprobado2 = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           } elseif (empty($caso['usua_id_aprobado3'])) {
               $sql = "UPDATE casos SET usua_id_aprobado3 = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           }
       }
       
       if ($success) {
           mysql_query($sql);
           echo json_encode([
               "success" => mysql_errno() == 0,
               "remainingApprovals" => $caso['cantidad_usua_firmas_aprobado'] - ($current_approvals + 1)
           ]);
       } else {
           echo json_encode([
               "success" => false,
               "error" => "Ya se han completado todas las aprobaciones requeridas"
           ]);
       }
   } elseif (isset($_PATCH["usua_id_revisado"])) {
       $user_id = $_SESSION["login_user"];
       $success = false;
       
       // Check if user has already reviewed
       if ($user_id == $caso['usua_id_revisado'] || 
           $user_id == $caso['usua_id_revisado2'] || 
           $user_id == $caso['usua_id_revisado3']) {
           echo json_encode([
               "success" => false,
               "error" => "Ya has revisado este caso anteriormente"
           ]);
           exit;
       }
       
       // Count current reviews
       $current_reviews = 
           (!empty($caso['usua_id_revisado']) ? 1 : 0) + 
           (!empty($caso['usua_id_revisado2']) ? 1 : 0) + 
           (!empty($caso['usua_id_revisado3']) ? 1 : 0);
       
       // Check if we can add more reviews
       if ($current_reviews < $caso['cantidad_usua_firmas_revisado']) {
           if (empty($caso['usua_id_revisado'])) {
               $sql = "UPDATE casos SET usua_id_revisado = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           } elseif (empty($caso['usua_id_revisado2'])) {
               $sql = "UPDATE casos SET usua_id_revisado2 = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           } elseif (empty($caso['usua_id_revisado3'])) {
               $sql = "UPDATE casos SET usua_id_revisado3 = '$user_id' WHERE caso_id = '$caso_id'";
               $success = true;
           }
       }
       
       if ($success) {
           mysql_query($sql);
           echo json_encode([
               "success" => mysql_errno() == 0,
               "remainingReviews" => $caso['cantidad_usua_firmas_revisado'] - ($current_reviews + 1)
           ]);
       } else {
           echo json_encode([
               "success" => false,
               "error" => "Ya se han completado todas las revisiones requeridas"
           ]);
       }
   }

elseif(isset($_PATCH["usua_id_cerrado"])){
      $caso_nota_cierre = $_PATCH["caso_nota_cierre"];
      $usua_id_cerrado = $_PATCH["usua_id_cerrado"];
      $user_id = $_SESSION["login_user"];
      mysql_query("UPDATE casos SET usua_id_cerrado = '$user_id', caes_id = 2, caso_nota_cierre = '$caso_nota_cierre',caso_fecha_cierre = NOW()  WHERE caso_id = '$caso_id'");

      $sql= "DELETE FROM usuarios_notificaciones WHERE usno_tabla_id = '$caso_id' AND usno_tabla ='casos'";
      mysql_query($sql);
      
      if(mysql_errno() > 0){
         echo json_encode([
            "success" => false
         ]);
      }else{
         echo json_encode([
            "success" => true
         ]);
      }

   }else { // ACTUALIZAR EL ESTADO DE CASO A CERRADA
      //$usua_id = $_SESSION["login_user"];
      $sql= "DELETE FROM usuarios_notificaciones WHERE usno_tabla_id = '$caso_id' AND usno_tabla ='casos'";
      mysql_query($sql);

      echo $sql;

      if(mysql_errno()){
         die("NO SE PUDO BORRAR");
      }

      $sql = "UPDATE casos SET caes_id = 2 WHERE caso_id = '$caso_id'";
      mysql_query($sql);

      if(mysql_errno() > 0){
         echo json_encode([
            "success" => true
         ]);
      }else{
         echo json_encode([
            "success" => false
         ]);
      }
   }
}
