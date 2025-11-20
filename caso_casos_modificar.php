<?php 
include('conexion.php');
include('funciones.php');
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$tipo_email = 1;
require "ajax/mailerConfig.php";

$id = $_GET['id'];

$m_caso_descripcion = $_POST['m_caso_descripcion'];
$m_caes_id = $_POST['m_caes_id'];
$m_depa_id = $_POST['m_depa_id'];
$m_cati_id = $_POST['m_cati_id'];
$m_inso_id = $_POST['m_inso_id'];
$m_inpr_id = $_POST['m_inpr_id'];
$m_ubicacion = $_POST["m_ubicacion"];
$m_imec_id = $_POST['m_imec_id'];
$m_impe_id = $_POST['m_impe_id'];
$m_imma_id = $_POST['m_imma_id'];
$m_equi_id = $_POST['m_equi_id'];
$m_caso_fecha = $_POST['m_caso_fecha'];
$m_caso_nota = $_POST['m_caso_nota'];
$m_usua_id_asignado = $_POST['m_usua_id_asignado'];
$m_depa_id_asignado = $_POST["m_depa_id_asignado"];
$m_cacl_id = $_POST['m_cacl_id'];
$m_cacd_id = $_POST['m_cacd_id'];
$m_referencia = $_POST['m_referencia'];
$m_proc_id = $_POST["m_proc_id"];
$m_depa_id_quien_reporta = $_POST["m_depa_id_quien_reporta"];
$m_caso_externo = $_POST["m_caso_externo"];

// Usuarios de revisión
$m_usuario_revisado = $_POST["m_usuario_revisado"];
$m_usuario_revisado2 = $_POST["m_usuario_revisado2"];
$m_usuario_revisado3 = $_POST["m_usuario_revisado3"];

// Usuarios de aprobación
$m_usuario_aprobado = $_POST["m_usuario_aprobado"];
$m_usuario_aprobado2 = $_POST["m_usuario_aprobado2"];
$m_usuario_aprobado3 = $_POST["m_usuario_aprobado3"];

// Contar usuarios válidos en revisión
$usuarios_revisados = array_filter([
    $m_usuario_revisado, $m_usuario_revisado2, $m_usuario_revisado3
]);
$cantidad_usua_firmas_revisado = count($usuarios_revisados);

// Contar usuarios válidos en aprobación
$usuarios_aprobados = array_filter([
    $m_usuario_aprobado, $m_usuario_aprobado2, $m_usuario_aprobado3
]);
$cantidad_usua_firmas_aprobado = count($usuarios_aprobados);

// Actualizar el caso
$qsql = "UPDATE casos SET
    caso_descripcion='$m_caso_descripcion',  
    caes_id='$m_caes_id',
    depa_id='$m_depa_id',
    cati_id='$m_cati_id',
    inso_id='$m_inso_id',
    inpr_id='$m_inpr_id',
    caso_ubicacion='$m_ubicacion',
    imec_id='$m_imec_id',
    impe_id='$m_impe_id',
    imma_id='$m_imma_id',
    equi_id='$m_equi_id',
    caso_fecha='$m_caso_fecha',
    caso_nota='$m_caso_nota',
    usua_id_asignado='$m_usua_id_asignado',
    depa_id_asignado='$m_depa_id_asignado',
    cacl_id='$m_cacl_id',
    cacd_id='$m_cacd_id',
    caso_referencia='$m_referencia',
    proc_id='$m_proc_id',
    depa_id_quien_reporta='$m_depa_id_quien_reporta',
    caso_externo='$m_caso_externo',
    usua_id_encargado_revision='$m_usuario_revisado',
    usua_id_encargado_revision2='$m_usuario_revisado2',
    usua_id_encargado_revision3='$m_usuario_revisado3',
    usua_id_encargado_aprobacion='$m_usuario_aprobado',
    usua_id_encargado_aprobacion2='$m_usuario_aprobado2',
    usua_id_encargado_aprobacion3='$m_usuario_aprobado3',
    cantidad_usua_firmas_aprobado='$cantidad_usua_firmas_aprobado',
    cantidad_usua_firmas_revisado='$cantidad_usua_firmas_revisado'
    WHERE caso_id='$id'";

mysql_query($qsql);

if ($current_case['usua_id_asignado'] != $m_usua_id_asignado) {
    try {
        // Get new assigned user's information
        $sql = "SELECT usua_mail, usua_nombre FROM usuarios WHERE usua_id = '$m_usua_id_asignado'";
        $result = mysql_query($sql);
        $new_user = mysql_fetch_assoc($result);
        
        // Verify we have the user's email
        if (empty($new_user['usua_mail'])) {
            throw new Exception("User email not found for ID: $m_usua_id_asignado");
        }

        // Get email template
        $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NUEVO-CASO'";
        $result = mysql_query($stmt);
        if (!$result) {
            throw new Exception("Error fetching email template: " . mysql_error());
        }
        $template_row = mysql_fetch_assoc($result);
        if (!$template_row || empty($template_row["cont_detalle"])) {
            throw new Exception("Email template 'NUEVO-CASO' not found or empty");
        }
        $plantilla_original = $template_row["cont_detalle"];

        // Verify SMTP configuration variables exist
        if (!isset($smtp_host) || !isset($smtp_username) || !isset($smtp_password)) {
            throw new Exception("SMTP configuration variables not set");
        }

        // Initialize PHPMailer with debug mode
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";
       
        // Set sender and recipient
        $mail->setFrom($smtp_username, 'SISTEMA GIRAG - NOTIFICACIÓN');
        $mail->addAddress($new_user["usua_mail"]);
        
        // Customize template and set content
        $plantilla = str_replace("[CASO_ID]", $id, $plantilla_original);
        $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=detalle-caso&caso=' . $id . '" 
                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                Revisar Caso
              </a>';
        $mail->isHTML(true);
        $mail->Subject = 'CASO ASIGNADO: #' . $id;
        $mail->Body = $plantilla;
        
        // Attempt to send
        if (!$mail->send()) {
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }

        // Add system notification only if email was sent successfully
        $usno_mensaje = "Nuevo caso asignado #" . $id;
        $referencia = "https://giraglogicdesa.girag.aero/index.php?p=detalle-caso&caso=" . $id;
        asignarNotificaciones($m_usua_id_asignado, $usno_mensaje, $referencia);
        
        // Log success
        error_log("Email sent successfully to " . $new_user["usua_mail"] . " for case #" . $id);
        
    } catch (Exception $e) {
        // Log the specific error
        error_log("Error in email notification process: " . $e->getMessage());
        // You might want to add some user feedback here
        // echo "Error sending notification: " . $e->getMessage();
    }
}

$usuarios_revisar = [
    'usua_id_encargado_revision' => $m_usuario_revisado,
    'usua_id_encargado_revision2' => $m_usuario_revisado2,
    'usua_id_encargado_revision3' => $m_usuario_revisado3,
    'usua_id_encargado_aprobacion' => $m_usuario_aprobado,
    'usua_id_encargado_aprobacion2' => $m_usuario_aprobado2,
    'usua_id_encargado_aprobacion3' => $m_usuario_aprobado3
];

foreach ($usuarios_revisar as $campo => $nuevo_id) {
    if ($current_case[$campo] != $nuevo_id && !empty($nuevo_id)) {
        try {
            $sql = "SELECT usua_mail, usua_nombre FROM usuarios WHERE usua_id = '$nuevo_id'";
            $result = mysql_query($sql);
            $new_user = mysql_fetch_assoc($result);

            if (empty($new_user['usua_mail'])) {
                throw new Exception("Correo no encontrado para el ID: $nuevo_id ($campo)");
            }

            $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NUEVO-CASO'";
            $result = mysql_query($stmt);
            if (!$result) {
                throw new Exception("Error obteniendo plantilla: " . mysql_error());
            }
            $template_row = mysql_fetch_assoc($result);
            if (!$template_row || empty($template_row["cont_detalle"])) {
                throw new Exception("Plantilla 'NUEVO-CASO' vacía o no encontrada");
            }

            $plantilla_original = $template_row["cont_detalle"];

            if (!isset($smtp_host) || !isset($smtp_username) || !isset($smtp_password)) {
                throw new Exception("Configuración SMTP incompleta");
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;
            $mail->CharSet = "UTF-8";

            $mail->setFrom($smtp_username, 'SISTEMA GIRAG - NOTIFICACIÓN');
            $mail->addAddress($new_user["usua_mail"]);

            $plantilla = str_replace("[CASO_ID]", $id, $plantilla_original);
            $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=detalle-caso&caso=' . $id . '" 
                    style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                    Revisar Caso
                  </a>';
            $mail->isHTML(true);
            $mail->Subject = 'CASO ASIGNADO: #' . $id;
            $mail->Body = $plantilla;

            if (!$mail->send()) {
                throw new Exception("Error enviando correo a " . $new_user["usua_mail"]);
            }

            $usno_mensaje = "Nuevo caso asignado para revisión/aprobación #" . $id;
            $referencia = "https://giraglogicdesa.girag.aero/index.php?p=detalle-caso&caso=" . $id;
            asignarNotificaciones($nuevo_id, $usno_mensaje, $referencia);

            error_log("Correo enviado a " . $new_user["usua_mail"] . " para $campo en el caso #$id");

        } catch (Exception $e) {
            error_log("Error enviando notificación para $campo: " . $e->getMessage());
        }
    }
}

?>