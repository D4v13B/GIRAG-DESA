<?php

use PHPMailer\PHPMailer\PHPMailer;

require "../vendor/autoload.php";

include '../conexion.php';
include '../funciones.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$usua_id = $_SESSION["login_user"];

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
      if (isset($_POST["a"]) && $_POST["a"] == "transportista") {

         $cade_id = $_POST["cade_id"];
         $cade_transportista_nombre = $_POST["cade_transportista_nombre"];
         $cade_transportista_cedula = $_POST["cade_transportista_cedula"];
         $cade_transportista_matricula = $_POST["cade_transportista_matricula"];
         $transporte_id = $_POST["transporte_id"];

         $cade_guia = obtener_valor("SELECT cade_guia FROM carga_detalles WHERE cade_id = '$cade_id'", "cade_guia");

         $sql = "
            UPDATE carga_detalles SET 
            cade_transportista_nombre = '$cade_transportista_nombre',
            cade_transportista_cedula = '$cade_transportista_cedula',
            cade_transportista_matricula = '$cade_transportista_matricula',
            tran_id = '$transporte_id'
            WHERE cade_guia = '$cade_guia'
         ";

         $res = mysql_query($sql);

         if ($res) {
            echo json_encode(["success" => true]);
         } else {
            echo json_encode(["success" => false]);
         }
         exit; // Agregado exit aquí

      } else {

         if (isset($_POST["cade_id"]) && !empty($cade_id = $_POST["cade_id"])) {
            $campo = $_POST["campo"];
            $valor = $_POST["valor"];
            $id = isset($_POST["cade_id"]) ? $_POST["cade_id"] : "";
            if (modificarCampo("carga_detalles", $campo, $valor, $id, "cade_id")) {
               $success = true; // Corregido: inicializar antes de usar
            } else {
               $success = false; // Corregido: inicializar antes de usar
            }
            echo json_encode(["success" => $success]); // Removido el echo duplicado
            exit; // Agregado exit aquí

         } else { //Vamos a insertar un nuevo registro de carga detalle

            $carg_id = $_POST["carg_id"];
           
            $cade_guia = $_POST["cade_guia_n"] ?? null;
            $cade_desc = $_POST["cade_desc"] ?? '';
            $calb_id = isset($_POST["calb_id"]) ? explode(",", $_POST["calb_id"]) : [];
            $cade_tipo_id = $_POST["cade_tipo_id"] ?? null;
            $cade_cantidad = isset($_POST["cade_cantidad"]) && is_numeric($_POST["cade_cantidad"]) ? $_POST["cade_cantidad"] : 0;
            $cade_peso = isset($_POST["cade_peso"]) && is_numeric($_POST["cade_peso"]) ? $_POST["cade_peso"] : 0;
            $cade_largo = isset($_POST["cade_largo"]) ? $_POST["cade_largo"] : 0;
            $cade_ancho = isset($_POST["cade_ancho"]) ? $_POST["cade_ancho"] : 0;
            $cade_alto = isset($_POST["cade_alto"]) ? $_POST["cade_alto"] : 0;
            $cade_piezas = $_POST["cade_piezas"];
            $shipper = isset($_POST["ship_id"]) ? $_POST["ship_id"] : null;
            $consignee = isset($_POST["cons_id"]) ? $_POST["cons_id"] : null;

            $caes_id = isset($_POST["caes_id_n"]) ? $_POST["caes_id_n"] : '';



            $sql = "SELECT cati_id, liae_id FROM carga WHERE carg_id = $carg_id";
            $result = mysql_query($sql);

            if ($result && mysql_num_rows($result) > 0) {
               $row = mysql_fetch_assoc($result);
               $cati_id = $row['cati_id']; // Ahora sí tienes el valor
			      $aerolinea = $row['liae_id'];
            } else {
               $cati_id = 0; // Valor por defecto si no hay resultados
            }

            // $cade_recibidas = $_POST["cade_recibidas"];
            // $cade_salida = $_POST["cade_salida"];
            // $coin_id = $_POST["coin_id"];
            // $cade_notas = $_POST["cade_notas"];
            // $cade_localizacion = $_POST["calb_id"];
            //cambiar tabla, hablar con David
            $stmt = "INSERT INTO carga_detalles(
   carg_id,
   cade_guia,
   cade_peso,
   cade_largo,
   cade_ancho,
   cade_alto,  
   cade_piezas,
   cade_desc,
   ship_id,
   cons_id,
   cade_tipo_id,    
   cade_notificada,
   cade_notificada_fecha,
   caes_id,
   cade_cantidad
   )
    VALUES (
      '$carg_id',
      " . (($cati_id == 2 || $cati_id == 4) ? 'NULL' : "'$cade_guia'") . ",
      '$cade_peso',
      '$cade_largo',
      '$cade_ancho',
      '$cade_alto',
      '$cade_piezas',  
      '$cade_desc',
      '$shipper',
      '$consignee',
      '$cade_tipo_id',
      '1',
      NOW(),
      '$caes_id',
      '$cade_cantidad'
      )";

            $query_success = false;

            try {
               $res = mysql_query($stmt);

               if ($res) {
                  $query_success = true; // Éxito - continuar con el resto del código
               }
            } catch (mysqli_sql_exception $e) {
               if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                  echo json_encode([
                     "success" => false,
                     "message" => "La guía ya está registrada."
                  ]);
               } else {
                  echo json_encode([
                     "success" => false,
                     "message" => "Error en la base de datos: " . $e->getMessage()
                  ]);
               }
               exit; // Sale aquí si hay error
            }

            // Solo continúa si la query fue exitosa
            if ($query_success) {
               //Vamos a insertarles los cargos a la carga que son import 
               $lastId = mysql_insert_id(); //Esto es del detalle de la carga

               // Vamos a insetar las localizaciones en bodega
               foreach ($calb_id as $ci) {
                  $sql = "INSERT INTO carga_detalle_localizacion(calb_id, cade_id) VALUES($ci, $lastId)";
                  mysql_query($sql);
               }

               // Insertamos tambien en la tabla de detalleDetalle
               $sql = "INSERT INTO carga_detalle_detalles(cadd_descripcion, cadd_peso, cati_id, cade_id,cadd_piezas,cadd_cantidad) VALUES ('$cade_desc', '$cade_peso', '$cade_tipo_id', '$lastId','$cade_piezas','$cade_cantidad')";

               mysql_query($sql);

               // Buscamos la guia dentro de la tabla carga_cargos, para saber si ya tiene monto asignado
               $sql_info = mysql_query("SELECT * FROM carga_cargos WHERE cade_guia = '$cade_guia'");

               if (mysql_numrows($sql_info) == 0) {
                  
               //COMO YA TENGO LA AEROLINEA DEBO BUSCAR EL SERVICIO DE AIT QUE LE CORRESPONDE A ESA AEROLINEA
               $qsql = "SELECT COUNT(*), COALESCE(case_monto, (SELECT case_monto FROM carga_servicios WHERE  case_es_ait=1 AND liae_id IS NULL limit 1)) monto
                        FROM carga_servicios 
                        WHERE case_es_ait = 1 AND liae_id='$aerolinea'";
               $ait_monto = obtener_valor($qsql, 'monto');
                           
               $AIT = $ait_monto * $cade_peso;

               /**
                * CONSULTA: INSERCIÓN AUTOMÁTICA DE CARGOS DE SERVICIO (INSERT INTO... SELECT)
               *
               * Propósito: Asigna automáticamente los servicios aplicables a una guía de detalle de carga (cade_guia).
               * Solo se insertan servicios que aún NO EXISTEN para esa guía, evitando duplicados.
               *
               * Variables de PHP utilizadas:
               * - $carg_id: ID de la carga principal.
               * - $cade_guia: Guía del detalle de carga.
               * - $usua_id: ID del usuario que realiza la acción.
               * - $cade_cantidad: Cantidad de piezas del detalle.
               * - $cade_peso: Peso de la carga del detalle.
               * - $AIT: Monto fijo para el cargo AIT (si aplica).
               * - $cade_tipo_id: Tipo de detalle de la carga (cadt_id).
               */
               $sql = "INSERT INTO carga_cargos (
                     carg_id, cade_guia, case_id, caca_monto, caca_fecha, usua_id, caca_facturado, caca_itbms
                     )
               SELECT 
               -- Asignación de IDs y metadatos
               '$carg_id',
               '$cade_guia',
               cs.case_id,
               
               -- LÓGICA DE CÁLCULO DEL MONTO BASE (caca_monto)
               (
                CASE
                    -- [Regla 1: Por Cantidad] Si el servicio aplica a tipos de detalle 7 u 8 (e.g., cargos fijos por pieza), se calcula: Cantidad * Monto Unitario.
                    WHEN cs.cadt_id IN (7,8) THEN $cade_cantidad * cs.case_monto
                    
                    -- [Regla 2: Por Peso con Mínimo] Si tiene peso mínimo definido Y el peso de la carga lo supera, se aplica la tarifa máxima por peso: Peso * Monto Máx.
                    WHEN cs.case_peso_minimo > 0 AND $cade_peso > cs.case_peso_minimo THEN $cade_peso * cs.case_monto_max
                    
                    -- [Regla 3: Monto Fijo] Si no aplica ninguna de las anteriores, se toma el monto fijo predefinido para el servicio.
                    ELSE cs.case_monto
                END
                +
                -- ADICIONAL: Se suma el cargo AIT (si el servicio lo requiere)
                CASE 
                    WHEN cs.case_ait = 1 AND cs.cadt_id NOT IN (7,8) THEN $AIT
                    ELSE 0
                END
               ),
            
               NOW(),
               '$usua_id',
               0, -- caca_facturado: Se inserta siempre como 0 (No Facturado)
               
               -- LÓGICA DE CÁLCULO DEL ITBMS (7%)
               CASE 
                -- [ITBMS Regla 1: Por Cantidad] Aplica el 7% sobre el cálculo por cantidad.
                WHEN cs.cadt_id IN (7,8) THEN $cade_cantidad * cs.case_monto * 0.07
                
                -- [ITBMS Regla 2: Por Peso con Mínimo] Aplica el 7% sobre el cálculo por peso, solo si case_itbms=1.
                WHEN cs.case_itbms = 1 AND cs.case_peso_minimo > 0 AND $cade_peso > cs.case_peso_minimo THEN ($cade_peso * cs.case_monto_max) * 0.07
                
                -- [ITBMS Regla 3: Monto Fijo] Aplica el 7% sobre el monto fijo, solo si case_itbms=1.
                WHEN cs.case_itbms = 1 THEN cs.case_monto * 0.07
                
                -- Si no aplica ninguna regla o case_itbms = 0, el impuesto es cero.
                ELSE 0
               END AS caca_itbms
            
               -- FUENTE DE DATOS Y FILTROS
               FROM carga_servicios cs
               
               -- UNIÓN: Busca cargos existentes para la misma guía y servicio (para evitar duplicados)
               LEFT JOIN carga_cargos cc
                     ON cc.cade_guia = '$cade_guia'
                     AND cc.case_id = cs.case_id
                     
               -- FILTRO PRINCIPAL: Solo inserta los servicios que NO EXISTEN (es decir, el LEFT JOIN no encontró coincidencia)
               WHERE cc.case_id IS NULL
                  AND (
                        -- FILTRO DE APLICACIÓN DEL SERVICIO
                        -- Condición A: Servicios Automáticos para TODOS los tipos de carga (cadt_id = 0)
                        (cs.case_automatico = 1 AND cs.cadt_id = 0)
                        
                        -- Condición B: Servicios Automáticos específicos para el tipo de carga actual ($cade_tipo_id)
                        OR (cs.case_automatico = 1 AND cs.cadt_id = $cade_tipo_id)

                        -- Condición C: Servicios NO Automáticos que aplican al tipo de carga actual
                        OR (cs.case_automatico = 0 AND cs.cadt_id = $cade_tipo_id)
                     )";
                  mysql_query($sql);

                  // AIT solo aplica si no es 7 ni 8
                  $sql = "UPDATE carga_cargos cc
        INNER JOIN carga_servicios cs ON cs.case_id = cc.case_id
        SET cc.caca_monto = '$AIT'
        WHERE cc.cade_guia = '$cade_guia' 
          AND cc.case_id = 2
          AND cs.cadt_id NOT IN (7,8)";

                  mysql_query($sql);
               }



               /**
                * Vamos a enviar el email de carga recibida en bodega  
                * 1. Buscamos el email del consignee
                * */

               $cons_email = obtener_valor("SELECT cons_email FROM consignee WHERE cons_id = '$consignee'", "cons_email");
               $cons_nombre = obtener_valor("SELECT cons_nombre FROM consignee WHERE cons_id = '$consignee'", "cons_nombre");

               include "./mailerConfig.php";

               // Buscar la guia
               // $cade_guia = obtener_valor("SELECT cade_guia FROM carga_detalles WHERE cade_id = '$cade_id'", "cade_guia");

               // Vamos a buscar la plantailla sencilla de CARGA-RECIBIDA-BODEGA
               $plantilla = mysql_fetch_assoc(mysql_query("SELECT * FROM contratos WHERE cont_nombre = 'CARGA-RECIBIDA-BODEGA'"))["cont_detalle"];

               $plantilla = str_replace("[CONS_NOMBRE]", $cons_nombre, $plantilla);
               $plantilla = str_replace("[NUMERO_GUIA]", $cade_guia, $plantilla);

               enviar_email($smtp_username, "GIRAG INFO", "CARGA RECIBIDA EN BODEGA DE GIRAG", $plantilla, [$cons_email], $smtp_username, $smtp_password, new PHPMailer());

               // Verificar errores SQL al final
               if (mysql_error()) {
                  echo json_encode(["error_sql" => mysql_error()]);
                  exit;
               }

               // Solo una respuesta de éxito al final
               echo json_encode([
                  "success" => true,
                  "message" => "Registro agregado correctamente"
               ]);
               exit;
            } else {
               // Si llegó aquí es porque $res fue false pero no hubo excepción
               echo json_encode([
                  "success" => false,
                  "message" => "Error al insertar el registro"
               ]);
               exit;
            }
         }
      }
      break;
   case "GET":
      if (isset($_GET["cade_id"])) {
         // echo "Hola";
         // Esto trae todos los detalles de la carga
         $cade_id = $_GET["cade_id"];
         $stmt = "SELECT * FROM carga_detalles WHERE cade_id = '$cade_id'";
         $res = mysql_fetch_assoc(mysql_query($stmt));
         echo json_encode($res);
      } elseif (isset($_GET["a"])) {

         switch ($_GET["a"]) {
            case "caja-detalles":

               // $cade_id = $_GET["id"];
               $cade_guia = isset($_GET["guia"]) ? $_GET["guia"] : "";
               $stmt = "SELECT * FROM carga_cargos a 
               JOIN carga_servicios b ON a.case_id = b.case_id
               WHERE cade_guia = '$cade_guia'";
               $res = mysql_query($stmt);

               $data = [];
               while ($fila = mysql_fetch_assoc($res)) {

                  $data["detalles"][] = $fila;
               }

               if (empty($data["detalles"])) {
                  $data["detalles"][] = [];
               }

               $stmt = "SELECT *,
                     COALESCE((SELECT ingr_fe_qr FROM ingresos c WHERE b.cons_id = clie_id AND c.cade_guia = a.cade_guia 
                     ORDER BY ingr_id DESC LIMIT 1 ), 0) fact_url, 
                     (SELECT cade_descripcion FROM carga_detalle_tipo WHERE cade_tipo_id=a.cade_tipo_id) tipo
                     FROM carga_detalles a
                     JOIN consignee b ON a.cons_id = b.cons_id
                     WHERE a.cade_guia = '$cade_guia' GROUP BY cade_guia";
               $data["detail_info"] = mysql_fetch_assoc(mysql_query($stmt));

               echo json_encode($data);
               break;
         }
      }
      break;
   case "DELETE":
      $_DELETE = json_decode(file_get_contents("php://input"), true);
      $cade_id = $_DELETE["cade_id"];
      mysql_query("DELETE FROM carga_detalles WHERE cade_id = $cade_id");
      if (!mysql_error()) {
         echo json_encode(["success" => true]);
      }
      break;
}
