<?php
include '../conexion.php';
include '../funciones.php';
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verificar si el usuario está autenticado
if (!isset($_SESSION['login_user'])) {
    // Guardar la URL solicitada en una variable de sesión
    $_SESSION['url_requerida'] = 'https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $_GET["id"];
    // Redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
    exit();
}

require __DIR__ . "/../vendor/autoload.php";
require '../vendor/autoload.php';
$tipo_email = 1;
require "mailerConfig.php";


function getExtension($str)
{
    $i = strrpos($str, ".");
    if (!$i) {
        return "";
    }
    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    return $ext;
}

// Capturar errores y guardarlos en un log
function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . "Error en reportes-detalles-uploader.php: " . $message . PHP_EOL, 3, "../logs/upload_errors.log");
}

// Validar que tenemos ID
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    logError("ID no proporcionado");
    echo "Error: ID no proporcionado";
    exit();
}

// Validar que tenemos el archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
    logError("Error en la subida del archivo: " . $_FILES['file']['error']);
    echo "Error en la subida del archivo";
    exit();
}

try {
    $id = $_GET["id"];
    $usuario = $_SESSION['login_user'];

    // Procesar la carga del archivo
    $filename = stripslashes($_FILES['file']['name']);
    $filename = preg_replace('/[^A-Za-z0-9.\-]/', '', $filename);
    $extension = getExtension($filename);

    // Insertar en la base de datos
    $insertar = "INSERT INTO reportes_documentos_bitacora(redb_fecha, redo_id, rede_id, redb_procesado_por) 
                VALUES (NOW(), '$id', 5, '$usuario')";
    $result = mysql_query($insertar);

    if (!$result) {
        logError("Error al insertar en la base de datos: " . mysql_error());
        echo "Error al insertar en la base de datos";
        exit();
    }

    $maximo = mysql_insert_id();
    $target_path = "../manuales-uso/$maximo" . "_";
    $target_path = $target_path . preg_replace('/[^A-Za-z0-9.\-]/', '', $_FILES['file']['name']);

    // Verificar directorio de destino
    $upload_dir = "../manuales-uso/";
    if (!is_dir($upload_dir)) {
        logError("El directorio de destino no existe: " . $upload_dir);
        echo "Error: El directorio de destino no existe";
        exit();
    }

    if (!is_writable($upload_dir)) {
        logError("El directorio de destino no tiene permisos de escritura: " . $upload_dir);
        echo "Error: El directorio de destino no tiene permisos de escritura";
        exit();
    }

    // Mover el archivo subido
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        // Actualizar ruta del archivo en la base de datos
        $qsql = "UPDATE reportes_documentos_bitacora SET redb_ref='$maximo" . "_$filename' WHERE redb_id = $maximo";
        mysql_query($qsql);

        try {
            // Obtener rol del usuario actual
            $sql_usuario_sesion = "SELECT usca_id FROM usuarios WHERE usua_id = '$usuario'";
            $result_usuario_sesion = mysql_query($sql_usuario_sesion);

            if (!$result_usuario_sesion) {
                logError("Error al consultar rol del usuario: " . mysql_error());
                echo "Error al consultar información del usuario";
                exit();
            }

            $usca_id_sesion = mysql_fetch_assoc($result_usuario_sesion)["usca_id"];

            // Obtener información del documento y gerentes directamente
            $sql_documento = "SELECT rd.*, 
                             rd.redo_titulo as titulo, 
                             rd.redo_descripcion as descripcion,
                             rd.usua_id_gerente_sms as gerente_sms,
                             rd.usua_id_gerente_departamento as gerente_departamento,
                             rd.usuario_encargado_aprobacion as gerente_encargado_aprobacion
                      FROM reportes_documentos rd
                      WHERE rd.redo_id = '$id'";

            $result_documento = mysql_query($sql_documento);

            if (!$result_documento || mysql_num_rows($result_documento) == 0) {
                logError("No se encontró información del documento ID: $id");
                echo "Error: No se encontró información del documento";
                exit();
            }

            $documento_info = mysql_fetch_assoc($result_documento);
            $titulo = $documento_info['titulo'];
            $descripcion = $documento_info['descripcion'];
            $gerente_sms = $documento_info['gerente_sms'];
            $gerente_departamento = $documento_info['gerente_departamento'];
            $gerente_encargado_aprobacion = $documento_info['gerente_encargado_aprobacion'];

            // Obtener plantilla de correo
            $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'ACTUALIZACION-DOCUMENTO'";
            $result_plantilla = mysql_query($stmt);

            if (!$result_plantilla || mysql_num_rows($result_plantilla) == 0) {
                logError("No se encontró la plantilla de correo ACTUALIZACION-DOCUMENTO");
                echo "Documento cargado, pero no se encontró la plantilla de correo";
                exit();
            }

            $plantilla_base = mysql_fetch_assoc($result_plantilla)["cont_detalle"];


            // Obtener información básica del usuario
            $destinatario_id = null;

            // Modificamos la consulta para obtener más información de depuración
            $sql_destinatarios = "SELECT usua_id, usua_nombre, usua_mail, usca_id FROM usuarios 
                     WHERE (usca_id IN (2, 3, 4) OR usua_id = '$gerente_departamento')";
            $result_destinatarios = mysql_query($sql_destinatarios);

            // Verificar si hay errores en la consulta
            if (!$result_destinatarios) {
                logError("Error en consulta de destinatarios: " . mysql_error());
                echo "Error al obtener destinatarios: " . mysql_error();
                exit();
            }



            if ($result_destinatarios && mysql_num_rows($result_destinatarios) > 0) {
                $enviados = 0;
                $errores = 0;

                while ($destinatario_info = mysql_fetch_assoc($result_destinatarios)) {
                    if (empty($destinatario_info["usua_mail"])) continue;

                    $plantilla_personal = str_replace("[USUA_ASIGNADO]", $destinatario_info["usua_nombre"], $plantilla_base);
                    $plantilla_personal = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personal);
                    $plantilla_personal = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personal);
                    $plantilla_personal .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $id . '" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">Ver Documento</a>';
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
                        $mail->addAddress($destinatario_info["usua_mail"], $destinatario_info["usua_nombre"]);
            
                        $mail->isHTML(true);
                        $mail->Subject = 'NOTIFICACIÓN DE DOCUMENTO: ' . $titulo;
                        $mail->Body = $plantilla_personal;
                        if ($mail->send()) {
                            $enviados++;
                        } else {
                            $errores++;
                            logError("Error al enviar a {$destinatario_info['usua_mail']}");
                        }
                    } catch (Exception $e) {
                        $errores++;
                        logError("Excepción al enviar a {$destinatario_info['usua_mail']}: " . $e->getMessage());
                    }
                }

                if ($errores > 0) {
                    echo "Documento cargado. Se enviaron $enviados notificaciones, pero hubo $errores errores.";
                } else {
                    echo "Documento cargado y notificaciones enviadas exitosamente a todos los destinatarios.";
                }
            } else {
                echo "Documento cargado exitosamente. No se encontraron destinatarios para notificar.";
            }

     
        } catch (Exception $e) {
            logError("Error en el proceso de notificación: " . $e->getMessage());
            echo "Documento cargado exitosamente. No se pudieron enviar notificaciones.";
        }
    } else {
        logError("Error al mover el archivo: " . error_get_last()['message']);
        echo "Error al cargar el archivo. Por favor, inténtelo nuevamente.";
    }
} catch (Exception $e) {
    logError("Excepción general: " . $e->getMessage());
    echo "Error en el procesamiento de la solicitud. Por favor, inténtelo nuevamente.";
}
