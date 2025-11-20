<?php
session_start();
$usua_id = $_SESSION["login_user"];
session_write_close();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use \Mpdf\Mpdf;

require "../vendor/autoload.php";

include '../conexion.php';
include '../funciones.php';
include "./FacturaService.php";
// $hkApi = new FacturaService("aojjjucbweqb_tfhka", "CmY.ZAMYYV+!", "https://demoemision.thefactoryhka.com.pa/ws/obj/v1.0/Service.svc?singleWsdl");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":


      if (isset($_POST["a"]) and $_POST["a"] == "actualizarCampos") {

         $where = "";

         if (isset($_POST["action"]) and $_POST["action"] == "facturacion") $where .= " AND caca_facturado = 0";

         $cade_id = $_POST["cade_id"];
         $campo = $_POST["campo"];
         $valor = $_POST["valor"];

         $sql = "UPDATE carga_detalles SET $campo = '$valor' WHERE cade_id = $cade_id $where ";

         mysql_query($sql);

         if (mysql_errno()) {
            http_response_code(500);
            die(json_encode(["err" => "Error de base de datos"]));
         }

         echo json_encode(["msg" => "$campo actualizado correctamente"]);
      } elseif (isset($_POST["a"]) && $_POST["a"] == "enviarEmail") {

         include "mailer_config_caja.php";

         $cons_email = $_POST["cons_email"];
         $cons_nombre = $_POST["cons_nombre"];
         $cade_id = $_POST["cade_id"];

         // =========================================================
         // === NUEVA LÓGICA PARA MÚLTIPLES DESTINATARIOS (START) ===
         // =========================================================
         
         $email_list = explode(';', $cons_email);
         $recipients_added = 0;

         // Inicializa PHPMailer aquí, antes del bucle, para no re-inicializarlo
         $mail = new PHPMailer(true);
         // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
         $mail->isSMTP();
         $mail->SMTPDebug  = 2;
         $mail->Host = $smtp_host;
         $mail->SMTPAuth = true;
         $mail->Username = $smtp_username;
         $mail->Password = $smtp_password;
         $mail->SMTPSecure = 'ssl';
         $mail->Port = 465;
         $mail->CharSet = "UTF-8";
         $mail->isHTML(true);


         foreach ($email_list as $recipient) {
               $recipient = trim($recipient); // Limpiar espacios en blanco
               
               // Usamos filter_var para validar el formato del email antes de agregarlo
               if (!empty($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                  $mail->addAddress($recipient);
                  $recipients_added++;
               }
         }

         // Si no se agregó ningún destinatario válido, salir para evitar errores de envío.
         if ($recipients_added === 0) {
               echo json_encode(['success' => false, 'message' => "Error: No se encontró ningún email válido en la lista para enviar."]);
               exit;
         }

         // =======================================================
         // === NUEVA LÓGICA PARA MÚLTIPLES DESTINATARIOS (END) ===
         // =======================================================

         // Buscar la guia
         // $cade_guia = obtener_valor("SELECT cade_guia FROM carga_detalles WHERE cade_id = '$cade_id'", "cade_guia");
         $carga_detalle = mysql_fetch_assoc(mysql_query("SELECT * FROM carga_detalles WHERE cade_id = '$cade_id'"));
         // print_r($carga_detalle);
         $cade_guia = $carga_detalle["cade_guia"];
         $cade_peso = $carga_detalle["cade_peso"];
         $cade_cantidad = $carga_detalle["cade_cantidad"];

         $plantilla = mysql_fetch_assoc(mysql_query("SELECT * FROM contratos WHERE cont_nombre = 'CAJA-NOTIFICACION-CLIENTE'"))["cont_detalle"];
         $plantilla = str_replace("[FECHA_HOY]", date("d/m/Y"), $plantilla);
         $plantilla = str_replace("[CONS_NOMBRE]", $cons_nombre, $plantilla);
         $plantilla = str_replace("[NUMERO_GUIA]", $cade_guia, $plantilla);
         $plantilla = str_replace("[PIEZAS]", $carga_detalle["cade_piezas"], $plantilla);
         $plantilla = str_replace("[PESO]", $carga_detalle["cade_peso"], $plantilla);
         $plantilla = str_replace("[FECHA_NOTIFICACION]", date("d/m/Y"), $plantilla);
         // Obtener descripción del tipo de carga desde la tabla relacionada
         $cade_tipo_id = $carga_detalle["cade_tipo_id"];
         $query_tipo = mysql_query("SELECT cade_descripcion FROM carga_detalle_tipo WHERE cade_tipo_id = '$cade_tipo_id'");
         $tipo = mysql_fetch_assoc($query_tipo);
         $descripcion_tipo = strtolower(trim($tipo["cade_descripcion"]));

         // Obtener fecha actual
         // Obtener fecha actual
         $fecha_notificacion = new DateTime(); // Hoy
         $pago_almacenaje = 0;

         if ($descripcion_tipo == "regular") {
            // ====== FECHA (48 horas hábiles) ======
            $dias_habiles_a_sumar = 2; // 48 horas en días hábiles
            $fecha_inicio = clone $fecha_notificacion;
            $dias_sumados = 0;
            while ($dias_sumados < $dias_habiles_a_sumar) {
               // Avanzar un día
               $fecha_inicio->modify('+1 day');
               $dia_semana = (int)$fecha_inicio->format('w');
               $fecha_str = $fecha_inicio->format('Y-m-d');
               $query = "SELECT COUNT(*) as total FROM dias_feriados WHERE dife_fecha = '$fecha_str'";
               $result = mysql_query($query);
               $row = mysql_fetch_assoc($result);
               $es_feriado = ($row['total'] > 0);

               if ($dia_semana != 0 && $dia_semana != 6 && !$es_feriado) {
                  $dias_sumados++;
               }
            }

            
            if ($cade_peso <= 125) {
               $pago_base = 25;
            } else {
               $pago_base = $cade_peso * 0.20;
            }

            $pago_almacenaje = $pago_base + ($pago_base * 0.07); 


            $texto_almacenaje = "A partir de las 48 horas hábiles de notificación (" . $fecha_inicio->format("d/m/Y") . ") - Monto: B/ " . number_format($pago_almacenaje, 2);
         } elseif ($descripcion_tipo == "valor") {
            
            if ($cade_peso <= 130) {
               $pago_base = 45;
            } else {
               $pago_base = $cade_peso * 0.35;
            }

            $pago_almacenaje = $pago_base + ($pago_base * 0.07); 

            $texto_almacenaje = "Inmediato (" . $fecha_notificacion->format("d/m/Y") . ") - Monto: B/ " . number_format($pago_almacenaje, 2);
         } elseif ($descripcion_tipo == "refrigerado") {
            
               if ($cade_peso <= 101) {
               $pago_base = 35;
            } else {
               $pago_base = $cade_peso * 0.35;
            }

            $pago_almacenaje = $pago_base + ($pago_base * 0.07); 

            $texto_almacenaje = "Inmediato (" . $fecha_notificacion->format("d/m/Y") . ") - Monto: B/ " . number_format($pago_almacenaje, 2);
         }else {
          
            $pago_almacenaje = ($cade_cantidad * 125);
            $pago_almacenaje += $pago_almacenaje * 0.07;

            $texto_almacenaje = "Inmediato (" . $fecha_notificacion->format("d/m/Y") . ") - Monto: B/ " . number_format($pago_almacenaje, 2);
         }

         // Reemplazar en plantilla
         $plantilla = str_replace("[COSTO_ALMACENAJE]", $texto_almacenaje, $plantilla);


         //Descripcion de los cargos que se van a hacer
         $carga_cargos = mysql_query("SELECT *, SUM(caca_monto + caca_itbms) caca_total FROM carga_cargos a,carga_servicios b WHERE a.case_id = b.case_id AND cade_guia = '$cade_guia' GROUP BY caca_id");

         $tablaDescripcion = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; margin-bottom:20px; width:100%">
         <thead>
         <tr>
         <th>Descripción</th>
         <th>Monto</th>
         <th>ITBMS</th>
         <th>Total</th>
         </tr>
         </thead>
         <tbody>';

         $total_monto = 0;
         $total_itbms = 0;
         $total_general = 0;

         while ($fila = mysql_fetch_assoc($carga_cargos)) {
            $tablaDescripcion .= "
            <tr>
               <td>" . $fila["case_nombre"] . "</td>
               <td>" . $fila["caca_monto"] . "</td>
               <td>" . $fila["caca_itbms"] . "</td>
               <td>" . $fila["caca_total"] . "</td>
            </tr>";

            $total_monto += $fila["caca_monto"];
            $total_itbms += $fila["caca_itbms"];
            $total_general += $fila["caca_total"];
         }

         $tablaDescripcion .= '</tbody>
            <tfoot>
              
            </tfoot>
         </table>';
         $plantilla = str_replace("[TABLA_CARGOS]", $tablaDescripcion, $plantilla);
         $htmlTabla = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
         <thead>
         <tr>
         <th>Cant Piezas</th>
         <th>Peso</th>
         <th>Descripción del Servicio</th>

         </tr>
         </thead>
         <tbody>';

                  $sql = "SELECT 
         IFNULL(dd.cadd_piezas, d.cade_piezas) AS piezas,
         IFNULL(dd.cadd_peso, d.cade_peso) AS peso,
         t.cade_descripcion AS descripcion_servicio,
         0 AS vr_total_z
         FROM 
         carga_detalles d
         LEFT JOIN 
         carga_detalle_detalles dd ON dd.cade_id = d.cade_id
         LEFT JOIN 
         carga_detalle_tipo t ON d.cade_tipo_id = t.cade_tipo_id
         WHERE 
         d.cade_id = '$cade_id'";

         $res = mysql_query($sql);

         while ($fila = mysql_fetch_assoc($res)) {
            $htmlTabla .= "<tr>
            <td>{$fila['piezas']}</td>
            <td>{$fila['peso']}</td>
            <td>Carga {$fila['descripcion_servicio']}</td>
            
         </tr>";
         }

         $htmlTabla .= '</tbody></table>';

         // Reemplaza en tu plantilla
         $plantilla = str_replace("[TABLA_CARGA]", $htmlTabla, $plantilla);


         $mail->Subject = "Notificación de Carga - Guía " . $cade_guia;
         $mail->Body = "<body style='font-family:Verdana, Arial, Helvetica'>" . $plantilla . '</body>';
         
         $mail->send();

         // Respuesta de éxito al cliente
         echo json_encode(['success' => true, 'message' => "Correo enviado a {$recipients_added} destinatario(s)."]);
        
         //} catch (Exception $e) {
         //    echo $e->getMessage();
         // }
         // Vamos a actualizar el estado de notificacion
         mysql_query("UPDATE carga_detalles SET cade_fecha_notificacion = NOW() WHERE cade_id = $cade_id");
      } elseif (isset($_POST["a"]) and $_POST["a"] == "facturar") 
			{

			 /**
			  * Aqui vamos a enviar la factura electronica
			  */
			 $last_id;
			 $formaPagoFact;

            $tipoContribuyente = $_GET["tipo"];
            $isConsumidorFinal =  $_GET["consumidorFinal"];

            // Definir los tipos según consumidor final o no
            $tipoClienteFE = $isConsumidorFinal === "true" ? "02" : "01";

            if (!$isConsumidorFinal) {
               if ($_GET["ruc"] == "" or $_GET["dv"] == "") {
                  http_response_code(500);
                  echo json_encode(["msg" => "El RUC y el DV deben estar llenos"]);
                  die();
               }
            }
               
			 if (isset($_POST["cade_id"])) { //Aqui vamos a procesar desde la tabla de carga_cargos y transformarla en ingresos

				$totalFactura = 0;
				$subtotalFactura = 0;
				$valuesDetalles = [];

				//Transformar en factura -------------------------------------------
				$cade_id = $_POST["cade_id"];
				//  buscamos la guia de ese cade_id
				$cade_guia = obtener_valor("SELECT cade_guia FROM carga_detalles WHERE cade_id = '$cade_id'", "cade_guia");
				$carg_id = obtener_valor("SELECT carg_id FROM carga_detalles WHERE cade_id = '$cade_id'", "carg_id");
				// $cade_guia = $_POST["cade_id"];
				$formaPagoFact = obtener_valor("SELECT fopa_codigo FROM facturas_pagos a, forma_pago b WHERE fapa_codigo_agrupado = '$cade_guia' AND a.fopa_id = b.fopa_id", "fopa_codigo");

				// Traer la info de la carga
				$cade_info = mysql_fetch_assoc(mysql_query("SELECT * FROM carga_detalles WHERE cade_guia = '$cade_guia'"));

				// Traer todos los cargos no facturados
				$cargos = mysql_query("SELECT * FROM carga_cargos a JOIN carga_servicios b ON a.case_id = b.case_id WHERE cade_guia = '$cade_guia' AND caca_facturado = 0");

				$cons_id = (int)$cade_info['cons_id']; // Sanear ID

				$query = "SELECT * FROM consignee WHERE cons_id = $cons_id";
				$result = mysql_query($query);

				if ($result && mysql_num_rows($result) > 0) {
				   $cons_info = mysql_fetch_assoc($result);
				} else {
				   $cons_info = null;
				   echo "No se encontró el consignatario o error en la consulta.";
				   die();
				}

				// print_r($cons_info);

				// Traer la plantilla
				$plantilla = mysql_fetch_assoc(mysql_query("SELECT cont_detalle FROM contratos WHERE cont_nombre = 'CAJA-VOUCHER'"))["cont_detalle"];

				// Agregar la etiqueta style
				$plantilla .= "<style>

				img {
				   width: 500px; /* Ajusta el ancho */
				   height: auto; /* Mantiene la proporción */
				   }
				   
				   tr{
					  font-size: 28px; /* Tamaño de letra grande */
					  font-weight: bold;
					  
					  }
					  </style>";



				$detalle_factura = "";
				// Buscar en base de datos la ultima secuencia del ingreso para ponerla en numero de factura
				$secuencia = obtener_valor("SELECT * FROM ingresos_secuencia WHERE id = 1", "inse_numeracion");
				// Actualizar la secuencia
				mysql_query("UPDATE ingresos_secuencia SET inse_numeracion = inse_numeracion + 1 WHERE id = 1");

				$cod_temporal = obtener_valor("SELECT cote_id FROM codigos_temporales LIMIT 1", "cote_id");

				//Actualizar codigos temporales
				mysql_query("UPDATE codigos_temporales SET cote_id = cote_id + 1");

				//Crear el madre del ingreso
				$sql = "INSERT INTO ingresos(clie_id, ingr_fecha, usua_id, ingr_fecha_creacion, ingr_numero_factura, ingr_subtotal, ingr_total, cade_guia, faes_id, carg_id, ingr_tipo_cliente_FE, ingr_tipo_contribuyente_FE) 
				VALUES('{$cons_info["cons_id"]}', NOW(), '$usua_id', NOW(), '$secuencia', '0', '0', '$cade_guia', 3, $carg_id, '$tipoClienteFE', '$tipoContribuyente')";
				mysql_query($sql);

				$last_id = mysql_insert_id(); //Este es el ID del ingreso_madre


				while ($fila = mysql_fetch_assoc($cargos)) {
				   /**
					* 1.Crear el html para la plantilla
					* 2.Crear el value para ingresos detalles
					* 3.Suma para calcular el total
					*/
				   $itbms_rate = $fila["caca_itbms"] != 0 ? "01" : "00";

				   $detalle_factura .= "<tr style='border: 5px solid #000'>
				<td style='border-color:#dddddd; text-align:left'>{$fila['case_nombre']}</td>
				<td style='border-color:#dddddd; text-align:left'>{$fila['caca_monto']}</td>
				</tr>";

				   $valuesDetalles[] = [$last_id, $fila['case_id'], 1, $fila['caca_monto'], $fila['case_nombre'], $fila['caca_itbms'], $itbms_rate, $fila["case_reembolsable"], $cod_temporal];
				}

				$values_sql = array_map(function ($row) {
				   return "('" . implode("','", $row) . "')";
				}, $valuesDetalles);
				$string_values = implode(", ", $values_sql);

				//Vamos a decirle a los cargos que estoy procesando que se pasen a facturados
				$sql = mysql_query("UPDATE carga_cargos SET caca_facturado = 1 WHERE cade_guia = '$cade_guia' AND caca_facturado = 0");

				//SQL DE DETALLES
				$sql_detalles = "INSERT INTO ingresos_detalle(ingr_id, prod_id, inde_cantidad, ingr_precio, inde_detalle, ingr_itbms, inde_tasa_itbms, inde_reembolsable, inde_temp_code) VALUES $string_values;";

				mysql_query($sql_detalles);

				// SQL para enlazar los pagos a la factura
				$sql = "UPDATE facturas_pagos SET fact_id = '$last_id', fapa_facturado = 1 WHERE fapa_codigo_agrupado = '$cade_guia' AND fapa_facturado = 0";
				mysql_query($sql);

            // DEBUG: Ver qué hay en ingresos_detalle ANTES de actualizar totales
            error_log("=== VERIFICAR INGRESOS_DETALLE ANTES DE UPDATE TOTALES ===");
            error_log("ingr_id: $last_id");

            $debug_detalle = mysql_query("SELECT inde_id, inde_detalle, ingr_precio, ingr_itbms, inde_temp_code FROM ingresos_detalle WHERE ingr_id = '$last_id'");
            $count = 0;
            $sum_precio = 0;
            $sum_itbms = 0;

            while($dd = mysql_fetch_assoc($debug_detalle)) {
               $count++;
               $sum_precio += $dd['ingr_precio'];
               $sum_itbms += $dd['ingr_itbms'];
               error_log("  Detalle #$count - ID: {$dd['inde_id']} | Desc: {$dd['inde_detalle']} | Precio: {$dd['ingr_precio']} | ITBMS: {$dd['ingr_itbms']} | Temp_code: {$dd['inde_temp_code']}");
            }

            error_log("TOTAL items encontrados: $count");
            error_log("SUMA precios: $sum_precio");
            error_log("SUMA itbms: $sum_itbms");
            error_log("TOTAL general: " . ($sum_precio + $sum_itbms));

            // Comparar con lo que se insertó
            error_log("Items que se insertaron en este proceso: " . count($valuesDetalles));

            // Obtener el código temporal que usamos en esta inserción
            $cod_temp_actual = $cod_temporal;

            // Actualizar totales solo con los registros que tienen este código temporal
            $sql = "UPDATE ingresos SET 
            ingr_impuesto = (SELECT SUM(ingr_itbms) FROM ingresos_detalle WHERE ingr_id = '$last_id' AND inde_temp_code = '$cod_temp_actual'),
            ingr_subtotal = (SELECT SUM(ingr_precio) FROM ingresos_detalle WHERE ingr_id = '$last_id' AND inde_temp_code = '$cod_temp_actual'),
            ingr_total = (SELECT (SUM(ingr_precio) + SUM(ingr_itbms)) FROM ingresos_detalle WHERE ingr_id = '$last_id' AND inde_temp_code = '$cod_temp_actual')
            WHERE ingr_id = '$last_id'";
            mysql_query($sql);

				// Vamos a actualizar los totales
				// $sql = "UPDATE ingresos SET 
				// ingr_impuesto = (SELECT SUM(ingr_itbms) FROM ingresos_detalle WHERE ingr_id = '$last_id' GROUP BY ingr_id),
				// ingr_subtotal = (SELECT SUM(ingr_precio) FROM ingresos_detalle WHERE ingr_id = '$last_id' GROUP BY ingr_id),
				// ingr_total = (SELECT (SUM(ingr_precio) + SUM(ingr_itbms)) FROM ingresos_detalle WHERE ingr_id = '$last_id' GROUP BY ingr_id)
				// WHERE ingr_id = '$last_id'";
				// mysql_query($sql);
			 } else {
				$last_id = $_GET["ingr_id"];
				$formaPagoFact = "01";
			 }

			 //EMPEZAMOS A FACTURAR A ENVIAR A HK-----------------------------------------------------------------------------------------

			 // Vamos a buscar la factura dentro de la tabla ingresos
			 $sql = "SELECT *, 
			 (SELECT COUNT(*) FROM ingresos_detalle WHERE ingr_id = '$last_id' GROUP BY ingr_id) total_items
			 FROM ingresos a, consignee b WHERE ingr_id = '$last_id' AND a.clie_id = b.cons_id";
			 $ingr_info = mysql_fetch_assoc(mysql_query($sql));

			 //Vamos a buscar el detalle de ingresos_detalle
			 $sql = "SELECT * FROM ingresos_detalle WHERE ingr_id = '$last_id'";
			 $res_ingr_detalle = mysql_query($sql);

			 // Vamos a crear el cuerpo de los item
			 $items_FE = [];
			 $items_reembolso = [];

			 // TOTALES AIT
			 $itemsAIT = 0;
			 $subtotalAIT = 0;
			 $totalPrecioNetoAIT = 0;
			 $totalITBMSAIT = 0;
			 $totalFacturaAIT = 0;

			 // Fctura normal
			 $precio_neto = number_format(0.00, 2, ".");
			 $itbms = number_format(0.00, 2, ".");
			 $items = 0;

			 while ($fila = mysql_fetch_assoc($res_ingr_detalle)) {
				if ($fila["inde_reembolsable"] == 1) {
				   $itemTotal = number_format($fila["inde_cantidad"] * $fila["ingr_precio"], 2, '.', '');
				   $itbms_monto = $fila["ingr_itbms"];

				   $items_reembolso[] = array(
					  "descripcion" => $fila["inde_detalle"],
					  "codigo" => "NIGR",
					  "unidadMedida" => "und",
					  "cantidad" => $fila["inde_cantidad"],
					  "precioUnitario" => $fila["ingr_precio"],
					  "precioUnitarioDescuento" => "0.00",
					  "precioItem" => number_format($itemTotal, 2, '.', ''),
					  "valorTotal" => number_format(($itemTotal + $itbms_monto), 2, '.', ''),
					  "tasaITBMS" => $fila["inde_tasa_itbms"],
					  "valorITBMS" => $fila["ingr_itbms"]
				   ); // Se agrega a la lista de reembolso

				   $itemsAIT++;
				   $subtotalAIT += $fila["ingr_precio"];
				   $totalPrecioNetoAIT += $fila["ingr_precio"];
				   $totalITBMSAIT += $fila["ingr_itbms"];
				   $totalFacturaAIT += ($fila["ingr_itbms"] + $fila["ingr_precio"]);
				} else {
				   $itemTotal = number_format($fila["inde_cantidad"] * $fila["ingr_precio"], 2, '.', '');
				   $itbms_monto = $fila["ingr_itbms"];

				   $items_FE[] = array(
					  "descripcion" => $fila["inde_detalle"],
					  "codigo" => "NIGR",
					  "unidadMedida" => "und",
					  "cantidad" => $fila["inde_cantidad"],
					  "precioUnitario" => $fila["ingr_precio"],
					  "precioUnitarioDescuento" => "0.00",
					  "precioItem" => number_format($itemTotal, 2, '.', ''),
					  "valorTotal" => number_format(($itemTotal + $itbms_monto), 2, '.', ''),
					  // "tasaITBMS" => $fila["inde_tasa_itbms"],
					  "tasaITBMS" => $fila["inde_tasa_itbms"],
					  "valorITBMS" => $fila["ingr_itbms"]
				   );

				   $items++;
				   $precio_neto += $itemTotal;
				   $itbms += $fila["ingr_itbms"];
				}
			 }

			 $facturas_tosend = array(
   "codigoSucursalEmisor" => "0000",
   "datosTransaccion" => array(
      "tipoEmision" => "01",
      "tipoDocumento" => "02",
			"numeroDocumentoFiscal" => str_pad($ingr_info["ingr_numero_factura"], 5, '0', STR_PAD_LEFT),
      "puntoFacturacionFiscal" => "001",
      "naturalezaOperacion" => "01",
      "tipoOperacion" => "1",
			"destinoOperacion" => "1", // PANAMA
      "formatoCAFE" => "1",
      "entregaCAFE" => '1',
      "envioContenedor" => "1",
      "procesoGeneracion" => "1",
			"fechaEmision" => date("Y-m-d\TH:i:s-05:00", strtotime($ingr_info["ingr_fecha"])),
      "informacionInteres" => "Factura de Venta",
      "cliente" => array(
         "tipoClienteFE" => "$tipoClienteFE",
         "tipoContribuyente" => "$tipoContribuyente",
         "codigoUbicacion" => "1-1-1",
         "numeroRUC" => (string)$ingr_info['cons_ruc'],
				 "digitoVerificadorRUC" => $ingr_info["cons_dv"],
				 "razonSocial" => $ingr_info["cons_nombre"],
         "direccion" => "PANAMA",
         "corregimiento" => "",
         "distrito" => "",
         "provincia" => "",
         "telefono1" => "",
				 "correoElectronico1" => $ingr_info["cons_email"],
         "pais" => "PA"
      ),
   ),
   "listaItems" => $items_FE,
   "totalesSubTotales" => array(
      "totalPrecioNeto" => number_format($precio_neto, 2, "."),
      "totalITBMS" => number_format($itbms, 2, "."),
      "totalMontoGravado" => number_format($itbms, 2, "."),
      "totalDescuento" => "0.00",
      "totalFactura" => number_format($precio_neto + $itbms, 2, "."),
      "totalValorRecibido" => number_format($precio_neto + $itbms, 2, "."),
      "totalTodosItems" => number_format($precio_neto + $itbms, 2, "."),
      "tiempoPago" => "1",
      "nroItems" => $items,
      "listaFormaPago" => array(
         array(
            "formaPagoFact" => "$formaPagoFact",// 01:Crédito. 02:Efectivo. 03:Tarjeta Crédito. 04:Tarjeta Débito. 05:Tarjeta Fidelización. 06:Vale. 07:Tarjeta de Regalo. 08:Transf/Deposito cta. Bancaria 09: Cheque 99:Otro.
            "valorCuotaPagada" => number_format($precio_neto + $itbms, 2, "."),
            "descFormaPago" => null
         )
      )
   )
);

			 // print_r($facturas_tosend);
			 	// Variables de control
				$error_fe = false;
				$error_mensaje = "";
				$fe_exitosa = false;
				$warning_fe = false;
				$warning_mensaje = "";

				// Enviar factura principal sin el ait
				if (!empty($facturas_tosend)) {
					$res = $hkApi->enviarFactura($facturas_tosend);
					
					// VERIFICAR SI HAY ERROR
					if (!isset($res->codigo) || ($res->codigo != 200 && $res->codigo != 201)) {
							$error_fe = true;
							
							// Capturar mensaje de error detallado
							if (isset($res->mensaje)) {
								$error_mensaje = $res->mensaje;
							} elseif (isset($res->err)) {
								$error_mensaje = $res->err;
							} else {
								$error_mensaje = "Respuesta desconocida del servicio de Factura Electrónica";
							}
							
							// Sanitizar para BD (limitar a 60 caracteres)
							$error_db = mysql_real_escape_string(substr($error_mensaje, 0, 60));
							
							// Marcar el ingreso con error de FE
							$sql_error = "UPDATE ingresos SET 
														ingr_fe_comentario = 'ERROR FE: $error_db'
														WHERE ingr_id = '$last_id'";
							mysql_query($sql_error);
							
					} else {
							// FACTURA EXITOSA
							$fe_exitosa = true;
							
							// Obtener datos de la respuesta exitosa
							$fe_fecha = $res->fechaRecepcionDGI;
							$fe_cufe = $res->cufe;
							$fe_qr = $res->qr;
							$fe_protocolo_autoizacion = $res->nroProtocoloAutorizacion;
							
							// Actualizar ingreso con datos de FE
							$sql = "UPDATE ingresos SET 
											ingr_fe_fecha = '$fe_fecha', 
											ingr_fe_cufe = '$fe_cufe', 
											ingr_fe_qr = '$fe_qr', 
											ingr_fe_protocolo_autoizacion = '$fe_protocolo_autoizacion',
											ingr_fe_comentario = NULL
											WHERE ingr_id = '$last_id'";
							mysql_query($sql);
							
							$res_factura_info = $res;
					}
				}

			 // MARCAR CARGA COMO FACTURADA (independientemente del estado de FE)
			if (isset($cade_guia) and !empty($cade_guia)) {
				mysql_query("UPDATE carga_detalles SET cade_facturada = 1 WHERE cade_guia = '$cade_guia'");
			}

			// PROCESAR REEMBOLSO (solo si la factura principal fue exitosa)
			if ($fe_exitosa && !empty($items_reembolso)) {
				
				$facturas_tosend["listaItems"] = $items_reembolso;
				$facturas_tosend["totalesSubTotales"]["totalPrecioNeto"] = number_format($subtotalAIT, "2", ".", "");
				$facturas_tosend["totalesSubTotales"]["totalITBMS"] = number_format($totalITBMSAIT, 2, ".");
				$facturas_tosend["totalesSubTotales"]["totalMontoGravado"] = number_format($totalITBMSAIT, 2, ".");
				$facturas_tosend["totalesSubTotales"]["totalFactura"] = number_format($totalFacturaAIT, 2, ".");
				$facturas_tosend["totalesSubTotales"]["totalValorRecibido"] = number_format($totalFacturaAIT, 2, ".");
				$facturas_tosend["totalesSubTotales"]["nroItems"] = $itemsAIT;
				$facturas_tosend["totalesSubTotales"]["totalTodosItems"] = number_format($totalFacturaAIT, 2, ".");
				$facturas_tosend["totalesSubTotales"]["listaFormaPago"][0]["valorCuotaPagada"] = number_format($totalFacturaAIT, 2, ".");
				
				// Ahora vamos a enviar la factura que vamos a reembolsar ----------------------------------
				// Buscar en base de datos la ultima secuencia del ingreso para ponerla en numero de factura
				$secuencia = obtener_valor("SELECT * FROM ingresos_secuencia WHERE id = 1", "inse_numeracion");
				mysql_query("UPDATE ingresos_secuencia SET inse_numeracion = inse_numeracion + 1 WHERE id = 1");
				$facturas_tosend["datosTransaccion"]["numeroDocumentoFiscal"] = str_pad($secuencia, 5, '0', STR_PAD_LEFT);
				$facturas_tosend["datosTransaccion"]["tipoDocumento"] = "09";
				
				$res_reembolso = $hkApi->enviarFactura($facturas_tosend);
				
				if (!isset($res_reembolso->codigo) || $res_reembolso->codigo != 200) {
						$warning_fe = true;
						$warning_mensaje = isset($res_reembolso->mensaje) ? $res_reembolso->mensaje : "Error en reembolso AIT";
						
						$error_reembolso_db = mysql_real_escape_string(substr($warning_mensaje, 0, 200));
						$sql_warning = "UPDATE ingresos SET 
														ingr_fe_comentario = CONCAT(IFNULL(ingr_fe_comentario, ''), ' | REEMBOLSO: $error_reembolso_db') 
														WHERE ingr_id = '$last_id'";
						mysql_query($sql_warning);
						
						error_log("Error Reembolso - Ingreso ID: $last_id - Error: $warning_mensaje");
				}
			}

			// GENERAR PDF DEL VOUCHER (siempre se genera)
			$plantilla = str_replace("[LOGO]", '<svg width="450" height="250" viewBox="0 0 760 400" xmlns="http://www.w3.org/2000/svg">
<path d="M0 0 C9.73473859 8.71904881 17.98619899 18.71360891 23.8581543 30.45751953 C25.75214568 31.01659629 25.75214568 31.01659629 28.06518555 31.31689453 C29.39501465 31.53104004 29.39501465 31.53104004 30.75170898 31.74951172 C31.71496094 31.9006543 32.67821289 32.05179687 33.6706543 32.20751953 C62.92987206 37.20624694 89.87603986 48.23514741 111.8581543 68.45751953 C112.74245117 69.25931641 113.62674805 70.06111328 114.5378418 70.88720703 C128.11348784 83.78618536 138.88722813 100.72653742 141.8581543 119.45751953 C141.89717023 121.12372946 141.90927344 122.791637 141.8581543 124.45751953 C142.47819336 124.29638672 143.09823242 124.13525391 143.73706055 123.96923828 C162.60496715 120.87575172 185.36166336 123.27889373 201.60424805 133.70751953 C209.90728674 140.15424628 213.56047324 149.07725435 215.6081543 159.14501953 C216.63853667 172.79758602 214.69187178 183.9805805 205.7331543 194.58251953 C200.11771319 200.60568243 193.61967402 204.71815963 185.8581543 207.45751953 C184.8681543 207.45751953 183.8781543 207.45751953 182.8581543 207.45751953 C185.24143953 211.53331026 187.62590939 215.60840465 190.01147461 219.68286133 C190.82267241 221.06873862 191.63354747 222.45480487 192.4440918 223.84106445 C193.6099075 225.83480932 194.77688615 227.8278694 195.9440918 229.82080078 C196.30562347 230.43965652 196.66715515 231.05851227 197.03964233 231.69612122 C198.58544127 234.33321366 200.16185522 236.91307091 201.8581543 239.45751953 C205.8046825 232.00422253 208.85011456 224.21326479 211.9519043 216.38330078 C212.54484711 214.89464737 213.13815741 213.40614028 213.73181152 211.91777039 C215.29517981 207.99609043 216.85471796 204.07290019 218.41308594 200.14923096 C223.25135194 187.96888692 228.10197358 175.7935318 232.9855957 163.63129425 C233.56587698 162.18565979 234.14563226 160.73981408 234.72485352 159.29375458 C236.36712873 155.1943904 238.01880744 151.09908873 239.68598938 147.00978661 C240.42661635 145.1877015 241.15835383 143.36201087 241.88977051 141.53620911 C247.36944687 128.1751178 247.36944687 128.1751178 252.6784668 124.03955078 C256.01564925 123.14843934 259.30578641 123.12929766 262.7331543 123.14501953 C263.42795898 123.12890625 264.12276367 123.11279297 264.83862305 123.09619141 C269.65763217 123.09165373 272.92342209 123.65388882 276.8581543 126.45751953 C281.67663664 132.478657 284.6675457 139.14483868 287.45581055 146.27392578 C288.09146439 147.86089264 288.09146439 147.86089264 288.73995972 149.47991943 C290.14324093 152.98949802 291.5320037 156.50462913 292.9206543 160.02001953 C293.9080024 162.499191 294.89615888 164.97802068 295.88574219 167.45629883 C297.47527929 171.43838577 299.06398281 175.42079217 300.64823914 179.40498352 C304.65865078 189.48568704 308.72023529 199.54499593 312.80320168 209.59651375 C314.81444114 214.54919362 316.82176125 219.50346394 318.8295927 224.45752621 C320.47533219 228.51725391 322.12323509 232.57607773 323.77566528 236.63308716 C325.34006743 240.47465611 326.89776832 244.31887678 328.45059586 248.16513824 C329.02714321 249.58975816 329.60613897 251.01338992 330.18770981 252.43596649 C330.98523612 254.3876463 331.77414721 256.34259561 332.56103516 258.29858398 C333.00684433 259.39685013 333.4526535 260.49511627 333.91197205 261.62666321 C334.8581543 264.45751953 334.8581543 264.45751953 334.8581543 268.45751953 C325.9481543 268.45751953 317.0381543 268.45751953 307.8581543 268.45751953 C303.8981543 257.56751953 299.9381543 246.67751953 295.8581543 235.45751953 C274.7381543 235.45751953 253.6181543 235.45751953 231.8581543 235.45751953 C227.5681543 246.67751953 223.2781543 257.89751953 218.8581543 269.45751953 C193.41970672 271.29092116 193.41970672 271.29092116 187.80932617 268.40283203 C184.44468278 265.16178778 182.97268907 260.75522227 181.24584961 256.49951172 C178.49156172 250.46178704 174.85363488 244.88580945 171.40631104 239.22460938 C169.72578205 236.45156667 168.0744449 233.6618968 166.42745972 230.86883545 C165.6490251 229.54979157 164.86776508 228.23241055 164.08352661 226.91680908 C162.38306374 224.06308904 160.7199741 221.20563988 159.12182617 218.29248047 C158.6171582 217.38530273 158.11249023 216.478125 157.5925293 215.54345703 C157.17712891 214.7679248 156.76172852 213.99239258 156.33374023 213.19335938 C154.68006973 210.88448553 154.68006973 210.88448553 149.8581543 210.45751953 C149.8581543 229.92751953 149.8581543 249.39751953 149.8581543 269.45751953 C140.4531543 269.95251953 140.4531543 269.95251953 130.8581543 270.45751953 C126.77695678 274.00427452 124.67790356 276.16876182 121.8581543 280.45751953 C120.69481573 281.8530211 119.50850498 283.22982797 118.2956543 284.58251953 C117.7284668 285.22705078 117.1612793 285.87158203 116.5769043 286.53564453 C90.63116492 315.54769857 48.96144867 330.27867927 10.8581543 332.45751953 C-37.56917958 334.37357945 -82.07420744 322.47413491 -118.8684082 290.05517578 C-126.35075375 282.91725566 -133.77615065 274.12694913 -138.46801758 264.82958984 C-139.93492122 262.29159512 -139.93492122 262.29159512 -142.49169922 261.52612305 C-143.40878662 261.38102295 -144.32587402 261.23592285 -145.27075195 261.08642578 C-146.81001343 260.8189856 -146.81001343 260.8189856 -148.38037109 260.54614258 C-149.47728271 260.37252197 -150.57419434 260.19890137 -151.7043457 260.02001953 C-168.86763804 256.93124098 -183.8487852 251.80674886 -199.1418457 243.45751953 C-200.21176758 242.88001953 -201.28168945 242.30251953 -202.3840332 241.70751953 C-229.33473381 226.58712646 -252.14519987 202.91700942 -261.2043457 172.94189453 C-264.27353489 160.48806918 -264.30395775 147.92096428 -264.30981445 135.17236328 C-264.31303788 133.50484693 -264.31640271 131.83733086 -264.31990051 130.16981506 C-264.32585761 126.68603868 -264.32774407 123.20228697 -264.32714844 119.71850586 C-264.32704727 115.30714046 -264.34066812 110.89593763 -264.35792065 106.48461056 C-264.36917495 103.04140024 -264.37106355 99.59822749 -264.37053871 96.15500069 C-264.37169994 94.53150437 -264.37601378 92.90800645 -264.38387108 91.28452873 C-264.53318643 57.23586152 -256.7158022 28.90466708 -232.3918457 4.20751953 C-225.50910184 -2.5792723 -218.33145819 -8.41864235 -210.1418457 -13.54248047 C-209.53969238 -13.92452637 -208.93753906 -14.30657227 -208.31713867 -14.70019531 C-146.10706895 -53.57538667 -55.47601279 -48.43131806 0 0 Z M-233.1418457 11.45751953 C-257.71124112 39.22686797 -260.36311343 69.26287052 -260.2668457 104.95751953 C-260.26463013 106.03967743 -260.26241455 107.12183533 -260.26013184 108.23678589 C-260.24234619 115.64389166 -260.20064925 123.05063164 -260.1418457 130.45751953 C-260.13560181 131.30582336 -260.12935791 132.1541272 -260.1229248 133.02813721 C-260.07307656 155.09056696 -260.07307656 155.09056696 -255.1418457 176.45751953 C-254.79250977 177.48876953 -254.44317383 178.52001953 -254.08325195 179.58251953 C-249.3444129 192.17569737 -241.45952082 202.8767207 -232.1418457 212.45751953 C-231.34520508 213.32763672 -230.54856445 214.19775391 -229.7277832 215.09423828 C-207.4842968 238.64814155 -174.19511221 253.98447937 -142.1418457 257.45751953 C-140.14217174 257.49363102 -138.14124006 257.50673539 -136.1418457 257.45751953 C-135.89692383 258.31603516 -135.65200195 259.17455078 -135.3996582 260.05908203 C-125.91382086 285.68851833 -96.64649375 303.59566739 -73.1418457 314.45751953 C-30.4335248 333.28946487 19.34816381 332.8797237 62.76074219 316.63842773 C77.94949604 310.68609802 91.31336655 302.85225593 103.8581543 292.45751953 C104.67928711 291.78978516 105.50041992 291.12205078 106.34643555 290.43408203 C113.55501981 284.33355304 119.2459206 277.01109193 124.8581543 269.45751953 C124.1981543 269.45751953 123.5381543 269.45751953 122.8581543 269.45751953 C122.8581543 221.60751953 122.8581543 173.75751953 122.8581543 124.45751953 C127.8081543 124.45751953 132.7581543 124.45751953 137.8581543 124.45751953 C134.49139972 99.88021112 119.82934653 79.37718003 100.45922852 64.46704102 C81.40976609 50.34222497 57.74234179 39.25699152 33.9128418 36.91064453 C22.04602333 35.69643748 22.04602333 35.69643748 18.49487305 31.99267578 C16.7956543 28.95751953 16.7956543 28.95751953 15.3425293 25.71533203 C4.2170945 1.2979304 -27.30368854 -16.24082009 -51.30102539 -25.36328125 C-67.4554701 -31.19311247 -84.9452649 -35.0036842 -102.1418457 -35.54248047 C-104.02516602 -35.61982422 -104.02516602 -35.61982422 -105.9465332 -35.69873047 C-151.90933634 -36.98775566 -200.94247456 -22.634735 -233.1418457 11.45751953 Z M149.8581543 148.45751953 C149.8581543 160.99751953 149.8581543 173.53751953 149.8581543 186.45751953 C167.68276437 187.15193347 167.68276437 187.15193347 183.8581543 180.58251953 C188.03218051 176.14761668 189.54103866 171.48271669 189.8581543 165.45751953 C188.89944431 159.8970016 187.29437684 156.94986494 182.8581543 153.45751953 C171.95700787 147.48401309 162.84574526 148.45751953 149.8581543 148.45751953 Z M259.91381836 161.34570312 C257.21226498 165.43525174 255.63739945 169.65604759 253.9675293 174.24658203 C253.61805237 175.1868222 253.26857544 176.12706238 252.9085083 177.09579468 C252.17468863 179.07604708 251.44542623 181.05799353 250.72045898 183.04150391 C249.6080572 186.08278499 248.48039931 189.11807289 247.3503418 192.15283203 C246.63850093 194.07958186 245.92753294 196.00665444 245.2175293 197.93408203 C244.88009705 198.84348541 244.54266479 199.75288879 244.19500732 200.68984985 C243.73387543 201.95664078 243.73387543 201.95664078 243.26342773 203.24902344 C242.85516754 204.36224472 242.85516754 204.36224472 242.43865967 205.49795532 C241.71613403 207.59630212 241.71613403 207.59630212 241.8581543 210.45751953 C256.3781543 210.45751953 270.8981543 210.45751953 285.8581543 210.45751953 C285.15585093 206.94600267 284.55568017 204.40325003 283.23168945 201.20556641 C282.90109528 200.40123169 282.5705011 199.59689697 282.22988892 198.76818848 C281.87260712 197.91285522 281.51532532 197.05752197 281.1472168 196.17626953 C280.778414 195.28516479 280.40961121 194.39406006 280.02963257 193.47595215 C279.25009325 191.59567244 278.46827266 189.71633712 277.68432617 187.83789062 C276.48845425 184.97022865 275.30171083 182.098913 274.1159668 179.22705078 C273.35609468 177.39739745 272.59569548 175.56796291 271.8347168 173.73876953 C271.48148346 172.88343628 271.12825012 172.02810303 270.76431274 171.14685059 C268.28623676 165.04353853 268.28623676 165.04353853 264.8581543 159.45751953 C261.76513406 159.10918363 261.76513406 159.10918363 259.91381836 161.34570312 Z " fill="#0B2067" transform="translate(275.141845703125,48.54248046875)"/>
<path d="M0 0 C4.13061608 3.53110721 8.15695932 7.15695932 12 11 C12 12.32 12 13.64 12 15 C11.40799805 15.08975098 10.81599609 15.17950195 10.20605469 15.27197266 C-27.65716376 21.13019316 -64.19663193 34.9139984 -89.0234375 65.4921875 C-90.79593384 67.93839286 -92.41325267 70.4295447 -94 73 C-94.65613281 74.02351563 -95.31226563 75.04703125 -95.98828125 76.1015625 C-99.92638145 82.55381706 -102.95667955 88.95713756 -105.375 96.125 C-105.73283569 97.17977539 -105.73283569 97.17977539 -106.09790039 98.25585938 C-111.1042085 114.01840228 -110.90974741 130.64621115 -112 147 C-83.95 147 -55.9 147 -27 147 C-26.34 144.69 -25.68 142.38 -25 140 C-16.59714527 125.15187867 -4.22871185 113.61435592 11 106 C-16.72 106 -44.44 106 -73 106 C-69.9521139 90.7605695 -57.3289406 77.87008241 -44.91210938 69.25439453 C-11.7065376 47.50906964 33.0023796 44.87091327 71.21875 51.70703125 C94.93580639 56.9010666 114.64991845 69.90431257 127.9375 90.25 C128.31543701 90.82838623 128.69337402 91.40677246 129.08276367 92.00268555 C131.41177517 95.70539386 132.57603293 98.72809878 134 103 C140.6 103 147.2 103 154 103 C154 150.85 154 198.7 154 248 C144.76 248 135.52 248 126 248 C125.67 243.71 125.34 239.42 125 235 C124.071875 236.258125 123.14375 237.51625 122.1875 238.8125 C107.71175651 257.26254582 89.35668117 266.98682369 67 273 C65.79730469 273.32355469 64.59460938 273.64710938 63.35546875 273.98046875 C29.20245915 281.47232359 -8.11000593 274.88415114 -38 257 C-41.55116346 254.54481556 -44.78242913 251.8730296 -48 249 C-48.85980469 248.28199219 -49.71960938 247.56398438 -50.60546875 246.82421875 C-55.33716072 242.81145053 -55.33716072 242.81145053 -57 241 C-57 240.01 -57 239.02 -57 238 C-49.36976387 236.01229143 -41.66509949 234.47268343 -33.93164062 232.94335938 C-28.4086456 231.82057191 -23.12891528 230.51933984 -17.83764648 228.56616211 C-16 228 -16 228 -13 228 C-13.65226563 227.06800781 -14.30453125 226.13601563 -14.9765625 225.17578125 C-29 204.83562506 -29 204.83562506 -29 197 C-30.21171875 197.26554688 -31.4234375 197.53109375 -32.671875 197.8046875 C-69.58322426 205.6820869 -110.15694463 207.8982989 -144 189 C-145.02351563 188.43152344 -146.04703125 187.86304688 -147.1015625 187.27734375 C-164.25223545 177.05940761 -177.20853065 158.65609696 -182.3125 139.4375 C-183.66565442 133.72239168 -184.33961526 128.13835312 -184.59375 122.27734375 C-184.63248734 121.46147797 -184.67122467 120.64561218 -184.71113586 119.80502319 C-185.12935371 110.24775423 -185.19922131 100.69012815 -185.1875 91.125 C-185.18689575 90.20689545 -185.1862915 89.28879089 -185.18566895 88.34286499 C-185.13365149 59.1892991 -183.69639623 30.64329649 -163 8 C-162.48308594 7.40316406 -161.96617187 6.80632812 -161.43359375 6.19140625 C-122.63890314 -35.90909759 -42.97079051 -33.6884596 0 0 Z M-162.64257812 14.34082031 C-166.54209361 19.1072065 -169.29589708 24.49746498 -172 30 C-172.49242187 30.95261719 -172.98484375 31.90523437 -173.4921875 32.88671875 C-183.11361429 53.29666847 -181.30539739 79.00164125 -181 101 C-180.98743164 102.14130371 -180.97486328 103.28260742 -180.96191406 104.45849609 C-180.62027471 129.60384957 -177.13834082 150.62935428 -160 170 C-159.51015625 170.63164062 -159.0203125 171.26328125 -158.515625 171.9140625 C-145.91554357 187.98270568 -122.36696238 195.38388037 -103 198 C-98.98114286 198.35935156 -94.96959895 198.55089442 -90.9375 198.6875 C-89.85331787 198.72584961 -88.76913574 198.76419922 -87.65209961 198.80371094 C-68.2913546 199.37955876 -49.52860817 197.87078172 -31 192 C-31.0407666 191.4423999 -31.0815332 190.8847998 -31.12353516 190.31030273 C-32.07474804 176.65240095 -31.69463696 164.47318482 -29 151 C-57.71 151 -86.42 151 -116 151 C-114.58638396 111.41875099 -110.8060455 80.77625182 -83.0625 51.625 C-81.73409785 50.38839799 -80.37761457 49.18153074 -79 48 C-77.69289062 46.87464844 -77.69289062 46.87464844 -76.359375 45.7265625 C-52.72818757 26.51864861 -24.57644044 16.66674657 5 11 C-15.40264739 -9.98247396 -46.49194883 -19.60927844 -75.19482422 -20.34912109 C-107.99689438 -20.65173375 -140.31976134 -10.69284057 -162.64257812 14.34082031 Z M-3 57 C-4.09610596 57.26538574 -4.09610596 57.26538574 -5.21435547 57.53613281 C-27.11724781 63.03918105 -49.50377015 73.339317 -62.125 92.9375 C-63.88064267 95.90287654 -65.46793022 98.91509076 -67 102 C-48.32355512 102.07778251 -29.65267199 101.87389332 -10.97827148 101.62768555 C-6.7343301 101.57198942 -2.49033997 101.52204436 1.75368595 101.47328281 C5.05745161 101.43432058 8.36115464 101.39125505 11.66485405 101.34705925 C13.2312576 101.32680235 14.79768161 101.30806377 16.36412239 101.29092598 C18.53978676 101.26673965 20.71529868 101.23673164 22.89086914 101.20532227 C24.12277481 101.18977798 25.35468048 101.1742337 26.62391663 101.15821838 C29.83125924 101.00790795 32.84484825 100.57272926 36 100 C49.63470507 99.25208566 63.89385008 98.72849302 77 103 C78.08796875 103.35191406 79.1759375 103.70382813 80.296875 104.06640625 C92.83866169 108.52668954 103.02057942 115.33676284 113 124 C111.5969473 127.27856192 109.98851532 129.1141327 107.25 131.375 C103.6385246 134.4263013 100.30090396 137.61657344 97 141 C92.6177133 139.02262673 88.84277262 136.15069465 84.953125 133.36523438 C78.53124034 128.64909876 78.53124034 128.64909876 71 127 C70.90295776 127.82854492 70.90295776 127.82854492 70.80395508 128.67382812 C69.31929446 140.97930705 67.11717692 152.30119241 63 164 C62.01 166.97 61.02 169.94 60 173 C80.46 173 100.92 173 122 173 C120.458089 211.54777509 120.458089 211.54777509 110 225 C109.37996094 225.80050781 108.75992188 226.60101563 108.12109375 227.42578125 C96.73286717 241.11465966 79.68969009 248.86097642 62.15625 250.82128906 C36.86597582 252.91735798 14.26100116 248.75505534 -6 233 C-6.99 232.01 -6.99 232.01 -8 231 C-13.4728399 231.42756562 -18.75835464 233.31971917 -24 234.875 C-32.27980603 237.29268313 -40.52375713 239.37598036 -49 241 C-35.9212492 258.73863826 -7.17693481 267.87877259 13.75976562 271.22631836 C45.65667294 275.49358884 79.35045723 269.85456461 105.3125 250.125 C113.70482324 243.06719373 124.24314291 233.14569816 126.63670158 222.04792213 C126.85430162 218.86231914 126.86762939 215.74418414 126.79467773 212.55200195 C126.79621101 210.7646891 126.79621101 210.7646891 126.79777527 208.94126892 C126.79376635 205.7097444 126.75894771 202.48041062 126.70975757 199.24931741 C126.66565594 195.85952247 126.66185983 192.46967319 126.65357971 189.07963562 C126.63192206 182.67638853 126.57461665 176.27398951 126.50413328 169.87111396 C126.42557889 162.57486658 126.38732584 155.27857624 126.35226524 147.98201048 C126.27932261 132.98753649 126.15639534 117.99385352 126 103 C126.99 103 127.98 103 129 103 C121.86758878 83.92335947 105.89981862 70.53465192 88.04394531 61.91650391 C71.82394811 54.64346646 54.94543404 53.61329747 37.4375 53.6875 C36.1054805 53.69023422 36.1054805 53.69023422 34.74655151 53.69302368 C21.98464001 53.73721243 9.45901373 53.94941506 -3 57 Z M6 140 C4.02 142.97 4.02 142.97 2 146 C10.58 146 19.16 146 28 146 C21.01519365 163.46201587 11.53674893 175.62810941 -5 185 C-3.29009317 195.25944099 0.98487403 205.99201028 9 213 C10.31101698 213.70954683 11.6409153 214.38753694 13 215 C24.88288005 207.2377961 40.50123632 196.99752736 47 184 C47.07033769 182.12620395 47.0843022 180.24998898 47.0625 178.375 C47.05347656 177.37210938 47.04445313 176.36921875 47.03515625 175.3359375 C47.02355469 174.56507813 47.01195312 173.79421875 47 173 C49.31 173 51.62 173 54 173 C62.06008176 157.89866704 67.68265642 142.4077386 67 125 C46.46027363 117.92709147 20.74793429 123.97106474 6 140 Z M-1 151 C-1.65256456 152.70577342 -2.29673041 154.41476089 -2.9375 156.125 C-3.47697266 157.55199219 -3.47697266 157.55199219 -4.02734375 159.0078125 C-5.76046709 164.33942888 -6.11949757 169.28923829 -6.0625 174.875 C-6.05347656 176.02742188 -6.04445312 177.17984375 -6.03515625 178.3671875 C-6.02355469 179.23601562 -6.01195312 180.10484375 -6 181 C6.32506981 173.40847149 15.04991108 164.64110375 22 152 C22 151.34 22 150.68 22 150 C19.47909222 149.9727633 16.95848543 149.95304793 14.4375 149.9375 C13.73302734 149.92912109 13.02855469 149.92074219 12.30273438 149.91210938 C7.75975202 149.89117397 3.48215108 150.25199318 -1 151 Z M46 192 C45.47535156 192.64324219 44.95070313 193.28648437 44.41015625 193.94921875 C36.86785557 202.74647794 27.45526807 211.77236596 17 217 C17.70191733 220.17884792 17.70191733 220.17884792 20.44921875 221.5 C35.3259688 228.39103983 49.96151385 231.66280094 65.84765625 226.3359375 C72.91010463 223.34292964 78.89602649 218.70444098 84 213 C84.680625 212.278125 85.36125 211.55625 86.0625 210.8125 C89.2497686 206.18581977 92 200.68812548 92 195 C77.15 195 62.3 195 47 195 C46.67 194.01 46.34 193.02 46 192 Z " fill="#0B2067" transform="translate(239,69)"/>
<path d="M0 0 C1.44502042 1.34613877 2.88543215 2.69734398 4.3125 4.0625 C0.71647834 8.53322963 -3.14110223 12.62412178 -7.3125 16.5625 C-8.23289062 17.4390625 -9.15328125 18.315625 -10.1015625 19.21875 C-12.6875 21.0625 -12.6875 21.0625 -14.67578125 21.109375 C-17.47978333 19.65020498 -19.78728469 17.79316584 -22.25 15.8125 C-35.80356316 5.33985679 -51.65624784 1.52985951 -68.6875 3.0625 C-83.57227429 5.33342363 -98.12699207 11.04947831 -107.43359375 23.375 C-109.04063579 25.87573374 -110.3931132 28.38717165 -111.6875 31.0625 C-112.30818359 32.31546875 -112.30818359 32.31546875 -112.94140625 33.59375 C-117.85506941 45.50981119 -117.93531098 62.54174846 -113 74.46875 C-112.3503125 75.75265625 -112.3503125 75.75265625 -111.6875 77.0625 C-110.9759375 78.47015625 -110.9759375 78.47015625 -110.25 79.90625 C-102.8589825 92.84053063 -90.11717026 100.75017755 -76.0625 104.9375 C-61.15477964 108.20897083 -48.10329254 106.53574532 -34.9375 98.875 C-25.56752614 92.57933996 -22.63640106 85.92197791 -18.6875 75.0625 C-33.5375 74.7325 -48.3875 74.4025 -63.6875 74.0625 C-63.6875 66.8025 -63.6875 59.5425 -63.6875 52.0625 C-38.6075 52.0625 -13.5275 52.0625 12.3125 52.0625 C12.3125 90.69557985 12.3125 90.69557985 -2.6875 108.0625 C-3.22117187 108.69285156 -3.75484375 109.32320312 -4.3046875 109.97265625 C-15.89622514 122.63107671 -33.91127125 129.13939597 -50.74804688 130.26757812 C-53.96010331 130.36551886 -57.16151347 130.39170087 -60.375 130.375 C-61.51622314 130.36936035 -62.65744629 130.3637207 -63.83325195 130.35791016 C-86.34212201 130.03596781 -105.96994478 123.53788948 -122.5 107.75 C-127.42252974 102.62206973 -131.23515082 97.26041821 -134.6875 91.0625 C-135.14769531 90.26328125 -135.60789062 89.4640625 -136.08203125 88.640625 C-145.45119271 71.31758326 -146.6400722 51.81350022 -141.90625 32.94921875 C-138.24106378 20.76879386 -131.61426384 11.00399447 -122.6875 2.0625 C-121.92695312 1.29035156 -121.16640625 0.51820313 -120.3828125 -0.27734375 C-88.62443666 -30.73414681 -32.05311786 -28.71706902 0 0 Z " fill="#0B2067" transform="translate(733.6875,189.9375)"/>
<path d="M0 0 C9.73473859 8.71904881 17.98619899 18.71360891 23.8581543 30.45751953 C25.75214568 31.01659629 25.75214568 31.01659629 28.06518555 31.31689453 C29.39501465 31.53104004 29.39501465 31.53104004 30.75170898 31.74951172 C31.71496094 31.9006543 32.67821289 32.05179687 33.6706543 32.20751953 C62.92987206 37.20624694 89.87603986 48.23514741 111.8581543 68.45751953 C112.74245117 69.25931641 113.62674805 70.06111328 114.5378418 70.88720703 C128.11348784 83.78618536 138.88722813 100.72653742 141.8581543 119.45751953 C141.89717023 121.12372946 141.90927344 122.791637 141.8581543 124.45751953 C140.5381543 124.45751953 139.2181543 124.45751953 137.8581543 124.45751953 C137.57842773 123.39275391 137.29870117 122.32798828 137.01049805 121.23095703 C133.06012174 106.71765454 127.38625439 94.27809483 117.8581543 82.45751953 C116.86041992 81.21228516 116.86041992 81.21228516 115.8425293 79.94189453 C97.19674032 57.98129863 65.19028979 41.05297138 36.44067383 38.21923828 C35.64129395 38.16380859 34.84191406 38.10837891 34.01831055 38.05126953 C22.65403042 37.16554471 22.65403042 37.16554471 19.30102539 33.9765625 C16.87193624 30.77446318 15.05718716 27.30732404 13.21362305 23.74658203 C9.68284794 16.94623324 4.26763141 11.78952472 -1.1418457 6.45751953 C-1.84438477 5.74466797 -2.54692383 5.03181641 -3.27075195 4.29736328 C-28.86539343 -20.09518153 -67.18063872 -33.44709017 -102.1418457 -34.54248047 C-104.02516602 -34.61982422 -104.02516602 -34.61982422 -105.9465332 -34.69873047 C-150.21088117 -35.94012254 -195.74910707 -22.72219751 -228.51464844 7.78076172 C-234.08321875 13.16654493 -238.71740514 19.11501549 -243.1418457 25.45751953 C-243.71547852 26.27607422 -244.28911133 27.09462891 -244.88012695 27.93798828 C-259.78635404 50.93286573 -259.3379008 78.61133923 -259.2668457 104.95751953 C-259.26463013 106.03967743 -259.26241455 107.12183533 -259.26013184 108.23678589 C-259.24234619 115.64389166 -259.20064925 123.05063164 -259.1418457 130.45751953 C-259.13560181 131.30582336 -259.12935791 132.1541272 -259.1229248 133.02813721 C-259.07307656 155.09056696 -259.07307656 155.09056696 -254.1418457 176.45751953 C-253.79250977 177.48876953 -253.44317383 178.52001953 -253.08325195 179.58251953 C-248.34345884 192.17823273 -240.45745826 202.87119687 -231.1418457 212.45751953 C-230.41868164 213.25416016 -229.69551758 214.05080078 -228.95043945 214.87158203 C-208.94812374 236.19384751 -178.455934 251.11998684 -149.62597656 254.81567383 C-137.25714281 256.40650468 -137.25714281 256.40650468 -133.4309082 260.01220703 C-131.69806432 263.09368959 -130.42866131 266.16899077 -129.1418457 269.45751953 C-108.80087523 299.99235567 -72.47625921 317.27190526 -37.55981445 324.45751953 C5.51707621 332.72240529 53.82269097 325.70610593 90.8581543 301.45751953 C95.36471996 298.33283815 99.62893389 294.94487393 103.8581543 291.45751953 C104.60581055 290.86326172 105.3534668 290.26900391 106.1237793 289.65673828 C111.00865363 285.62767855 114.87154304 280.97826442 118.7800293 276.00830078 C120.70709758 273.64293313 122.55463855 271.45916326 124.8581543 269.45751953 C128.1081543 269.20751953 128.1081543 269.20751953 130.8581543 269.45751953 C105.88638692 304.83418998 68.69996198 323.53505247 26.78979492 330.81689453 C-16.97248645 337.85236178 -65.60006856 329.36055143 -102.1418457 303.45751953 C-116.37673653 293.06233069 -130.39238741 280.83218246 -138.46801758 264.82958984 C-139.93492122 262.29159512 -139.93492122 262.29159512 -142.49169922 261.52612305 C-143.40878662 261.38102295 -144.32587402 261.23592285 -145.27075195 261.08642578 C-146.81001343 260.8189856 -146.81001343 260.8189856 -148.38037109 260.54614258 C-149.47728271 260.37252197 -150.57419434 260.19890137 -151.7043457 260.02001953 C-168.86763804 256.93124098 -183.8487852 251.80674886 -199.1418457 243.45751953 C-200.21176758 242.88001953 -201.28168945 242.30251953 -202.3840332 241.70751953 C-229.33473381 226.58712646 -252.14519987 202.91700942 -261.2043457 172.94189453 C-264.27353489 160.48806918 -264.30395775 147.92096428 -264.30981445 135.17236328 C-264.31303788 133.50484693 -264.31640271 131.83733086 -264.31990051 130.16981506 C-264.32585761 126.68603868 -264.32774407 123.20228697 -264.32714844 119.71850586 C-264.32704727 115.30714046 -264.34066812 110.89593763 -264.35792065 106.48461056 C-264.36917495 103.04140024 -264.37106355 99.59822749 -264.37053871 96.15500069 C-264.37169994 94.53150437 -264.37601378 92.90800645 -264.38387108 91.28452873 C-264.53318643 57.23586152 -256.7158022 28.90466708 -232.3918457 4.20751953 C-225.50910184 -2.5792723 -218.33145819 -8.41864235 -210.1418457 -13.54248047 C-209.53969238 -13.92452637 -208.93753906 -14.30657227 -208.31713867 -14.70019531 C-146.10706895 -53.57538667 -55.47601279 -48.43131806 0 0 Z " fill="#6B8CC7" transform="translate(275.141845703125,48.54248046875)"/>
<path d="M0 0 C9.24 0 18.48 0 28 0 C28 47.85 28 95.7 28 145 C18.76 145 9.52 145 0 145 C0 97.15 0 49.3 0 0 Z " fill="#0B2067" transform="translate(365,172)"/>
<path d="M0 0 C4.13061608 3.53110721 8.15695932 7.15695932 12 11 C12 12.32 12 13.64 12 15 C11.40799805 15.08975098 10.81599609 15.17950195 10.20605469 15.27197266 C-27.65716376 21.13019316 -64.19663193 34.9139984 -89.0234375 65.4921875 C-90.79593384 67.93839286 -92.41325267 70.4295447 -94 73 C-94.65613281 74.02351563 -95.31226563 75.04703125 -95.98828125 76.1015625 C-99.92638145 82.55381706 -102.95667955 88.95713756 -105.375 96.125 C-105.73283569 97.17977539 -105.73283569 97.17977539 -106.09790039 98.25585938 C-111.1042085 114.01840228 -110.90974741 130.64621115 -112 147 C-83.95 147 -55.9 147 -27 147 C-27.33 148.32 -27.66 149.64 -28 151 C-57.04 151 -86.08 151 -116 151 C-116 110.90749543 -111.63872182 80.43987866 -83.5625 51.125 C-59.00909487 28.05439376 -28.43117572 16.85928392 4 10 C-22.71089684 -12.32006448 -57.08500508 -21.68612365 -91.59326172 -18.78173828 C-118.90327884 -15.70401997 -144.90138508 -5.4491931 -163 16 C-166.12698077 20.42653893 -168.59339642 25.15390076 -171 30 C-171.49371094 30.95390625 -171.98742187 31.9078125 -172.49609375 32.890625 C-182.10629907 53.30874669 -180.30545939 78.99717521 -180 101 C-179.98743164 102.14130371 -179.97486328 103.28260742 -179.96191406 104.45849609 C-179.61972087 129.64461366 -176.11223261 150.57631113 -159 170 C-158.21625 171.051875 -157.4325 172.10375 -156.625 173.1875 C-141.16702168 189.74961963 -115.8638189 196.61075003 -94.02172852 197.58300781 C-93.00393311 197.61749023 -91.9861377 197.65197266 -90.9375 197.6875 C-89.85331787 197.72584961 -88.76913574 197.76419922 -87.65209961 197.80371094 C-71.73570063 198.27711337 -56.62863361 196.94240416 -41 194 C-38.79173795 193.60376917 -36.58341657 193.20786869 -34.375 192.8125 C-32.91639779 192.54311851 -31.45793646 192.27296167 -30 192 C-29.67 193.32 -29.34 194.64 -29 196 C-57.6196745 207.12987342 -101.50571603 208.18818325 -130.02001953 196.14697266 C-134.80100895 193.98401035 -139.41991886 191.55756288 -144 189 C-145.02351563 188.43152344 -146.04703125 187.86304688 -147.1015625 187.27734375 C-164.25223545 177.05940761 -177.20853065 158.65609696 -182.3125 139.4375 C-183.66565442 133.72239168 -184.33961526 128.13835312 -184.59375 122.27734375 C-184.63248734 121.46147797 -184.67122467 120.64561218 -184.71113586 119.80502319 C-185.12935371 110.24775423 -185.19922131 100.69012815 -185.1875 91.125 C-185.18689575 90.20689545 -185.1862915 89.28879089 -185.18566895 88.34286499 C-185.13365149 59.1892991 -183.69639623 30.64329649 -163 8 C-162.48308594 7.40316406 -161.96617187 6.80632812 -161.43359375 6.19140625 C-122.63890314 -35.90909759 -42.97079051 -33.6884596 0 0 Z " fill="#6A8BC6" transform="translate(239,69)"/>
<path d="M0 0 C7.98154778 7.6064021 15.6841753 17.74144287 20 28 C20 28.99 20 29.98 20 31 C17.75390625 30.921875 17.75390625 30.921875 15 30 C13.43298657 27.75023791 12.19176706 25.6631233 10.9375 23.25 C0.37729959 4.41203069 -16.66009131 -8.29670822 -37.37109375 -14.359375 C-46.82864607 -16.88333629 -56.06893202 -18.14742672 -65.828125 -18.203125 C-66.6510051 -18.21013931 -67.47388519 -18.21715363 -68.32170105 -18.22438049 C-70.9645235 -18.24180221 -73.60712223 -18.24823291 -76.25 -18.25 C-77.15050079 -18.25067474 -78.05100159 -18.25134949 -78.97879028 -18.25204468 C-91.84337797 -18.23669263 -104.43824972 -18.07573994 -117 -15 C-117.7307373 -14.82307617 -118.46147461 -14.64615234 -119.21435547 -14.46386719 C-140.62276705 -9.08505603 -162.76717466 0.68159791 -175.0625 19.9375 C-176.05201638 21.61895204 -177.03294108 23.30553234 -178 25 C-178.66 25.99 -179.32 26.98 -180 28 C-149.64 28 -119.28 28 -88 28 C-94.24735824 32.16490549 -100.09444793 32.32655332 -107.37109375 32.4140625 C-108.89987152 32.4431797 -108.89987152 32.4431797 -110.45953369 32.47288513 C-113.7854374 32.53325866 -117.11136174 32.57953679 -120.4375 32.625 C-122.72827893 32.66288251 -125.01905044 32.70121594 -127.30981445 32.73999023 C-147.20632098 33.05878143 -167.10158016 33.11396575 -187 33 C-184.47241557 18.48956423 -172.08752086 5.86630649 -160.6171875 -2.5625 C-118.14336 -32.03388718 -39.94267175 -35.74777928 0 0 Z " fill="#6A8BC6" transform="translate(353,142)"/>
<path d="M0 0 C0.33 0 0.66 0 1 0 C1.57776895 4.47770933 1.88257721 7.45734404 -0.39453125 11.4375 C-2.48089496 14.08900907 -4.68501787 16.54538403 -7 19 C-7.83273437 19.92296875 -8.66546875 20.8459375 -9.5234375 21.796875 C-22.72526812 35.81172158 -39.70871842 43.08027599 -58 48 C-59.20269531 48.32355469 -60.40539062 48.64710938 -61.64453125 48.98046875 C-95.79754085 56.47232359 -133.11000593 49.88415114 -163 32 C-166.55116346 29.54481556 -169.78242913 26.8730296 -173 24 C-173.85980469 23.28199219 -174.71960938 22.56398438 -175.60546875 21.82421875 C-180.33716072 17.81145053 -180.33716072 17.81145053 -182 16 C-182 15.01 -182 14.02 -182 13 C-176.4864222 11.56224885 -170.95720918 10.364174 -165.37011719 9.24829102 C-152.5384151 6.69190333 -152.5384151 6.69190333 -140.171875 2.50390625 C-138 2 -138 2 -135.703125 2.90234375 C-135.14109375 3.26457031 -134.5790625 3.62679688 -134 4 C-134 4.66 -134 5.32 -134 6 C-145.33206929 10.61531418 -156.91049325 13.11753595 -168.88378906 15.50683594 C-171.03903361 15.92262335 -171.03903361 15.92262335 -173 17 C-147.2948282 40.48442261 -112.79218217 47.37486509 -79 46 C-53.71290995 44.24158034 -28.27025994 34.44708142 -10.8125 15.6875 C-7.16771185 11.45006337 -4.22721801 6.8716217 -1.34765625 2.08984375 C-0.90292969 1.40019531 -0.45820313 0.71054687 0 0 Z " fill="#6A8BC6" transform="translate(364,294)"/>
<path d="M0 0 C2.31 0 4.62 0 7 0 C7.71614583 1.953125 8.43229167 3.90625 9.1484375 5.859375 C9.9826353 7.95634928 10.99072431 9.98144861 12 12 C13.65 8.04 15.3 4.08 17 0 C19.31 0 21.62 0 24 0 C24.33 6.27 24.66 12.54 25 19 C28.3 12.73 31.6 6.46 35 0 C37.31 0 39.62 0 42 0 C44.64 7.26 47.28 14.52 50 22 C48.02 22 46.04 22 44 22 C43.505 20.5459375 43.505 20.5459375 43 19.0625 C41.39253557 15.89278582 41.39253557 15.89278582 39.01953125 15.37890625 C37.02222356 15.14912749 35.00940186 15.06588203 33 15 C32.34 17.31 31.68 19.62 31 22 C27.04 22 23.08 22 19 22 C18.67 18.37 18.34 14.74 18 11 C16.68 14.63 15.36 18.26 14 22 C12.02 22 10.04 22 8 22 C7.01 18.7 6.02 15.4 5 12 C4.34 15.3 3.68 18.6 3 22 C1.35 22 -0.3 22 -2 22 C-1.34 14.74 -0.68 7.48 0 0 Z M38 6 C37.34 7.65 36.68 9.3 36 11 C37.32 11 38.64 11 40 11 C39.67 9.35 39.34 7.7 39 6 C38.67 6 38.34 6 38 6 Z " fill="#0B2067" transform="translate(627,140)"/>
<path d="M0 0 C2.0625 -0.04125 4.125 -0.0825 6.25 -0.125 C7.99023438 -0.15980469 7.99023438 -0.15980469 9.765625 -0.1953125 C12.85711479 -0.00862833 14.5204469 0.18598016 17 2 C18.0625 5.375 18.0625 5.375 18 9 C15.5625 11.4375 15.5625 11.4375 13 13 C14.32 15.31 15.64 17.62 17 20 C18.27289905 17.79283537 19.5433605 15.58432515 20.8125 13.375 C21.16892578 12.75753906 21.52535156 12.14007813 21.89257812 11.50390625 C24.05913172 7.72734494 26.0904299 3.91552228 28 0 C30.31 0 32.62 0 35 0 C37.64 7.26 40.28 14.52 43 22 C41.02 22 39.04 22 37 22 C36.505 20.5459375 36.505 20.5459375 36 19.0625 C34.39253557 15.89278582 34.39253557 15.89278582 32.01953125 15.37890625 C30.02222356 15.14912749 28.00940186 15.06588203 26 15 C25.34 17.31 24.68 19.62 24 22 C19.71 22 15.42 22 11 22 C9.35 18.7 7.7 15.4 6 12 C5.67 15.3 5.34 18.6 5 22 C3.35 22 1.7 22 0 22 C0 14.74 0 7.48 0 0 Z M6 4 C6 5.98 6 7.96 6 10 C7.65 10 9.3 10 11 10 C11.66 8.68 12.32 7.36 13 6 C10.04622502 3.63019987 10.04622502 3.63019987 6 4 Z M31 6 C30.34 7.65 29.68 9.3 29 11 C30.32 11 31.64 11 33 11 C32.67 9.35 32.34 7.7 32 6 C31.67 6 31.34 6 31 6 Z " fill="#0B2067" transform="translate(457,140)"/>
<path d="M0 0 C8.91 0 17.82 0 27 0 C24.50876934 14.94738394 13.82869317 26.06587618 2.26953125 35.14453125 C-3.26536885 39 -3.26536885 39 -7 39 C-6.6743886 35.97129713 -6.41049022 34.40779553 -4.21484375 32.2265625 C-3.46332031 31.69804687 -2.71179688 31.16953125 -1.9375 30.625 C-1.12152344 30.02042969 -0.30554688 29.41585937 0.53515625 28.79296875 C1.34855469 28.20128906 2.16195312 27.60960937 3 27 C11.08234897 21.06476461 16.53959861 12.92080278 21 4 C13.41 4.33 5.82 4.66 -2 5 C-1.34 3.35 -0.68 1.7 0 0 Z " fill="#6A8BC6" transform="translate(240,215)"/>
<path d="M0 0 C2.31 0 4.62 0 7 0 C8.92409498 2.24838066 10.105419 3.94219177 11.4375 6.5 C11.78748047 7.12003906 12.13746094 7.74007812 12.49804688 8.37890625 C13.35569369 9.9062225 14.18111194 11.45155713 15 13 C15.66 8.71 16.32 4.42 17 0 C18.65 0 20.3 0 22 0 C21.67 7.26 21.34 14.52 21 22 C18.69 22 16.38 22 14 22 C11.56640625 19.375 11.56640625 19.375 9.5625 16 C8.88316406 14.88625 8.20382812 13.7725 7.50390625 12.625 C7.00761719 11.75875 6.51132812 10.8925 6 10 C5.67 13.96 5.34 17.92 5 22 C3.02 22 1.04 22 -1 22 C-0.67 14.74 -0.34 7.48 0 0 Z " fill="#0B2067" transform="translate(577,140)"/>
<path d="M0 0 C1.63001953 0.01353516 1.63001953 0.01353516 3.29296875 0.02734375 C4.12441406 0.03894531 4.95585938 0.05054688 5.8125 0.0625 C6.1425 1.7125 6.4725 3.3625 6.8125 5.0625 C5.79929687 5.02769531 5.79929687 5.02769531 4.765625 4.9921875 C3.87359375 4.97414062 2.9815625 4.95609375 2.0625 4.9375 C0.73992188 4.90269531 0.73992188 4.90269531 -0.609375 4.8671875 C-3.33656426 5.07379275 -4.90065408 5.58944159 -7.1875 7.0625 C-8.33102916 9.34955831 -8.29084439 10.65514762 -8.25 13.1875 C-8.229375 14.46625 -8.20875 15.745 -8.1875 17.0625 C-4.8875 17.3925 -1.5875 17.7225 1.8125 18.0625 C1.8125 16.4125 1.8125 14.7625 1.8125 13.0625 C0.4925 13.0625 -0.8275 13.0625 -2.1875 13.0625 C-2.1875 11.7425 -2.1875 10.4225 -2.1875 9.0625 C1.1125 9.0625 4.4125 9.0625 7.8125 9.0625 C7.4825 13.3525 7.1525 17.6425 6.8125 22.0625 C0.77228733 23.57255317 -5.56558132 24.20416044 -11.1875 21.0625 C-14.17098463 19.1445456 -14.24364966 17.81784792 -15.0625 14.25 C-15.51772739 9.29712601 -13.82802693 6.04480338 -10.6875 2.25 C-7.39259501 -0.35846645 -4.00791944 -0.04309591 0 0 Z " fill="#0B2067" transform="translate(515.1875,139.9375)"/>
<path d="M0 0 C1.63001953 0.01353516 1.63001953 0.01353516 3.29296875 0.02734375 C4.12441406 0.03894531 4.95585937 0.05054688 5.8125 0.0625 C6.1425 1.7125 6.4725 3.3625 6.8125 5.0625 C5.79929687 5.02769531 5.79929687 5.02769531 4.765625 4.9921875 C3.87359375 4.97414062 2.9815625 4.95609375 2.0625 4.9375 C0.73992187 4.90269531 0.73992187 4.90269531 -0.609375 4.8671875 C-3.33656426 5.07379275 -4.90065408 5.58944159 -7.1875 7.0625 C-8.33102916 9.34955831 -8.29084439 10.65514762 -8.25 13.1875 C-8.229375 14.46625 -8.20875 15.745 -8.1875 17.0625 C-4.8875 17.3925 -1.5875 17.7225 1.8125 18.0625 C1.8125 16.4125 1.8125 14.7625 1.8125 13.0625 C0.4925 13.0625 -0.8275 13.0625 -2.1875 13.0625 C-2.1875 11.7425 -2.1875 10.4225 -2.1875 9.0625 C1.1125 9.0625 4.4125 9.0625 7.8125 9.0625 C7.4825 13.3525 7.1525 17.6425 6.8125 22.0625 C0.77228733 23.57255317 -5.56558132 24.20416044 -11.1875 21.0625 C-14.17098463 19.1445456 -14.24364966 17.81784792 -15.0625 14.25 C-15.51772739 9.29712601 -13.82802693 6.04480338 -10.6875 2.25 C-7.39259501 -0.35846645 -4.00791944 -0.04309591 0 0 Z " fill="#0B2067" transform="translate(437.1875,139.9375)"/>
<path d="M0 0 C1.18851563 0.00902344 2.37703125 0.01804687 3.6015625 0.02734375 C4.51679687 0.03894531 5.43203125 0.05054688 6.375 0.0625 C6.29415297 3.33434808 6.18131126 6.60432779 6.0625 9.875 C6.03994141 10.80376953 6.01738281 11.73253906 5.99414062 12.68945312 C5.95869141 13.58212891 5.92324219 14.47480469 5.88671875 15.39453125 C5.86053467 16.21671143 5.83435059 17.0388916 5.80737305 17.88598633 C5.28003027 20.54056617 4.53043833 21.46058843 2.375 23.0625 C-0.54296875 23.35546875 -0.54296875 23.35546875 -3.8125 23.25 C-4.89917969 23.22292969 -5.98585938 23.19585937 -7.10546875 23.16796875 C-8.35263672 23.11576172 -8.35263672 23.11576172 -9.625 23.0625 C-9.625 21.4125 -9.625 19.7625 -9.625 18.0625 C-6.655 18.3925 -3.685 18.7225 -0.625 19.0625 C-0.295 17.7425 0.035 16.4225 0.375 15.0625 C-0.615 15.5575 -0.615 15.5575 -1.625 16.0625 C-5.2067622 16.45182198 -6.98781721 16.49846718 -10 14.4375 C-12.11218421 11.35046154 -12.15069449 9.74236146 -11.625 6.0625 C-8.66700375 0.01205312 -6.4941666 -0.0636683 0 0 Z M-4.875 4.75 C-6.94647777 6.9481322 -6.94647777 6.9481322 -6.375 9.8125 C-6.1275 10.555 -5.88 11.2975 -5.625 12.0625 C-2.33424004 11.68345792 -2.33424004 11.68345792 0.375 10.0625 C0.41592937 7.72952567 0.41741723 5.39544775 0.375 3.0625 C-2.69375 2.7625 -2.69375 2.7625 -4.875 4.75 Z " fill="#0B2067" transform="translate(655.625,331.9375)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C9.14169523 7.2271408 11.92957946 14.39162534 14 22 C12.02 22 10.04 22 8 22 C7.34 19.69 6.68 17.38 6 15 C1.91435675 15.11544282 1.91435675 15.11544282 -2 16 C-4.08632697 18.91841857 -4.08632697 18.91841857 -5 22 C-6.98 22 -8.96 22 -11 22 C-9.48968084 16.73877829 -7.09625809 12.32767414 -4.375 7.625 C-3.95476563 6.88894531 -3.53453125 6.15289062 -3.1015625 5.39453125 C-2.07247236 3.5936235 -1.03689244 1.79642682 0 0 Z M2 6 C1.34 7.65 0.68 9.3 0 11 C1.32 11 2.64 11 4 11 C3.67 9.35 3.34 7.7 3 6 C2.67 6 2.34 6 2 6 Z " fill="#0B2067" transform="translate(610,140)"/>
<path d="M0 0 C3.25 -0.375 3.25 -0.375 6 0 C9.00727783 7.23026373 11.59326535 14.54936402 14 22 C12.02 22 10.04 22 8 22 C6.5393715 19.35261084 6 18.10551666 6 15 C1.91435675 15.11544282 1.91435675 15.11544282 -2 16 C-4.08632697 18.91841857 -4.08632697 18.91841857 -5 22 C-7.31 22 -9.62 22 -12 22 C-10.7550659 19.45574147 -9.50426112 16.91465639 -8.25 14.375 C-7.90195312 13.66214844 -7.55390625 12.94929687 -7.1953125 12.21484375 C-5.06753688 7.91988926 -2.94225446 3.79645736 0 0 Z M2 6 C1.34 7.65 0.68 9.3 0 11 C1.32 11 2.64 11 4 11 C3.67 9.35 3.34 7.7 3 6 C2.67 6 2.34 6 2 6 Z " fill="#0B2067" transform="translate(561,140)"/>
<path d="M0 0 C2.31 0 4.62 0 7 0 C8.17461555 3.10121711 9.33830456 6.20642241 10.5 9.3125 C10.83386719 10.19357422 11.16773438 11.07464844 11.51171875 11.98242188 C11.82753906 12.82998047 12.14335938 13.67753906 12.46875 14.55078125 C12.90864258 15.72120972 12.90864258 15.72120972 13.35742188 16.9152832 C14 19 14 19 14 22 C12.02 22 10.04 22 8 22 C7.34 20.02 6.68 18.04 6 16 C3.36 15.67 0.72 15.34 -2 15 C-2.309375 15.969375 -2.61875 16.93875 -2.9375 17.9375 C-4 21 -4 21 -5 22 C-6.99958364 22.04080783 -9.00045254 22.04254356 -11 22 C-7.33333333 14.66666667 -3.66666667 7.33333333 0 0 Z M2 6 C1.34 7.65 0.68 9.3 0 11 C1.65 11 3.3 11 5 11 C4.34 9.35 3.68 7.7 3 6 C2.67 6 2.34 6 2 6 Z " fill="#0B2067" transform="translate(719,140)"/>
<path d="M0 0 C4.95 0 9.9 0 15 0 C15.99 2.64 16.98 5.28 18 8 C19.77706209 5.33440687 20.87548273 2.9719385 22 0 C23.65 0 25.3 0 27 0 C26.37183502 3.94949704 25.1354471 7.19892028 23.4375 10.8125 C22.98246094 11.78832031 22.52742187 12.76414062 22.05859375 13.76953125 C21.70925781 14.50558594 21.35992188 15.24164063 21 16 C19.02 16 17.04 16 15 16 C13.68 12.37 12.36 8.74 11 5 C9.35 5.33 7.7 5.66 6 6 C5.67 9.3 5.34 12.6 5 16 C3.35 16 1.7 16 0 16 C0 10.72 0 5.44 0 0 Z " fill="#0B2067" transform="translate(549,362)"/>
<path d="M0 0 C1.32 0.33 2.64 0.66 4 1 C4.19934164 7.7182561 3.77782269 13.97720649 2.4375 20.5625 C2.18274902 21.83818848 2.18274902 21.83818848 1.92285156 23.13964844 C0.0351987 31.88913064 -3.263262 39.8967367 -7 48 C-8.98 48 -10.96 48 -13 48 C-12.31164062 46.43765625 -12.31164062 46.43765625 -11.609375 44.84375 C-5.25958244 30.13659896 -1.73677147 15.9051703 0 0 Z " fill="#6A8BC6" transform="translate(306,194)"/>
<path d="M0 0 C1.93719378 -0.05406122 3.87481921 -0.09282025 5.8125 -0.125 C6.89144531 -0.14820313 7.97039063 -0.17140625 9.08203125 -0.1953125 C12 0 12 0 15 2 C16.08005494 5.47160516 16.22380004 7.37056239 15 10.8125 C13 13 13 13 9.375 13.875 C7.704375 13.936875 7.704375 13.936875 6 14 C5.67 13.67 5.34 13.34 5 13 C4.67 15.97 4.34 18.94 4 22 C2.35 22 0.7 22 -1 22 C-1.02700675 19.08327116 -1.04684237 16.16679323 -1.0625 13.25 C-1.07087891 12.425 -1.07925781 11.6 -1.08789062 10.75 C-1.09111328 9.95078125 -1.09433594 9.1515625 -1.09765625 8.328125 C-1.10289307 7.5949707 -1.10812988 6.86181641 -1.11352539 6.10644531 C-0.9975109 3.9538151 -0.59073271 2.0685865 0 0 Z M5 4 C4.67 5.65 4.34 7.3 4 9 C5.89179203 9.6814823 5.89179203 9.6814823 8 10 C8.66 9.34 9.32 8.68 10 8 C9.6814823 5.89179203 9.6814823 5.89179203 9 4 C7.68 4 6.36 4 5 4 Z " fill="#0B2067" transform="translate(535,140)"/>
<path d="M0 0 C0.90105469 0.02707031 1.80210937 0.05414063 2.73046875 0.08203125 C3.76107422 0.13423828 3.76107422 0.13423828 4.8125 0.1875 C5.1425 1.8375 5.4725 3.4875 5.8125 5.1875 C3.5025 5.1875 1.1925 5.1875 -1.1875 5.1875 C-1.1875 6.1775 -1.1875 7.1675 -1.1875 8.1875 C0.34118307 9.39093135 1.87014624 10.59401514 3.40234375 11.79296875 C4.8125 13.1875 4.8125 13.1875 5.8125 16.3125 C5.8125 19.1875 5.8125 19.1875 3.8125 22.1875 C-0.07914348 23.48471449 -3.08179226 23.31783993 -7.1875 23.1875 C-7.8475 22.8575 -8.5075 22.5275 -9.1875 22.1875 C-9.1875 20.5375 -9.1875 18.8875 -9.1875 17.1875 C-6.2175 17.5175 -3.2475 17.8475 -0.1875 18.1875 C-1.30612612 14.83162164 -2.00598754 14.3872758 -4.75 12.375 C-8.10562581 9.60505837 -8.10562581 9.60505837 -8.8125 6 C-7.63270369 0.69091658 -5.19908789 -0.20256187 0 0 Z " fill="#0B2067" transform="translate(694.1875,139.8125)"/>
<path d="M0 0 C0.33 0 0.66 0 1 0 C1.54158292 7.11685723 1.54158292 7.11685723 -0.80859375 10.94921875 C-21.74191374 32.75346438 -21.74191374 32.75346438 -31 34 C-32.75 32.5625 -32.75 32.5625 -34 31 C-33.12859375 30.40574219 -32.2571875 29.81148438 -31.359375 29.19921875 C-13.27120092 16.89792157 -13.27120092 16.89792157 0 0 Z " fill="#6A8BC6" transform="translate(285,253)"/>
<path d="M0 0 C2.5625 1.5625 2.5625 1.5625 4 4 C4.71525283 8.37098953 4.50639423 10.25623347 2 13.9375 C-2.15080314 16.79117716 -4.0301078 16.7099846 -9 16 C-11.5 14.5 -11.5 14.5 -13 12 C-13.78550518 8.00701533 -13.60146332 5.04602317 -11.5625 1.5 C-7.74710589 -0.73340143 -4.31090664 -0.76726228 0 0 Z M-5 3 C-5.99 3.66 -6.98 4.32 -8 5 C-8.2502157 8.08365871 -8.2502157 8.08365871 -8 11 C-5.60280568 11.67958603 -5.60280568 11.67958603 -3 12 C-0.71271266 10.16116119 -0.71271266 10.16116119 -0.875 7.375 C-0.91625 6.59125 -0.9575 5.8075 -1 5 C-2.32 4.34 -3.64 3.68 -5 3 Z " fill="#0B2067" transform="translate(551,332)"/>
<path d="M0 0 C2.57183966 0.5779415 3.47974895 1.40714823 5.0859375 3.46875 C6.27872125 7.30269777 6.26629038 9.01357368 4.7734375 12.78125 C3.0859375 15.46875 3.0859375 15.46875 1.0859375 16.46875 C-5.93861796 17.15968988 -5.93861796 17.15968988 -9.9140625 14.34375 C-11.9140625 11.46875 -11.9140625 11.46875 -12.5390625 8.46875 C-11.04805013 1.31189061 -6.5765337 -0.08978203 0 0 Z M-5.7265625 4.59375 C-7.19805996 6.48228208 -7.19805996 6.48228208 -6.6015625 9.15625 C-6.3746875 9.919375 -6.1478125 10.6825 -5.9140625 11.46875 C-4.2640625 11.79875 -2.6140625 12.12875 -0.9140625 12.46875 C0.96137104 8.60111554 0.96137104 8.60111554 0.0859375 4.46875 C-2.4198557 3.29937984 -3.34556004 3.1158864 -5.7265625 4.59375 Z " fill="#0B2067" transform="translate(636.9140625,331.53125)"/>
<path d="M0 0 C2.29141286 -0.05384521 4.58318188 -0.09272087 6.875 -0.125 C8.78925781 -0.15980469 8.78925781 -0.15980469 10.7421875 -0.1953125 C14 0 14 0 16 2 C16.1953125 5.2578125 16.1953125 5.2578125 16.125 9.125 C16.10695313 10.40632813 16.08890625 11.68765625 16.0703125 13.0078125 C16.04710937 13.99523437 16.02390625 14.98265625 16 16 C14.35 16 12.7 16 11 16 C10.67 12.37 10.34 8.74 10 5 C8.68 5 7.36 5 6 5 C4.85185003 7.29629994 4.69230191 9.07698086 4.4375 11.625 C4.35371094 12.44226563 4.26992188 13.25953125 4.18359375 14.1015625 C4.12300781 14.72804688 4.06242188 15.35453125 4 16 C2.35 16 0.7 16 -1 16 C-1.02725341 14.2709226 -1.04650987 12.54171824 -1.0625 10.8125 C-1.07410156 9.84957031 -1.08570313 8.88664062 -1.09765625 7.89453125 C-1.00486622 5.14423487 -0.62111685 2.67520409 0 0 Z " fill="#0B2067" transform="translate(558,332)"/>
<path d="M0 0 C2.29141286 -0.05384521 4.58318188 -0.09272087 6.875 -0.125 C8.78925781 -0.15980469 8.78925781 -0.15980469 10.7421875 -0.1953125 C14 0 14 0 16 2 C16.1953125 5.2578125 16.1953125 5.2578125 16.125 9.125 C16.10695313 10.40632813 16.08890625 11.68765625 16.0703125 13.0078125 C16.04710937 13.99523437 16.02390625 14.98265625 16 16 C14.35 16 12.7 16 11 16 C10.67 12.37 10.34 8.74 10 5 C8.68 5 7.36 5 6 5 C4.85185003 7.29629994 4.69230191 9.07698086 4.4375 11.625 C4.35371094 12.44226563 4.26992188 13.25953125 4.18359375 14.1015625 C4.12300781 14.72804688 4.06242187 15.35453125 4 16 C2.35 16 0.7 16 -1 16 C-1.02725341 14.2709226 -1.04650987 12.54171824 -1.0625 10.8125 C-1.07410156 9.84957031 -1.08570313 8.88664062 -1.09765625 7.89453125 C-1.00486622 5.14423487 -0.62111685 2.67520409 0 0 Z " fill="#0B2067" transform="translate(483,332)"/>
<path d="M0 0 C1.21236328 0.04060547 1.21236328 0.04060547 2.44921875 0.08203125 C3.06410156 0.11683594 3.67898438 0.15164063 4.3125 0.1875 C4.6425 1.8375 4.9725 3.4875 5.3125 5.1875 C3.0025 5.1875 0.6925 5.1875 -1.6875 5.1875 C-0.70883662 6.33376322 0.27044587 7.47949788 1.25 8.625 C1.79527344 9.26308594 2.34054687 9.90117187 2.90234375 10.55859375 C3.68045695 11.45741149 4.47186528 12.34686528 5.3125 13.1875 C5.6875 16.0625 5.6875 16.0625 5.3125 19.1875 C3.3125 21.125 3.3125 21.125 0.3125 22.1875 C-2.46579356 22.12587426 -4.93687041 21.70032925 -7.6875 21.1875 C-8.0175 19.5375 -8.3475 17.8875 -8.6875 16.1875 C-5.7175 16.5175 -2.7475 16.8475 0.3125 17.1875 C-0.83290225 15.87462379 -1.97883433 14.56220979 -3.125 13.25 C-3.76308594 12.51910156 -4.40117188 11.78820312 -5.05859375 11.03515625 C-5.91291459 10.06610647 -6.77401175 9.10098825 -7.6875 8.1875 C-8.14826844 3.81019984 -8.14826844 3.81019984 -6.46484375 1.53515625 C-4.16667456 -0.2074116 -2.84893333 -0.12386667 0 0 Z " fill="#0B2067" transform="translate(523.6875,356.8125)"/>
<path d="M0 0 C3.6815625 -0.0309375 3.6815625 -0.0309375 7.4375 -0.0625 C8.21150146 -0.071604 8.98550293 -0.08070801 9.78295898 -0.09008789 C11.52221717 -0.09724913 13.26145304 -0.05024702 15 0 C16 1 16 1 16.09765625 4.37890625 C16.09098369 5.77347077 16.07902532 7.16801743 16.0625 8.5625 C16.05798828 9.27341797 16.05347656 9.98433594 16.04882812 10.71679688 C16.03700864 12.47790013 16.01907263 14.23896036 16 16 C14.35 16 12.7 16 11 16 C11 12.04 11 8.08 11 4 C9.35 4.33 7.7 4.66 6 5 C5.34 8.63 4.68 12.26 4 16 C2.35 16 0.7 16 -1 16 C-0.67 10.72 -0.34 5.44 0 0 Z " fill="#0B2067" transform="translate(423,332)"/>
<path d="M0 0 C2.4375 1.6875 2.4375 1.6875 4 4 C4 5.65 4 7.3 4 9 C0.37 9 -3.26 9 -7 9 C-6.01 10.485 -6.01 10.485 -5 12 C-3.02252878 12.36297936 -3.02252878 12.36297936 -0.875 12.1875 C0.40375 12.125625 1.6825 12.06375 3 12 C2.625 13.9375 2.625 13.9375 2 16 C-1.4908138 17.7454069 -5.29072535 16.86278 -9 16 C-11.91100766 13.70647881 -11.96570977 12.23574532 -12.5 8.5625 C-12 5 -12 5 -9.9375 2.0625 C-6.25937488 -0.52001338 -4.40768137 -0.72125695 0 0 Z M-4 3 C-4.99 3.99 -5.98 4.98 -7 6 C-5.02 6 -3.04 6 -1 6 C-1 5.34 -1 4.68 -1 4 C-1.99 3.67 -2.98 3.34 -4 3 Z " fill="#0B2067" transform="translate(543,362)"/>
<path d="M0 0 C1.79158489 -0.02696365 3.58328473 -0.04637917 5.375 -0.0625 C6.37273438 -0.07410156 7.37046875 -0.08570313 8.3984375 -0.09765625 C11 0 11 0 13 1 C13 5.95 13 10.9 13 16 C11.35 16 9.7 16 8 16 C8 13.69 8 11.38 8 9 C6.35 9.66 4.7 10.32 3 11 C4.32 11.66 5.64 12.32 7 13 C6.67 13.99 6.34 14.98 6 16 C2.625 16.1875 2.625 16.1875 -1 16 C-3 13 -3 13 -3.125 10.5 C-1.76557611 7.47905803 -1.06156872 7.3359637 1.9375 6.1875 C4.09178299 5.47983172 5.73139821 5 8 5 C8 4.34 8 3.68 8 3 C3.545 3.495 3.545 3.495 -1 4 C-0.67 2.68 -0.34 1.36 0 0 Z " fill="#0B2067" transform="translate(578,332)"/>
<path d="M0 0 C1.79158489 -0.02696365 3.58328473 -0.04637917 5.375 -0.0625 C6.37273437 -0.07410156 7.37046875 -0.08570313 8.3984375 -0.09765625 C11 0 11 0 13 1 C13 5.95 13 10.9 13 16 C11.35 16 9.7 16 8 16 C8 13.69 8 11.38 8 9 C6.35 9.66 4.7 10.32 3 11 C4.32 11.66 5.64 12.32 7 13 C6.67 13.99 6.34 14.98 6 16 C2.625 16.1875 2.625 16.1875 -1 16 C-3 13 -3 13 -3.125 10.5 C-1.76557611 7.47905803 -1.06156872 7.3359637 1.9375 6.1875 C4.09178299 5.47983172 5.73139821 5 8 5 C8 4.34 8 3.68 8 3 C3.545 3.495 3.545 3.495 -1 4 C-0.67 2.68 -0.34 1.36 0 0 Z " fill="#0B2067" transform="translate(503,332)"/>
<path d="M0 0 C2.375 1.125 2.375 1.125 4 3 C4.25 6.1875 4.25 6.1875 4 9 C0.7 9.33 -2.6 9.66 -6 10 C-5.67 10.66 -5.34 11.32 -5 12 C-2.36 12 0.28 12 3 12 C3 13.32 3 14.64 3 16 C-5.93033566 17.51361621 -5.93033566 17.51361621 -10 15 C-12.16719206 12.3402643 -11.99534863 10.91162404 -11.8125 7.4375 C-11.02487718 4.10524961 -10.70318387 2.94629239 -8 1 C-5.19084526 -0.40457737 -3.11189466 -0.24895157 0 0 Z M-6 4 C-6 4.66 -6 5.32 -6 6 C-4.02 6 -2.04 6 0 6 C-0.66 5.01 -1.32 4.02 -2 3 C-4.0745356 2.8509288 -4.0745356 2.8509288 -6 4 Z " fill="#0B2067" transform="translate(611,362)"/>
<path d="M0 0 C2.52623559 2.83244597 3 4.21564776 3 8 C-0.63 8 -4.26 8 -8 8 C-7.34 9.32 -6.68 10.64 -6 12 C-3.69 12 -1.38 12 1 12 C1 13.32 1 14.64 1 16 C-0.58246707 16.08153933 -2.16616273 16.13943557 -3.75 16.1875 C-5.07257813 16.23970703 -5.07257813 16.23970703 -6.421875 16.29296875 C-9.53689778 15.93898889 -10.83014804 15.24947955 -13 13 C-14.03036485 8.88611682 -13.62592092 5.11577208 -11.5625 1.4375 C-7.77184697 -0.6889639 -4.23919648 -0.94739618 0 0 Z M-7 3 C-7.33 3.99 -7.66 4.98 -8 6 C-6.02 6 -4.04 6 -2 6 C-2.33 5.01 -2.66 4.02 -3 3 C-4.32 3 -5.64 3 -7 3 Z " fill="#0B2067" transform="translate(466,332)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C6.10477512 5.78591511 5.99250545 11.29309366 5 17 C8.3 17 11.6 17 15 17 C15 18.32 15 19.64 15 21 C10.05 21 5.1 21 0 21 C0 14.07 0 7.14 0 0 Z " fill="#0B2067" transform="translate(610,327)"/>
<path d="M0 0 C0.33 1.65 0.66 3.3 1 5 C-1.64 5 -4.28 5 -7 5 C-7 7.31 -7 9.62 -7 12 C-4.36 12 -1.72 12 1 12 C0.67 13.32 0.34 14.64 0 16 C-3.725448 16.803528 -6.23465376 17.26620739 -9.875 16 C-12 14 -12 14 -13 10.6875 C-13 6.55040661 -12.22028372 4.45377468 -10 1 C-6.78548731 -0.60725635 -3.56387464 -0.05748185 0 0 Z " fill="#0B2067" transform="translate(597,362)"/>
<path d="M0 0 C0.33 1.65 0.66 3.3 1 5 C-0.65 5 -2.3 5 -4 5 C-3.175 5.721875 -2.35 6.44375 -1.5 7.1875 C1 10 1 10 1.3125 12.8125 C1.1578125 13.8953125 1.1578125 13.8953125 1 15 C-2.99560608 17.83022097 -6.42737323 16.76210446 -11 16 C-11 14.68 -11 13.36 -11 12 C-9.02 12 -7.04 12 -5 12 C-5.845625 11.113125 -6.69125 10.22625 -7.5625 9.3125 C-10.00880626 6.3962818 -10.00880626 6.3962818 -9.9375 3.1875 C-8.04200905 -1.23531222 -4.26503762 -0.07898218 0 0 Z " fill="#0B2067" transform="translate(627,362)"/>
<path d="M0 0 C1.37478516 0.01740234 1.37478516 0.01740234 2.77734375 0.03515625 C3.69644531 0.04417969 4.61554687 0.05320312 5.5625 0.0625 C6.27277344 0.07410156 6.98304688 0.08570313 7.71484375 0.09765625 C7.71484375 1.41765625 7.71484375 2.73765625 7.71484375 4.09765625 C6.06484375 4.09765625 4.41484375 4.09765625 2.71484375 4.09765625 C3.56046875 4.98453125 4.40609375 5.87140625 5.27734375 6.78515625 C7.72365001 9.70137445 7.72365001 9.70137445 7.65234375 12.91015625 C6.71484375 15.09765625 6.71484375 15.09765625 4.71484375 16.09765625 C3.17460379 16.16716166 1.63149387 16.18217982 0.08984375 16.16015625 C-1.13605469 16.14662109 -1.13605469 16.14662109 -2.38671875 16.1328125 C-3.01320313 16.12121094 -3.6396875 16.10960937 -4.28515625 16.09765625 C-4.28515625 14.44765625 -4.28515625 12.79765625 -4.28515625 11.09765625 C-1.31515625 11.59265625 -1.31515625 11.59265625 1.71484375 12.09765625 C0.88984375 11.39640625 0.06484375 10.69515625 -0.78515625 9.97265625 C-3.28515625 7.09765625 -3.28515625 7.09765625 -3.66015625 3.84765625 C-3.15385496 0.13478013 -3.15385496 0.13478013 0 0 Z " fill="#0B2067" transform="translate(723.28515625,331.90234375)"/>
<path d="M0 0 C0 1.32 0 2.64 0 4 C0.99 4 1.98 4 3 4 C3 5.32 3 6.64 3 8 C1.68 8 0.36 8 -1 8 C-1 10.64 -1 13.28 -1 16 C0.32 16 1.64 16 3 16 C3 17.32 3 18.64 3 20 C-0.375 20.125 -0.375 20.125 -4 20 C-6 18 -6 18 -6.23046875 16.125 C-6.21628906 15.42375 -6.20210937 14.7225 -6.1875 14 C-6.18105469 13.29875 -6.17460938 12.5975 -6.16796875 11.875 C-6 10 -6 10 -5 8 C-5.99 8 -6.98 8 -8 8 C-8 6.68 -8 5.36 -8 4 C-7.01 4 -6.02 4 -5 4 C-5 3.01 -5 2.02 -5 1 C-3 0 -3 0 0 0 Z " fill="#0B2067" transform="translate(692,328)"/>
<path d="M0 0 C1.23556641 0.01740234 1.23556641 0.01740234 2.49609375 0.03515625 C3.32238281 0.04417969 4.14867187 0.05320312 5 0.0625 C5.63808594 0.07410156 6.27617188 0.08570313 6.93359375 0.09765625 C6.93359375 1.41765625 6.93359375 2.73765625 6.93359375 4.09765625 C5.28359375 4.42765625 3.63359375 4.75765625 1.93359375 5.09765625 C2.75859375 5.65453125 3.58359375 6.21140625 4.43359375 6.78515625 C6.93359375 9.09765625 6.93359375 9.09765625 7.37109375 11.72265625 C6.93359375 14.09765625 6.93359375 14.09765625 4.93359375 16.09765625 C2.76953125 16.29296875 2.76953125 16.29296875 0.30859375 16.22265625 C-0.50867188 16.20460938 -1.3259375 16.1865625 -2.16796875 16.16796875 C-2.79445313 16.14476562 -3.4209375 16.1215625 -4.06640625 16.09765625 C-4.39640625 14.44765625 -4.72640625 12.79765625 -5.06640625 11.09765625 C-2.75640625 11.42765625 -0.44640625 11.75765625 1.93359375 12.09765625 C1.93359375 11.43765625 1.93359375 10.77765625 1.93359375 10.09765625 C1.00546875 9.66453125 1.00546875 9.66453125 0.05859375 9.22265625 C-2.06640625 8.09765625 -2.06640625 8.09765625 -4.06640625 6.09765625 C-3.34205084 0.15794191 -3.34205084 0.15794191 0 0 Z " fill="#0B2067" transform="translate(676.06640625,331.90234375)"/>
<path d="M0 0 C0 1.32 0 2.64 0 4 C1.32 4 2.64 4 4 4 C4 5.32 4 6.64 4 8 C2.68 8 1.36 8 0 8 C0 10.64 0 13.28 0 16 C1.32 16.33 2.64 16.66 4 17 C3.67 17.99 3.34 18.98 3 20 C0.66705225 20.04241723 -1.66702567 20.04092937 -4 20 C-5 19 -5 19 -5.09765625 16.49609375 C-5.08605469 15.48675781 -5.07445312 14.47742188 -5.0625 13.4375 C-5.05347656 12.42558594 -5.04445313 11.41367187 -5.03515625 10.37109375 C-5.02355469 9.58863281 -5.01195312 8.80617188 -5 8 C-5.66 8 -6.32 8 -7 8 C-7 6.68 -7 5.36 -7 4 C-2.77777778 0 -2.77777778 0 0 0 Z " fill="#0B2067" transform="translate(525,328)"/>
<path d="M0 0 C0 1.32 0 2.64 0 4 C1.32 4 2.64 4 4 4 C4 5.32 4 6.64 4 8 C2.68 8 1.36 8 0 8 C0 10.64 0 13.28 0 16 C1.32 16.33 2.64 16.66 4 17 C3.67 17.99 3.34 18.98 3 20 C0.66705225 20.04241723 -1.66702567 20.04092937 -4 20 C-5 19 -5 19 -5.09765625 16.49609375 C-5.08605469 15.48675781 -5.07445312 14.47742188 -5.0625 13.4375 C-5.05347656 12.42558594 -5.04445313 11.41367187 -5.03515625 10.37109375 C-5.02355469 9.58863281 -5.01195312 8.80617188 -5 8 C-5.66 8 -6.32 8 -7 8 C-7 6.68 -7 5.36 -7 4 C-2.77777778 0 -2.77777778 0 0 0 Z " fill="#0B2067" transform="translate(448,328)"/>
<path d="M0 0 C0 1.32 0 2.64 0 4 C-2.31 4.33 -4.62 4.66 -7 5 C-7.33 6.65 -7.66 8.3 -8 10 C-5.69 10.66 -3.38 11.32 -1 12 C-1.33 13.32 -1.66 14.64 -2 16 C-9.4815331 16.36794425 -9.4815331 16.36794425 -11.9375 14.625 C-14.13302006 11.2671458 -14.3943461 9.02233021 -14 5 C-10.94981482 -1.37765993 -6.24078779 -0.53594552 0 0 Z " fill="#0B2067" transform="translate(718,332)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C5.67 7.26 5.34 14.52 5 22 C3.02 22 1.04 22 -1 22 C-0.67 14.74 -0.34 7.48 0 0 Z " fill="#0B2067" transform="translate(448,140)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C5.67 6.93 5.34 13.86 5 21 C3.35 21 1.7 21 0 21 C0 14.07 0 7.14 0 0 Z " fill="#0B2067" transform="translate(414,327)"/>
<path d="M0 0 C3.63 0 7.26 0 11 0 C11 1.32 11 2.64 11 4 C10.360625 4.12375 9.72125 4.2475 9.0625 4.375 C8.381875 4.58125 7.70125 4.7875 7 5 C5.85185003 7.29629994 5.69230191 9.07698086 5.4375 11.625 C5.35371094 12.44226563 5.26992188 13.25953125 5.18359375 14.1015625 C5.12300781 14.72804688 5.06242187 15.35453125 5 16 C3.35 16 1.7 16 0 16 C0 10.72 0 5.44 0 0 Z " fill="#0B2067" transform="translate(470,332)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 6.93 5 13.86 5 21 C3.35 21 1.7 21 0 21 C0 14.07 0 7.14 0 0 Z " fill="#0B2067" transform="translate(594,327)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C5.67 5.28 5.34 10.56 5 16 C3.35 16 1.7 16 0 16 C0 10.72 0 5.44 0 0 Z " fill="#0B2067" transform="translate(577,362)"/>
<path d="M0 0 C1.98 0 3.96 0 6 0 C5.67 5.28 5.34 10.56 5 16 C3.35 16 1.7 16 0 16 C0 10.72 0 5.44 0 0 Z " fill="#0B2067" transform="translate(530,332)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 5.28 5 10.56 5 16 C3.35 16 1.7 16 0 16 C0 10.72 0 5.44 0 0 Z " fill="#0B2067" transform="translate(664,332)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5.09688221 5.44693314 5.12862175 10.63904667 4 16 C2.35 16 0.7 16 -1 16 C-0.67 10.72 -0.34 5.44 0 0 Z " fill="#0B2067" transform="translate(697,332)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C4.67 1.32 4.34 2.64 4 4 C2.35 4 0.7 4 -1 4 C-0.67 2.68 -0.34 1.36 0 0 Z " fill="#0B2067" transform="translate(703,158)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 1.32 5 2.64 5 4 C3.35 4 1.7 4 0 4 C0 2.68 0 1.36 0 0 Z " fill="#0B2067" transform="translate(697,326)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 1.32 5 2.64 5 4 C3.35 4 1.7 4 0 4 C0 2.68 0 1.36 0 0 Z " fill="#0B2067" transform="translate(664,326)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 1.32 5 2.64 5 4 C3.35 4 1.7 4 0 4 C0 2.68 0 1.36 0 0 Z " fill="#0B2067" transform="translate(530,326)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 1.32 5 2.64 5 4 C3.35 4 1.7 4 0 4 C0 2.68 0 1.36 0 0 Z " fill="#0B2067" transform="translate(735,158)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C4.34 1.32 3.68 2.64 3 4 C1.35 4 -0.3 4 -2 4 C-1.34 2.68 -0.68 1.36 0 0 Z " fill="#0B2067" transform="translate(665,135)"/>
<path d="M0 0 C1.65 0 3.3 0 5 0 C5 0.99 5 1.98 5 3 C3.35 3 1.7 3 0 3 C0 2.01 0 1.02 0 0 Z " fill="#0B2067" transform="translate(577,357)"/>
</svg>
', $plantilla);
			$plantilla = str_replace("[N_FACTURA]", $ingr_info["ingr_numero_factura"], $plantilla);
			$plantilla = str_replace("[FECHA]", date("Y-m-d"), $plantilla);
			$plantilla = str_replace("[CONS_NOMBRE]", $cons_info["cons_nombre"], $plantilla);
			$plantilla = str_replace("[CONS_RUC]", $cons_info["cons_ruc"], $plantilla);
			$plantilla = str_replace("[GUIA_NUMERO]", $ingr_info["cade_guia"], $plantilla);
			$plantilla = str_replace("[DETALLES]", $detalle_factura, $plantilla);

			// Solo agregar datos de FE si fue exitosa
			if ($fe_exitosa) {
				$plantilla = str_replace("[FE_CUFE]", $fe_cufe, $plantilla);
				$plantilla = str_replace("[PROTO_AUTO]", $fe_protocolo_autoizacion, $plantilla);
				$qr = file_get_contents("https://api.e-integracion.com/a-qr.php?url=$fe_qr");
				$plantilla = str_replace("[QR]", $qr, $plantilla);
			} else {
				// Si hubo error, colocar texto indicativo
				$plantilla = str_replace("[FE_CUFE]", "NO FISCAL", $plantilla);
				$plantilla = str_replace("[PROTO_AUTO]", "N/A", $plantilla);
				$plantilla = str_replace("[QR]", "", $plantilla);
			}

			$plantilla = str_replace("[SUBTOTAL]", $ingr_info["ingr_subtotal"], $plantilla);
			$plantilla = str_replace("[TOTAL]", $ingr_info["ingr_total"], $plantilla);
			$plantilla = str_replace("[ITBMS]", $ingr_info["ingr_impuesto"], $plantilla);

			$mpdf = new Mpdf(['mode' => 'utf-8', 'tempDir' => __DIR__ . '/temp', 'format' => 'A4']);
			$mpdf->WriteHTML($plantilla);
			$mpdf->Output(__DIR__ . "/factura.pdf");

			// RESPUESTA AL FRONTEND
			$response = array(
				"msg" => "done",
				"html" => $plantilla,
				"ingr_id" => $last_id,
				"fe_exitosa" => $fe_exitosa,
				"error_fe" => $error_fe,
				"warning_fe" => $warning_fe
			);

			if ($fe_exitosa) {
				$response["resFacturaInfo"] = $res_factura_info;
				$response["mensaje"] = "Factura electrónica generada exitosamente";
			}

			if ($error_fe) {
				$response["error_mensaje"] = $error_mensaje;
				$response["mensaje"] = "Factura NO FISCAL generada. Error en FE: " . substr($error_mensaje, 0, 100);
			}

			if ($warning_fe) {
				$response["warning_mensaje"] = $warning_mensaje;
			}

			echo json_encode($response);
      } else 
	  {
         /**
          * Esta seccion es para guardar los cargos
          **/
         $case_id = $_POST["case_id"];
         $caca_monto = $_POST["caca_monto"];
         $cade_id = $_POST["cade_id"];
         $carg_id = $_POST["carg_id"];
         $cons_id = $_POST["cons_id"];

         // Vamos a agrupar para que todos los que tenga numero de guia, se añada 1 monto
         $cade_guia = $_POST["cade_guia"];

         // Buscar la tarifa personalizada del servicio
         $sql = "SELECT cast_monto FROM carga_servicio_tarifa_cliente WHERE clie_id = '$cons_id' AND case_id = '$case_id'";

         $res = mysql_fetch_assoc(mysql_query($sql));

         if ($res != 0 or !empty($res) or $res != null) {
            // Si existe un monto para el cliente/consignee, vamos a reasignar caca_monto
            $caca_monto = $res["cast_monto"];
         }


         //Me inserta nuevos cargos con o sin ITBMS con la carga que le estamos enviando
         $sql = "INSERT INTO carga_cargos (cade_guia, carg_id, case_id, caca_monto, caca_fecha, usua_id, caca_itbms)
         VALUES ('$cade_guia', '$carg_id', '$case_id', '$caca_monto', NOW(), '$usua_id', (SELECT CASE WHEN case_itbms = 1 THEN $caca_monto * 0.07 ELSE 0 END FROM carga_servicios WHERE case_id = '$case_id'))";

         mysql_query($sql);

         if (mysql_errno()) {
            http_response_code(500);
            echo json_encode(["msg" => "Ha ocurrido un error"]);
         }

         // Vamos a actualizar el monto de AIT sacando el peso de la carga
         /**
          * Vamos a buscar el peso total de la carga, luego lo multiplicamos a 0.025 x kg
          */

         $sql = "UPDATE carga_cargos SET caca_monto = (SELECT (SUM(cade_peso) * 0.025) FROM carga_detalles WHERE cade_guia = '$cade_guia' GROUP BY cade_guia) WHERE cade_guia = '$cade_guia' AND case_id = 2"; //case_id = 2 es el ID del AIT 

         mysql_query($sql);

         if (mysql_errno()) {
            http_response_code(500);
            echo json_encode(["msg" => "Ha ocurrido un error"]);
         }

         echo json_encode(["msg" => "Se agegró el servicio", "id" => $cade_id]);
      }

      break;

   case "GET":
      //Verificar el RUC

      $ruc = $_GET["ruc"];
      $tipoContribuyente = $_GET["tipo"];
      $dv = $_GET["dv"];

      if (empty($ruc) || empty($tipoContribuyente) || $tipoContribuyente == 'null') {
         http_response_code(404);
         die(json_encode(["err" => "El RUC o tipo de contribuyente, no puede estar vacío"]));
      }

      $res = $hkApi->verificarRuc($ruc, $tipoContribuyente);

      if ($res->codigo != 200) { // Esto sucede cuando la peticion ha tenido un error de cualquier tipo
         http_response_code(404);
         die(json_encode(["err" => $res->mensaje]));
      }

      // if ($res->infoRuc->dv != $dv) {
      //    http_response_code(404);
      //    die(json_encode(["res" => "DV no válido", "info" => $res->mensaje]));
      // }

      echo json_encode(["res" => "RUC Válido", "info" => $res->mensaje, "datos" => $res->infoRuc]);
      break;

   case "DELETE":
      $_DELETE = json_decode(file_get_contents("php://input"), true);
      $caca_id = $_DELETE["id"];

      $stmt = "DELETE FROM carga_cargos WHERE caca_id = '$caca_id'";
      mysql_query($stmt);
      break;
}
