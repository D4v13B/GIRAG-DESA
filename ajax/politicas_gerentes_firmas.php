<?php
include '../conexion.php';
include '../funciones.php';
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . "/../vendor/autoload.php";
require '../vendor/autoload.php';
$tipo_email = 1;
require "mailerConfig.php";



$reporte_id = isset($_GET['reporte_id']) ? intval($_GET['reporte_id']) : 0;

$response = ['pendientes' => 0, 'nombres' => []];

if ($reporte_id > 0) {
    $consulta_tipo = mysql_query("SELECT redt_id FROM reportes_documentos WHERE redo_id = $reporte_id");
    if ($fila_tipo = mysql_fetch_assoc($consulta_tipo)) {
        if ($fila_tipo['redt_id'] == 6) {
            $query_gerentes = mysql_query("
                SELECT usua_id_gerente 
                FROM reportes_documentos_gerentes 
                WHERE redo_id = $reporte_id
            ");

            $pendientes = 0;

            while ($gerente = mysql_fetch_assoc($query_gerentes)) {
                $gerente_id = $gerente['usua_id_gerente'];

                $consulta_bitacora = mysql_query("
                    SELECT COUNT(*) AS existe 
                    FROM reportes_documentos_bitacora 
                    WHERE redo_id = $reporte_id 
                    AND redb_procesado_por = $gerente_id
                ");

                $resultado = mysql_fetch_assoc($consulta_bitacora);

                if ($resultado['existe'] == 0) {
                    $pendientes++;

                    // Obtener el nombre del gerente pendiente
                    $consulta_nombre = mysql_query("SELECT usua_nombre FROM usuarios WHERE usua_id = $gerente_id");
                    if ($fila_nombre = mysql_fetch_assoc($consulta_nombre)) {
                        $response['nombres'][] = $fila_nombre['usua_nombre'];
                    }
                }
            }

            $response['pendientes'] = $pendientes;
        }
    }
}
// Si no hay pendientes, enviar correo al usuario con usca_id = 27
if ($response['pendientes'] == 0&&$fila_tipo['redt_id'] == 6) {
    // Obtener datos del documento
    $query_doc = mysql_query("SELECT redo_titulo, redo_descripcion FROM reportes_documentos WHERE redo_id = $reporte_id");
    $info_doc = mysql_fetch_assoc($query_doc);
    $titulo = $info_doc['redo_titulo'];
    $descripcion = $info_doc['redo_descripcion'];

    // Obtener plantilla del correo
    $stmt = mysql_query("SELECT cont_detalle FROM contratos WHERE cont_nombre = 'FIRMA_PRESIDENTE'");
    if ($stmt && mysql_num_rows($stmt) > 0) {
        $plantilla_base = mysql_fetch_assoc($stmt)['cont_detalle'];

        // Obtener el usuario con usca_id = 27
        $usuario_q = mysql_query("SELECT usua_nombre, usua_mail FROM usuarios WHERE usca_id = 27 LIMIT 1");
        if ($usuario_q && mysql_num_rows($usuario_q) > 0) {
            $usuario = mysql_fetch_assoc($usuario_q);
            $nombre = $usuario['usua_nombre'];
            $correo = $usuario['usua_mail'];

            if (!empty($correo)) {
                // Personalizar plantilla
                $plantilla = str_replace("[USUA_ASIGNADO]", $nombre, $plantilla_base);
                $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                $plantilla = str_replace("[DESCRIPCION]", $descripcion, $plantilla);
                $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $reporte_id . '" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">Ver Documento</a>';

               

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = $smtp_host;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp_username;
                    $mail->Password = $smtp_password;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom($smtp_username, 'GIRAG CONTROL DE DOCUMENTOS');
                    $mail->addAddress($correo, $nombre);

                    $mail->isHTML(true);
                    $mail->Subject = 'TODOS LOS GERENTES HAN ACEPTADO: ' . $titulo;
                    $mail->Body = $plantilla;

                    $mail->send();
                    // Opcional: guardar log o mensaje si quieres
                } catch (Exception $e) {
                    logError("Error al enviar notificación final: " . $mail->ErrorInfo);
                }
            }
        }
    }
}

echo json_encode($response);
?>
