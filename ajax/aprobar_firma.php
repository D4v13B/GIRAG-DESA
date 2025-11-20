<?php
use PhpOffice\PhpWord\TemplateProcessor;
use Mpdf\Mpdf;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
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

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $redo_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$redo_id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de documento inválido'
        ]);
        exit;
    }

    try {
        // Consultas para obtener información de usuarios
        $sql = "SELECT rd.usuario_encargado_aprobacion, u.usua_mail, u.usua_nombre,u.usua_id
            FROM reportes_documentos rd
            JOIN usuarios u ON u.usua_id = rd.usuario_encargado_aprobacion
            WHERE rd.redo_id = $redo_id";
        $usua_id_encargado_aprobacion = mysql_fetch_assoc(mysql_query($sql));
    
        $sql = "SELECT rd.usua_id_gerente_departamento, u.usua_mail, u.usua_nombre, u.usua_id
            FROM reportes_documentos rd
            JOIN usuarios u ON u.usua_id = rd.usua_id_gerente_departamento
            WHERE rd.redo_id = $redo_id";
        $usua_id_gerente_departamento = mysql_fetch_assoc(mysql_query($sql));
    
        $sql = "SELECT * FROM reportes_documentos WHERE redo_id = $redo_id";
        $documento = mysql_fetch_assoc(mysql_query($sql));
    
       
        // Inicialización de PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";
        $mail->setFrom($smtp_username, 'GIRAG CONTROL DE DOCUMENTOS');
    
        // Preparar plantilla de correo
        $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACION-APROBACION-FIRMA'";
        $plantilla_original = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];
    
        // Usuarios a los que se enviará el correo
        $destinatarios = [
            $usua_id_encargado_aprobacion,
            $usua_id_gerente_departamento
        ];
    
        // Enviar correo a cada destinatario
        $correos_enviados = [];
        foreach ($destinatarios as $destinatario) {
            
            // Enviar notificacion en el sistema
            $usno_mensaje = "Documento en espera por Firma";
            $referencia = "https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=" . $redo_id;
           asignarNotificaciones($destinatario['usua_id'],$usno_mensaje,$referencia);

            $mail_actual = clone $mail;
            
            // Personalizar plantilla para cada destinatario
            $plantilla = str_replace("[USUA_ASIGNADO]", $destinatario["usua_nombre"], $plantilla_original);
            $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $documento["redo_titulo"], $plantilla);
            $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                Firmar Documento
              </a>';
    
            $mail_actual->clearAddresses(); // Limpiar destinatarios anteriores
            $mail_actual->addAddress($destinatario["usua_mail"]);
            $mail_actual->isHTML(true);
            $mail_actual->Subject = 'NOTIFICACION DE DOCUMENTO: ' . $documento["redo_titulo"];
            $mail_actual->Body = $plantilla;
    
            try {
                $mail_actual->send();
                $correos_enviados[] = $destinatario["usua_mail"];
            } catch (Exception $e) {
                // Log del error de envío para este destinatario
                error_log("Error enviando correo a " . $destinatario["usua_mail"] . ": " . $mail_actual->ErrorInfo);
            }
        }
    
        // Respuesta JSON de éxito
        echo json_encode([
            'success' => true,
            'message' => 'Correos enviados correctamente',
            'data' => [
                'correos_enviados' => $correos_enviados,
                'documento' => $documento
            ]
        ]);
    
    } catch (Exception $e) {
        // Respuesta JSON de error
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $e->getMessage()
        ]);
    
    }
}