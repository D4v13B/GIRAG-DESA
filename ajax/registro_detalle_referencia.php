<?php
//REGISTRA LAS REFERENCIAS, Y CARGA LA TABLA DE REFERENCIAS.
include('../conexion.php');
include('../funciones.php');
session_start();

require __DIR__ . "/../vendor/autoload.php";
require '../vendor/autoload.php';

$tipo_email = 1;
require "mailerConfig.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// Obtener el ID de la inspección desde GET o POST
$id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;
if ($id === 0 && isset($_POST['insp_id'])) {
    $id = intval($_POST['insp_id']);
}

if ($id === 0) {
    echo json_encode(["status" => "error", "message" => "ID de inspección no válido"]);
    exit;
}

$usuario = $_SESSION['login_user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Asegurarse de que los campos existen antes de usarlos
    if (isset($_POST['nombre']) && isset($_FILES['referencia'])) {
        // Asegurarse de que el nombre es una cadena y no un array
        $nombreReferencia = $_POST['nombre']; // Se espera que sea una cadena
        $referenciasArchivos = $_FILES['referencia'];
        $accionesCorrectivasArchivos = $_FILES['accion_correctiva'];
        $comentario = $_POST['comentario']; // Se espera que sea una cadena
        
        // Corregir la variable para usar directamente $_POST['usua_id']
        $usua_id = isset($_POST['usua_id']) ? $_POST['usua_id'] : '';

        // Validar que el ID de usuario no esté vacío
        if (empty($usua_id)) {
            echo json_encode(["status" => "error", "message" => "ID de usuario no válido"]);
            exit;
        }

        // Insertar referencia en la tabla inspecciones_referencias
        $sqlInsertReferencia = "INSERT INTO inspecciones_referencias (inre_nombre, insp_id, inre_comentario, inre_usua_id) VALUES ('$nombreReferencia', '$id', '$comentario', '$usua_id')";
        if (mysql_query($sqlInsertReferencia)) {
            // Obtener el ID de la referencia insertada
            $inre_id = mysql_insert_id();

            // Procesar las imágenes de referencia
            if (isset($referenciasArchivos['name'])) {
                for ($i = 0; $i < count($referenciasArchivos['name']); $i++) {
                    $tmpFilePath = $referenciasArchivos['tmp_name'][$i];
                    if ($tmpFilePath != "") {
                        $newFilePath = "../img/referencias/" . $id . "-" . $referenciasArchivos['name'][$i];
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            // Obtener solo el nombre del archivo
                            $fileName = basename($newFilePath);
                            // Insertar en inspecciones_fotos
                            $sqlInsertFoto = "INSERT INTO inspecciones_fotos (insp_id, info_ruta, info_tipo, inre_id) VALUES ('$id', '$fileName', '1', '$inre_id')";
                            mysql_query($sqlInsertFoto);
                        }
                    }
                }
            }
            
            // Procesar las imágenes de acción correctiva. 
            // Nota: Este campo es opcional, verifico que exista y tenga archivos antes de procesarlo. 
            if (isset($_FILES['accion_correctiva']) && is_array($_FILES['accion_correctiva']['name']) && count($_FILES['accion_correctiva']['name']) > 0 && $_FILES['accion_correctiva']['name'][0] != "") {
                $accionesCorrectivasArchivos = $_FILES['accion_correctiva'];

                for ($i = 0; $i < count($accionesCorrectivasArchivos['name']); $i++) {
                    $tmpFilePath = $accionesCorrectivasArchivos['tmp_name'][$i];
                    if ($tmpFilePath != "") {
                        $newFilePath = "../img/referencias/" . $id . "-" . $accionesCorrectivasArchivos['name'][$i];
                        if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                            // Obtener solo el nombre del archivo
                            $fileName = basename($newFilePath);
                            // Insertar en inspecciones_fotos con tipo 'accion_correctiva'
                            $sqlInsertFoto = "INSERT INTO inspecciones_fotos (insp_id, info_ruta, info_tipo, inre_id) VALUES ('$id', '$fileName', '2', '$inre_id')";
                            mysql_query($sqlInsertFoto);
                        }
                    }
                }
            }

            echo json_encode(["status" => "success", "message" => "Referencias y fotos guardadas exitosamente"]);
            
                // Obtener los datos del usuario destinatario (quien recibe el correo)
                $stmt_usuario = "SELECT usua_mail, usua_nombre FROM usuarios WHERE usua_id = '$usua_id'";
                $resultado_usuario = mysql_query($stmt_usuario);
                $datos_usuario = mysql_fetch_assoc($resultado_usuario);
           
                if ($datos_usuario) {
                    $correo_destino = $datos_usuario["usua_mail"];
                    $nombre_destino = $datos_usuario["usua_nombre"];
           
                    // Obtener plantilla desde la tabla contratos
                    $nombre_plantilla = 'REFERENCIA';
                    $stmt_plantilla = "SELECT cont_detalle FROM contratos WHERE cont_nombre = '$nombre_plantilla'";
                    $resultado_plantilla = mysql_query($stmt_plantilla);
                    
                    // Verificar si se encontró la plantilla
                    if ($row_plantilla = mysql_fetch_assoc($resultado_plantilla)) {
                        $plantilla_base = $row_plantilla["cont_detalle"];
                    } else {
                        // Plantilla predeterminada en caso de no encontrar en la BD
                        $plantilla_base = "
                        <p>Hola [USUA_ASIGNADO],</p>
                        <p>Se ha registrado una nueva referencia:</p>
                        <p><strong>Número de reporte:</strong> [NUMERO_REPORTE]</p>
                        <p><strong>Título:</strong> [TITULO]</p>
                        <p><strong>Descripción:</strong> [DESCRIPCION]</p>";
                        
                        // Registrar advertencia en el log
                        error_log("Plantilla 'REFERENCIA' no encontrada en la tabla contratos");
                    }
           
                    // Variables que vas a insertar en la plantilla
                    $titulo = isset($nombreReferencia) ? $nombreReferencia : '';
                    $descripcion = isset($comentario) ? $comentario : '';
                    $numero_reporte = isset($id) ? $id : '';
           
                    // Reemplazos en la plantilla
                    $plantilla_personal = str_replace("[USUA_ASIGNADO]", $nombre_destino, $plantilla_base);
                    $plantilla_personal = str_replace("[NUMERO_REPORTE]", $numero_reporte, $plantilla_personal);
                    $plantilla_personal = str_replace("[TITULO]", $titulo, $plantilla_personal);
                    $plantilla_personal = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personal);
           
                    // Puedes agregar un botón o enlace al final si lo deseas
                    $plantilla_personal .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=inspecciones_detalles&insp_id=' . $numero_reporte . '" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">Ver Reporte</a>';
           
                    // Configurar PHPMailer
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
                    $mail->addAddress($correo_destino, $nombre_destino);
           
                    // Asunto del correo
                    $mail->Subject = "Nueva referencia registrada";
           
                    // Cuerpo del correo con la plantilla personalizada
                    $mail->isHTML(true);
                    $mail->Body = $plantilla_personal;
           
                    if ($mail->send()) {
                        $response["email_sent"] = true;
                    }
                }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al insertar referencia: " . mysql_error()]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos para procesar las referencias"]);
    }
    
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Asegúrate de que el ID se obtenga de la URL
    $id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;

    if ($id > 0) {
        // Consultar las referencias y fotos asociadas a la inspección
        $sql = "SELECT 
    ir.inre_nombre, 
    inf.info_ruta, 
    inf.info_tipo, 
    ir.inre_comentario,
    ir.inre_usua_id,
    u.usua_nombre
FROM 
    inspecciones_referencias ir
LEFT JOIN 
    inspecciones_fotos inf ON ir.inre_id = inf.inre_id
LEFT JOIN 
    usuarios u ON ir.inre_usua_id = u.usua_id
WHERE 
    ir.insp_id = '$id'";
        
        // Suponiendo que ya tienes la conexión mysql establecida como $connection
        $result = mysql_query($sql);

        $referencias = [];
        if ($result) {
            while ($row = mysql_fetch_assoc($result)) {
                $referencias[] = [
                    'nombre' => $row['inre_nombre'],
                    'ruta' => $row['info_ruta'],
                    'tipo' => $row['info_tipo'],
                    'comentario' => $row['inre_comentario'],
                    'usua_nombre' => $row['usua_nombre'] 
                ];
            }
            echo json_encode(["status" => "success", "data" => $referencias]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al consultar las referencias: " . mysql_error()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ID de inspección inválido."]);
    }
    exit;
}
?>