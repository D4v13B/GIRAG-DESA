<?php
include '../conexion.php';
include '../funciones.php';
session_start();

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
      $caes_id = isset($_POST["caes_id"]) ? $_POST["caes_id"] : 2; //Verifica si es un borrador o ya lo vamos a recibir
      // $carg_notas = "";
      $no_recibo = $_POST["no_recibo"]; //Numero de recibo
      $carg_guia = isset($_POST["guia"]) ? $_POST["guia"] : ""; //Numero de guia
      $shipper = isset($_POST["ship_id"]) ? $_POST["ship_id"] : null; //Va a recibir id
      $consignee = isset($_POST["cons_id"]) ? $_POST["cons_id"] : null; //Va a recibir id
      // $agencia = $_POST["agencia"]; //Va a recibir un ID
      $carg_origin = isset($_POST["carg_origen"]) ? $_POST["carg_origen"] : "";
      $vuelo = $_POST["vuelo"];
      $destino_final_id = $_POST["destino_final"]; //ID del puerto al que va
      $fecha_recepcion_real = $_POST["recepcion_real"];
      $usua_id_creador = $_SESSION["login_user"]; //Podemos usar la variable de Sesion para asignar este valor
      $fecha_creado = $_POST["fecha_creacion"];
      $cati_id = $_POST["direccion"]; //Import, export ...
      $carg_peso = $_POST["carg_peso"];
      $coin_id = $_POST["coin_id"];
      $carg_nota = $_POST["carg_nota"];
      $carg_desc = isset($_POST["carg_desc"]) ? $_POST["carg_desc"] : null;

      // Valores para carga de tipo import & export-> $carga_tipo = 1 o carg_tipo = 2
      $trans_nombre = isset($_POST["trans_nombre"]) ? $_POST["trans_nombre"] : "";
      $trans_cedula = isset($_POST["trans_cedula"]) ? $_POST["trans_cedula"] : "";
      $trans_matricula = isset($_POST["trans_matricula"]) ? $_POST["trans_matricula"] : "";
      $transporte_id = isset($_POST["transporte_id"]) ? $_POST["transporte_id"] : "";

      // print_r($_POST);

      $error = "";
      if (isset($_POST["carg_id"]) and !empty($_POST["carg_id"])) { //Actualizar el dato
         $carg_id = $_POST["carg_id"];

         $stmt = "UPDATE carga SET
        cati_id = $cati_id,
        carg_guia = '$carg_guia',
        vuel_id = '$vuelo',
        aeco_id_destino_final = '$destino_final_id',
        carg_recepcion_real = '$fecha_recepcion_real',
         carg_origen = '$carg_origin',
        caes_id = '$caes_id',
        carg_transportista = '$trans_nombre',
        carg_transportista_cedula = '$trans_cedula',
        carg_transportista_matricula = '$trans_matricula',
        ship_id = '$shipper',
        cons_id = '$consignee',
        tran_id = '$transporte_id',
        carg_peso = '$carg_peso',
        carg_nota = '$carg_nota',
        coin_id = '$coin_id',
        carg_desc = '$carg_desc'
        WHERE carg_id = $carg_id";

         mysql_query($stmt);

         echo json_encode("ACTUALIZADO CORRECTAMENTE");
      } elseif (isset($caes_id)) {

         if ($cati_id == 1 or $cati_id == 2) {
            $stmt = "INSERT INTO carga(cati_id, carg_guia, carg_fecha_registro, vuel_id, aeco_id_destino_final, usua_id_creador, carg_recepcion_real, caes_id,
            carg_transportista, carg_transportista_cedula, carg_transportista_matricula, tran_id, carg_no_recibo, carg_peso, coin_id, carg_nota, cons_id, ship_id, carg_desc) VALUES('$cati_id', '$carg_guia', '$fecha_recepcion_real', '$vuelo', '$destino_final_id', '$usua_id_creador', '$fecha_creado', '$caes_id', '$trans_nombre', '$trans_cedula', '$trans_matricula', '$transporte_id', '$no_recibo', '$carg_peso', '$coin_id', '$carg_nota', '$consignee', '$shipper', '$carg_desc')";
         } else {
            $stmt = "INSERT INTO carga(cati_id, carg_guia, carg_fecha_registro, vuel_id, aeco_id_destino_final, usua_id_creador, carg_recepcion_real, liae_id, caes_id,
            carg_transportista, carg_transportista_cedula, carg_transportista_matricula, tran_id, carg_no_recibo, carg_peso, coin_id, carg_nota, cons_id, ship_id, carg_desc) VALUES('$cati_id', '$carg_guia', '$fecha_recepcion_real', '$vuelo', '$destino_final_id', '$usua_id_creador', '$fecha_creado', '$agencia', '$caes_id', '$carg_peso', '$coin_id', '$carg_nota', '$consignee', '$shipper', '$carg_desc')";
         }

         mysql_query($stmt);
         $last_insert_id = mysql_insert_id();

         if (mysql_errno()) {
            http_response_code(400);
            die(mysql_error());
         }

         echo json_encode(["carg_id" => $last_insert_id]);
      }

      if (mysql_error()) {
         http_response_code(400);
         echo json_encode(["error" => "Ha ocurrido un error"]);
      }

      break;
   case "GET":
      $carg_id = $_GET["carg_id"];

      // Consulta modificada con JOIN para obtener los nombres de shipper y consignee
      $stmt = "SELECT
      cd.cade_notificada_fecha,
      cd.cade_notificada,
      cd.cade_facturada,
    cd.cade_id,
    cd.cade_guia,
    cd.cade_peso,
    cd.cade_largo,
    cd.cade_ancho,
    cd.cade_alto,
    cd.cade_piezas,
    cd.cade_recibidas,
    cd.cade_salida,
    cd.cade_desc,
    cd.cade_notas,
    s.ship_nombre AS shipper_nombre,
    c.cons_nombre AS consignee_nombre,
    cd.calb_id,
    cd.cade_tipo_id,
      ce.caes_nombre,
      ce.caes_id,
      cd.cade_cantidad,
    cdt.cade_descripcion AS cade_tipo,
    GROUP_CONCAT(cl.calb_nombre SEPARATOR ',') AS lista_localizaciones
FROM carga_detalles cd
         LEFT JOIN shipper s ON cd.ship_id = s.ship_id
         LEFT JOIN consignee c ON cd.cons_id = c.cons_id
         LEFT JOIN carga_detalle_tipo cdt ON cd.cade_tipo_id = cdt.cade_tipo_id
         LEFT JOIN  carga_detalle_localizacion cdl ON cdl.cade_id = cd.cade_id
         LEFT JOIN carga_localizaciones_bodega cl ON cdl.calb_id = cl.calb_id
         LEFT JOIN carga_estado ce ON ce.caes_id = cd.caes_id
WHERE cd.carg_id = $carg_id
GROUP BY cd.cade_id";

      $res = [];
      $query = mysql_query($stmt);

      if ($query) {
         while ($fila = mysql_fetch_assoc($query)) {
            array_push($res, $fila);
         }
         echo json_encode($res); // Devuelve los datos en formato JSON
      } else {
         echo json_encode(["error" => "Error al obtener los detalles de carga"]);
      }
      break;
}
