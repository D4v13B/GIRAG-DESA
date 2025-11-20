<?php

include "../conexion.php";

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
      // $usua_id = $_POST["usuario_firma"];

      // //USFI_REF
      // $temp_file = $_FILES["usfi_ref"]["tmp_name"];
      // $nuevo_nombre = time() . "-" .$_FILES["usfi_ref"]["full_path"];

      // $sql = "INSERT INTO usuarios_firmas(usua_id, usfi_ref) VALUES('$usua_id', '$nuevo_nombre')";
      // mysql_query($sql);

      // if(mysql_error()){
      //    echo "USUARIO CON FIRMA EXISTENTE";
      //    http_response_code(400);
      //    die();
      // }

      // move_uploaded_file($temp_file, "../firmas-electronicas/".$nuevo_nombre);
      // print_r($_POST);

      $_POST["img"] = str_replace('data:image/png;base64,', '', $_POST["img"]);
      $_POST["img"] = str_replace(" ", "+", $_POST["img"]);
      $img = base64_decode($_POST["img"]);
      $usua_id = $_POST["usua_id"];

      $ext = pathinfo($_POST["fname"], PATHINFO_EXTENSION);

      $nuevoNombre = md5(time()) . "." . $ext;

      if (file_put_contents("../firmas-electronicas/" . $nuevoNombre, $img)) {
         $sql = "INSERT INTO usuarios_firmas(usua_id, usfi_ref) VALUES('$usua_id', '$nuevoNombre')";
         mysql_query($sql);

         if(mysql_error()){
            echo "USUARIO CON FIRMA EXISTENTE";
            http_response_code(400);
            die();
         }

         echo "Subido exitosamente";
      }


      break;
}
