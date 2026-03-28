<?php

include "../conexion.php";

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":

      if (isset($_POST["a"]) and $_POST["a"] == "actualizarCampos") {
         $cons_id = mysql_real_escape_string($_POST["cons_id"]);
         $campo = $_POST["campo"];
         $valor = mysql_real_escape_string($_POST["valor"]);
         $columnas_permitidas = ['cons_nombre', 'cons_email', 'cons_telefono', 'cons_ruc', 'cons_dv', 'cons_tipo_constribuyente'];
         if (!in_array($campo, $columnas_permitidas, true)) {
            http_response_code(400);
            echo json_encode(["error" => "Campo no permitido"]);
            die();
         }
         $stmt = "UPDATE consignee SET $campo = '$valor' WHERE cons_id = '$cons_id'";

         mysql_query($stmt);

         if (mysql_errno()) {
            http_response_code(500);
            echo json_encode(["error" => "No se ha logrado actualizar el consignee"]);
            die();
         }
      }
      break;

   case "GET":
      $term = $_GET["term"];
      $data = [];

      $where = "";

      if($term != "" && isset($_GET["term"])){
         $where .= " AND cons_nombre LIKE '%$term%'";
      }

      $sql = "SELECT * FROM consignee WHERE 1=1 $where";

      $res = mysql_query($sql);

      while($row = mysql_fetch_assoc($res)){
         $data[] = [
            "cons_id" => $row["cons_id"],
            "cons_nombre" => $row["cons_nombre"]
         ];
      }

      echo json_encode($data);

      break;
}
