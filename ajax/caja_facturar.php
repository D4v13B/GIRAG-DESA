<?php

include "./../funciones.php";
include "./../conexion.php";
session_start();
$usua_id = $_SESSION["login_user"];
// Aqui vamos a buscar las credenciales en la base de datos TODO

error_reporting(0);


switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST": //Add formas de pago
      if (!isset($_POST["forma_pago"]) || $_POST["forma_pago"] == "" || empty($_POST["forma_pago"])) {
         http_response_code(403);
         echo json_encode(["msg" => "Seleccione una forma de pago"]);
         die();
      }

      if (!isset($_POST["monto"]) || $_POST["monto"] <= 0) {
         http_response_code(403);
         echo json_encode(["msg" => "No puedes enviar monto negativo o en cero"]);
         die();
      }

      $fopa_id = $_POST["forma_pago"];
      $monto = $_POST["monto"];
      $cade_guia = $_POST["cade_guia"];

      /**
       * Vamos a insertar en facturas_pagos para luego enlazarlo con la factura ID
       */
      $sql = "INSERT INTO facturas_pagos(fapa_monto, fopa_id, usua_id, fapa_codigo_agrupado) VALUES('$monto', '$fopa_id', '$usua_id', '$cade_guia')";
      mysql_query($sql);

      break;
   case "GET":

      $cade_guia = $_GET["cade_guia"];

      //Verificar si hay saldo pendiente en los pagos de la guia
      $monto_pendiente = obtener_valor("SELECT COALESCE(
               (SELECT COALESCE((SELECT SUM(caca_monto + caca_itbms)
                                 FROM carga_cargos
                                 WHERE cade_guia = '$cade_guia'
                                   AND caca_facturado = 0), 0)
                           -
                       COALESCE((SELECT SUM(fapa_monto)
                                 FROM facturas_pagos
                                 WHERE fapa_codigo_agrupado = '$cade_guia'
                                   AND fapa_facturado = 0), 0)),
               0
                   ) AS saldo_pendiente
            FROM carga_cargos
            WHERE cade_guia = '$cade_guia'
            GROUP BY cade_guia", "saldo_pendiente");

      // $cargos_sin_facturar = obtener_valor("SELECT COALESCE(caca_id, 0) FROM carga_cargos WHERE caca_facturado = 0 AND cade_guia = '$cade_guia'", "caca_id");
      $cargos_sin_facturar = mysql_fetch_assoc(mysql_query("SELECT COALESCE(caca_id, 0) FROM carga_cargos WHERE caca_facturado = 0 AND cade_guia = '$cade_guia'"));


      $sql = "SELECT * FROM facturas_pagos a, forma_pago b WHERE fapa_codigo_agrupado = '$cade_guia' AND a.fopa_id = b.fopa_id";
      $res = mysql_query($sql);

      $formas_pagos = [];

      while ($row = mysql_fetch_assoc($res)) {
         $formas_pagos[] = $row;
      }

      echo json_encode(["data" => $formas_pagos, "monto_pendiente" => $monto_pendiente, "cargos_sin_facturar" => $cargos_sin_facturar]);

      break;

   case "DELETE":

      $_DELETE = json_decode(file_get_contents("php://input"), true);

      // print_r($_DELETE);

      $fopa_id = $_DELETE["fopa_id"];

      $sql = "DELETE FROM facturas_pagos WHERE fopa_id = '$fopa_id'";

      mysql_query($sql);

      break;
}
