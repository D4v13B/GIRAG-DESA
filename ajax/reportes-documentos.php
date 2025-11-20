<?php

include '../conexion.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
$tipo_email = 1;
require "mailerConfig.php";

$administrador_caso = $_SESSION["administrador_caso"];
$user_id = $_SESSION["login_user"];
// Página donde se genera el enlace



// Verificar si el usuario no ha iniciado sesión
if (!isset($_SESSION['login_user'])) {
    // Guardar la URL solicitada en una variable de sesión
    $_SESSION['url_requerida'] = 'https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $lastIdReporte;

    // Redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
    exit();
}
switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        // Aquí manejamos los requests GET
        break;
    case "POST":
        if (isset($_POST["redo_titulo"])) {
            $titulo = mysql_real_escape_string($_POST["redo_titulo"]);
            $descripcion = mysql_real_escape_string($_POST["redo_descripcion"]);
            $usuaIdGerentesms = mysql_real_escape_string($_POST["usua_id_gerente_sms"]);
            $comentario = !empty($_POST["comentario"]) ? mysql_real_escape_string($_POST["comentario"]) : "No hay retroalimentación";
            $redgId = mysql_real_escape_string($_POST["redg_id"]);
            $documento_tipo = mysql_real_escape_string($_POST["documento_tipo"]);
            $usuario_gerente_revision = !empty($_POST["usua_id_gerente_revision"]) ? mysql_real_escape_string($_POST["usua_id_gerente_revision"]) : null;
            $usuario_gerente_aprobacion = !empty($_POST["usua_id_gerente_aprobacion"]) ? mysql_real_escape_string($_POST["usua_id_gerente_aprobacion"]) : null;
            $depaId = !empty($_POST["depa_id"]) ? mysql_real_escape_string($_POST["depa_id"]) : null;

            // Verificar si hay gerentes múltiples (para tipo 6)
            $gerentes_multiples = null;
            if ($documento_tipo == '6' && isset($_POST["gerentes_multiples"])) {
                $gerentes_multiples = json_decode($_POST["gerentes_multiples"], true);

                // Verificar que el array de gerentes sea válido
                if (!is_array($gerentes_multiples)) {
                    $gerentes_multiples = null;
                }
            }

            // Imprimir para depuración
            echo "Valor recibido de documento_tipo: $documento_tipo"; // Depuración

            if (!empty($_FILES["documento"]["name"])) {
                $sql = "INSERT INTO reportes_documentos (
                        redo_titulo,
                        redo_descripcion,
                        usua_id_gerente_departamento,
                        depa_id,
                        rede_id,
                        redg_id,
                        usua_id_gerente_sms,
                        redt_id,
                        usuario_encargado_aprobacion
                        
                    ) VALUES (
                        '$titulo',
                        '$descripcion',
                        '$usuario_gerente_revision',
                        '$depaId',
                        1,
                        '$redgId',
                        '$usuaIdGerentesms',
                        '$documento_tipo',
                        '$usuario_gerente_aprobacion'
                    )";

                mysql_query($sql);

                if (mysql_error()) {
                    http_response_code(400);
                    echo "Error en el SQL: " . mysql_error();
                    die();
                }

                $lastIdReporte = mysql_insert_id();

                // Procesar los gerentes múltiples para tipo 6
                if ($documento_tipo == '6' && $gerentes_multiples) {
                    echo "Procesando gerentes múltiples para política..."; // Para depuración

                    foreach ($gerentes_multiples as $gerenteId) {
                        // Validar y escapar el ID del gerente
                        $gerenteId = intval($gerenteId); // Asegurarse de que sea un entero

                        if ($gerenteId > 0) { // Verificar que sea un ID válido
                            $sql_gerente = "INSERT INTO reportes_documentos_gerentes (
                                    redo_id, 
                                    usua_id_gerente
                                ) VALUES (
                                    '$lastIdReporte',
                                    '$gerenteId'
                                )";

                            mysql_query($sql_gerente);

                            if (mysql_error()) {
                                echo "Error al insertar gerente $gerenteId: " . mysql_error();
                            } else {
                                echo "Gerente $gerenteId insertado correctamente.";
                            }
                        }
                    }
                }
                $manualReferencia = time() . "-" . basename($_FILES["documento"]["name"]);

                if (move_uploaded_file($_FILES["documento"]["tmp_name"], "../manuales-uso/" . $manualReferencia)) {
                    $sql = "INSERT INTO reportes_documentos_bitacora (
                        redb_ref,
                        redb_fecha,
                        redo_id
                    ) VALUES (
                        '$manualReferencia',
                        NOW(),
                        '$lastIdReporte'
                    )";

                    mysql_query($sql);
                    echo "Todas las operaciones han sido realizadas con éxito";
                } else {
                    http_response_code(400);
                    echo "No podemos subir el archivo, verifique.";
                }
            } else {
                http_response_code(400);
                echo "No existe un archivo al cual darle seguimiento.";
            }
            // Detección del tipo de documento
            if ($documento_tipo == 6) {
                echo "Procesando tipo de documento 6"; // Para depuración
                // Consulta para obtener gerentes y presidente

                $sql_gerentes_presidente = "SELECT u.usua_mail,u.usua_nombre FROM usuarios u INNER JOIN 
                    reportes_documentos_gerentes rdg ON rdg.usua_id_gerente = u.usua_id
                WHERE 
                    rdg.redo_id = '$lastIdReporte'
            ";


                $result_gerentes_presidente = mysql_query($sql_gerentes_presidente);

                // Plantilla de correo para POLITICA
                $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'POLITICA'";
                $plantilla = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

    
                $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $lastIdReporte . '" 
                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                Aceptar Política
              </a>';

              while ($row = mysql_fetch_assoc($result_gerentes_presidente)) {
                $mail = new PHPMailer(true);
            
                try {
                    $mail->isSMTP();
                    $mail->Host = $smtp_host;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp_username;
                    $mail->Password = $smtp_password;
                    $mail->SMTPSecure = "tls";
                    $mail->Port = 587;
                    $mail->CharSet = "UTF-8";
            
                    $mail->setFrom($smtp_username, 'GIRAG CONTROL DE DOCUMENTOS');
                    $mail->addAddress($row["usua_mail"], $row["usua_nombre"]);
            
                    // Reemplazo dinámico de nombre por usuario
                    $plantillaPersonalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                    $plantillaPersonalizada = str_replace("[DESCRIPCION]", $descripcion, $plantillaPersonalizada);
                    $plantillaPersonalizada = str_replace("[USUA_ASIGNADO]", $row["usua_nombre"], $plantillaPersonalizada);
            
                    $mail->isHTML(true);
                    $mail->Subject = 'Notificación de Nueva Política: ' . $titulo;
                    $mail->Body = $plantillaPersonalizada;
            
                    $mail->send();
                } catch (Exception $e) {
                    echo "Error al enviar a " . $row["usua_mail"] . ": {$mail->ErrorInfo}";
                }
            }
            
            } else {
                // Obtenemos la plantilla base
                $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACION-DOCUMENTO'";
                $plantilla_base = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];
            
                // Consulta de usuarios con usca_id 2, 3 y 4
                $sql_usuarios = "SELECT usua_nombre, usua_mail FROM usuarios WHERE usca_id IN (2, 3, 4)";
                $result_usuarios = mysql_query($sql_usuarios);
            
                // Recorremos cada usuario y enviamos correo personalizado
                while ($usuario = mysql_fetch_assoc($result_usuarios)) {
                    $plantilla_personalizada = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_base);
                    $plantilla_personalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personalizada);
                    $plantilla_personalizada = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personalizada);
                    $plantilla_personalizada .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $lastIdReporte . '" 
                        style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                        Ver Documento
                    </a>';
            
                    $mail = new PHPMailer(true);
            
                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtp_host;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp_username;
                        $mail->Password = $smtp_password;
                        $mail->SMTPSecure = "tls";
                        $mail->Port = 587;
                        $mail->CharSet = "UTF-8";
            
                        $mail->setFrom($smtp_username, 'GIRAG CONTROL DE DOCUMENTOS');
                        $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
            
                        $mail->isHTML(true);
                        $mail->Subject = 'NOTIFICACIÓN DE DOCUMENTO: ' . $titulo;
                        $mail->Body = $plantilla_personalizada;
            
                        $mail->send();
                    } catch (Exception $e) {
                        echo "Error al enviar a {$usuario['usua_mail']}: {$mail->ErrorInfo}";
                    }
                }
            
                echo 'Notificaciones enviadas exitosamente a usuarios con usca_id 2, 3 y 4.';
            }
        }
    }
            