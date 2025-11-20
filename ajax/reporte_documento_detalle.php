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

if (!isset($_SESSION['login_user'])) {
    // Guardar la URL solicitada en una variable de sesión
    $_SESSION['url_requerida'] = 'https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $lastIdReporte;

    // Redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
    exit();
}
require __DIR__ . "/../vendor/autoload.php";
require '../vendor/autoload.php';
$tipo_email = 1;
require "mailerConfig.php";
// error_reporting(0);
$user_id = $_SESSION["login_user"];

$sql = "SELECT * FROM usuarios WHERE usti_id = 13";
$presidente = mysql_fetch_assoc(mysql_query($sql));
switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if (isset($_GET["id_bitacora"])) {


            $redb_id = $_GET["id_bitacora"];
            $sql = "SELECT * FROM reportes_documentos_bitacora WHERE redb_id = $redb_id";
            $res = mysql_query($sql);
            if (mysql_num_rows($res) > 0) {
                $fila = mysql_fetch_assoc($res);
                echo json_encode($fila);
            } else {
                echo json_encode(array("error" => "No se encontraron registros para el ID de bitácora proporcionado"));
            }
        } else {
            $redo_id = $_GET["redo_id"];
            // Consulta para obtener información del documento y sus usuarios relacionados
            $sql = "
    SELECT 
        rd.redo_id, 
        rd.usua_id_gerente_sms, 
        rd.redt_id,
        rd.usua_id_gerente_departamento, 
        rd.usuario_encargado_aprobacion,
        u1.usua_nombre AS nombre_gerente_sms,
        u2.usua_nombre AS nombre_gerente_departamento,
        u3.usua_nombre AS nombre_encargado_aprobacion
    FROM 
        reportes_documentos rd
    LEFT JOIN 
        usuarios u1 ON rd.usua_id_gerente_sms = u1.usua_id
    LEFT JOIN 
        usuarios u2 ON rd.usua_id_gerente_departamento = u2.usua_id
    LEFT JOIN 
        usuarios u3 ON rd.usuario_encargado_aprobacion = u3.usua_id
    WHERE 
        rd.redo_id = $redo_id
    ";

            $result = mysql_query($sql);
            $usuarios = mysql_fetch_assoc($result);
            $gerente_sms = $usuarios['usua_id_gerente_sms'];
            $gerente_departamento = $usuarios['usua_id_gerente_departamento'];
            $gerente_encargado_aprobacion = $usuarios['usuario_encargado_aprobacion'];

            // Consulta para obtener las bitácoras del documento
            $sql = "SELECT rd.*, 
        rde.rede_nombre AS estado, 
        u.usua_nombre AS nombre_procesador, 
        redo.usuario_encargado_aprobacion,
        redo.rede_id AS redo_rede_id,
        rd.redb_firmado,
        s.sino_nombre
 FROM reportes_documentos_bitacora rd
 LEFT JOIN reportes_documentos_estado rde ON rd.rede_id = rde.rede_id
 LEFT JOIN usuarios u ON rd.redb_procesado_por = u.usua_id
 LEFT JOIN reportes_documentos redo ON rd.redo_id = redo.redo_id
 LEFT JOIN sino s ON rd.redb_firmado = s.sino_id
 WHERE rd.redo_id = $redo_id
 ORDER BY rd.redb_id ASC";
            $res = mysql_query($sql);

            $response_html = "";
            $ultima_bitacora = null;

            // Primero recorremos para obtener la última bitácora
            while ($fila = mysql_fetch_assoc($res)) {
                $ultima_bitacora = $fila;
            }
            $ultimo_estado = $ultima_bitacora["rede_id"];
            $ultimo_procesador = $ultima_bitacora["redb_procesado_por"];
            $usuario_actual = $_SESSION["login_user"];

            // Reiniciamos la consulta
            $res = mysql_query($sql);

            while ($fila = mysql_fetch_assoc($res)) {
                $estado_id = $fila["rede_id"];
                $es_ultima_bitacora = ($fila["redb_id"] == $ultima_bitacora["redb_id"]);
                $buttons = "";
                $hay_botones = true; // Por defecto mostramos botones

                // CASO 1: Si es la última bitácora y fue aprobada por el usuario actual → NO mostrar botones
                if ($es_ultima_bitacora && $ultimo_estado == 5 && $ultimo_procesador == $usuario_actual) {
                    $buttons = '';
                    $hay_botones = false;
                }
                // CASO 2: Si la última bitácora fue aprobada por el encargado → NO mostrar botones a nadie
                // else if ($ultimo_estado == 5 && $ultimo_procesador == $gerente_encargado_aprobacion) {
                //     $buttons = '';
                //     $hay_botones = false;
                // }
                // CASO 3: Documentos especiales (redt_id = 6)
                else if ($usuarios["redt_id"] == 6) {
                    // Solo mostrar botón de aceptar si es la última bitácora y su estado es 1 (En Proceso)
                    if ($es_ultima_bitacora && $ultimo_estado == 1) {
                        $buttons = '<button class="btn btn-success btn-sm aprobar-reporte" id="aceptar" data-tipo="Aprobado" title="Aceptar" data-estado="5" data-bitacora="' . $fila["redb_id"] . '">
                            <i class="fa-solid fa-check-to-slot"></i>
                        </button>';
                    }
                    // Si es la última bitácora y el procesador es distinto al usuario actual
                    else if ($es_ultima_bitacora && $ultimo_procesador != $usuario_actual) {
                        $buttons = '<button class="btn btn-success btn-sm aprobar-reporte" id="aceptar" data-tipo="Aprobado" title="Aceptar" data-estado="5" data-bitacora="' . $fila["redb_id"] . '">
                            <i class="fa-solid fa-check-to-slot"></i>
                        </button>';
                    }
                }
                // CASO 4: Otros tipos de documentos
                else {
                    // IMPORTANTE: Solo mostrar botones si es la última bitácora
                    if ($es_ultima_bitacora) {
                        // Estado "En Proceso" (1)
                        if ($ultimo_estado == 1) {
                            $buttons .= '<button class="btn btn-success btn-sm aprobar-reporte" id="aceptar" data-tipo="Aprobado" title="Aceptar" data-estado="5" data-bitacora="' . $fila["redb_id"] . '">
                                <i class="fa-solid fa-check-to-slot"></i>
                            </button>
                            <button id="rechazado" data-tipo="Rechazado" data-estado="4" title="Rechazar" data-bitacora="' . $fila["redb_id"] . '" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-formulario-rechazo">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>';
                        }

                        // Estado "Aceptado" (5) pero procesado por otro usuario
                        else if ($ultimo_estado == 5 && $ultimo_procesador != $usuario_actual) {
                            $buttons .= '<button class="btn btn-success btn-sm aprobar-reporte" id="aceptar" data-tipo="Aprobado" title="Aceptar" data-estado="5" data-bitacora="' . $fila["redb_id"] . '">
                                <i class="fa-solid fa-check-to-slot"></i>
                            </button>
                            <button id="rechazado" data-tipo="Rechazado" data-estado="4" title="Rechazar" data-bitacora="' . $fila["redb_id"] . '" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-formulario-rechazo">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </button>';
                        }

                        // Estado "Rechazado" (4)
                        else if ($ultimo_estado == 4) {
                            $buttons .= '<button class="btn btn-info btn-sm traer-retro" data-id-bitacora="' . $fila["redb_id"] . '">
                                <i class="fa-solid fa-comment"></i>
                            </button>';
                        }
                    }
                }

                // Aquí renderizamos la fila con los botones adecuados
                $response_html .= '<tr>
                      <td style="display: none;">' . $fila["redb_id"] . '</td>';
                // Continúa el resto de tu código para mostrar la fila...


                // Obtener la extensión del archivo
                $archivo = $fila['redb_ref'];
                $extension = pathinfo($archivo, PATHINFO_EXTENSION);

                // Para archivos PDF, usar un visor personalizado que limite las opciones
if (strtolower($extension) === 'pdf') {
    // Redireccionar a un visor personalizado con el PDF como parámetro
    $enlace = 'ajax/visor-pdf.php?archivo=' . urlencode($archivo);
} elseif (in_array(strtolower($extension), ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])) {
    // Usar visor de Office Online para archivos de Office
    $enlace = 'https://view.officeapps.live.com/op/embed.aspx?wdPrint=0&src=https://giraglogicdesa.girag.aero/manuales-uso/' . $archivo;
} else {
    // Enlace genérico para otros tipos de archivo
    $enlace = 'https://giraglogicdesa.girag.aero/manuales-uso/' . $archivo;
}

                // Agregar la celda con el enlace
                $response_html .= '<td><a href="' . $enlace . '" target="_blank">' . $archivo . '</a></td>';

                // Agregar las demás celdas
                $response_html .= '
                <td>' . $fila["redb_fecha"] . '</td>
                <td>' . $fila["estado"] . '</td>
                <td>' . $fila["sino_nombre"] . '</td>
                <td>' . $fila["nombre_procesador"] . '</td>
                <td>' . $buttons . '</td>
            </tr>';
            }

            if (!$hay_botones) {
                // Si no hay botones, se imprime el response_html sin los botones
                echo preg_replace('/<td>.*?<\/td>$/', '<td></td>', $response_html); // Remover los botones de la última columna
            } else {
                echo $response_html; // Imprimir el response_html con los botones
            }
        }
        break;
    case "POST":
        if (isset($_POST["tipo"]) && $_POST["tipo"] == 'Aprobado') {
            $bitacora_id = $_POST["bitacora_id"];
            $estado_id = 5; // Cambia a 5 para Aceptar

            if (!empty($bitacora_id)) {
                // Obtener redo_id y redb_procesado_por basado en bitacora_id
                $sql = "SELECT redo_id, redb_procesado_por FROM reportes_documentos_bitacora WHERE redb_id = $bitacora_id";
                $res = mysql_query($sql);

                if ($res && mysql_num_rows($res) > 0) {
                    $row = mysql_fetch_assoc($res);
                    $redo_id = $row["redo_id"];
                    $procesado_por = $row["redb_procesado_por"];

                    // Obtener la última bitácora para el redo_id obtenido
                    $sql_last_bitacora = "SELECT * FROM reportes_documentos_bitacora WHERE redo_id = $redo_id ORDER BY redb_id DESC LIMIT 1";
                    $res_last = mysql_query($sql_last_bitacora);

                    if ($res_last && mysql_num_rows($res_last) > 0) {
                        $last_bitacora = mysql_fetch_assoc($res_last);

                        // Evaluar si redb_procesado_por está vacío
                        if (empty($procesado_por)) {
                            // Actualizar la bitácora existente para marcarla como procesada (en este caso se asume que debe actualizar el estado y el procesador)
                            $sql_update = "UPDATE reportes_documentos_bitacora 
                                               SET rede_id = $estado_id, redb_procesado_por = '{$_SESSION["login_user"]}' 
                                               WHERE redb_id = $bitacora_id";
                            $res_update = mysql_query($sql_update);

                            if ($res_update) {
                                echo "Bitácora actualizada con éxito.";
                                // Código para el estado "Aceptado"
                                $sql = "SELECT r.*, a.*
FROM reportes_documentos_bitacora r
JOIN reportes_documentos a ON r.redo_id = a.redo_id
WHERE r.redb_id = $bitacora_id";
                                $resultado = mysql_fetch_assoc(mysql_query($sql));

                                $gerente_sms = $resultado['usua_id_gerente_sms'];
                                $gerente_departamento = $resultado['usua_id_gerente_departamento'];
                                $gerente_encargado_aprobacion = $resultado['usuario_encargado_aprobacion'];

                                $titulo = $resultado["redo_titulo"];
                                $descripcion = $resultado["redo_descripcion"];

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

                                // Obtener el nombre del usuario en sesión (quien aceptó)
                                $stmt_user = "SELECT usua_nombre, usca_id FROM usuarios WHERE usua_id = $user_id";
                                $result_user = mysql_query($stmt_user);
                                $row_user = mysql_fetch_assoc($result_user);
                                $session_user_nombre = $row_user["usua_nombre"];
                                $session_user_usca_id = $row_user["usca_id"];

                                // Consultar la plantilla
                                $plantilla_nombre = 'ACEPTAR-DOCUMENTO';
                                $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = '$plantilla_nombre'";
                                $plantilla_original = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

                                // Si el usuario tiene usca_id 2, 3 o 4 → enviar al gerente de departamento
                                if (in_array($session_user_usca_id, [2, 3, 4])) {
                                    $stmt = "SELECT * FROM usuarios WHERE usua_id = $gerente_departamento";
                                    $usuario = mysql_fetch_assoc(mysql_query($stmt));

                                    // Personalizar plantilla
                                    $plantilla = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                    $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                                    $plantilla = str_replace("[DESCRIPCION]", $descripcion, $plantilla);
                                    $plantilla = str_replace("[USUA_NOMBRE]", $session_user_nombre, $plantilla);

                                    // Agregar enlace
                                    $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
    style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
    Ver Documento
    </a>';

                                    $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                    $mail->Body = $plantilla;
                                    $mail->send();
                                } elseif ($user_id == $gerente_departamento) {
                                    // Enviar al gerente encargado
                                    $stmt = "SELECT * FROM usuarios WHERE usua_id = $gerente_encargado_aprobacion";
                                    $usuario = mysql_fetch_assoc(mysql_query($stmt));

                                    $plantilla = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                    $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                                    $plantilla = str_replace("[DESCRIPCION]", $descripcion, $plantilla);
                                    $plantilla = str_replace("[USUA_NOMBRE]", $session_user_nombre, $plantilla);
                                    $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
    style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
    Ver Documento
    </a>';
                                    $mail->clearAddresses();
                                    $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                    $mail->Body = $plantilla;
                                    $mail->send();

                                    // También enviar a todos con usca_id 2, 3, 4
                                    $sql_usuarios = "SELECT usua_nombre, usua_mail FROM usuarios WHERE usca_id IN (2, 3, 4)";
                                    $result_usuarios = mysql_query($sql_usuarios);
                                    while ($usuario = mysql_fetch_assoc($result_usuarios)) {
                                        // Crear una nueva plantilla personalizada
                                        $plantilla_personalizada = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                        $plantilla_personalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personalizada);
                                        $plantilla_personalizada = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personalizada);

                                        // Agregar enlace
                                        $plantilla_personalizada .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
                                        style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                                        Ver Documento
                                        </a>';

                                        // Limpiar destinatarios anteriores
                                        $mail->clearAddresses();
                                        $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                        $mail->Body = $plantilla_personalizada;
                                        $mail->send();
                                    }
                                }
                            } else {
                                echo "Error al actualizar la bitácora: " . mysql_error();
                            }
                        } else {
                            // Insertar una nueva bitácora
                            $sql_insert = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por) 
                                               VALUES ('{$last_bitacora['redo_id']}', NOW(), '{$last_bitacora['redb_ref']}', $estado_id, '{$_SESSION["login_user"]}')";
                            $res_insert = mysql_query($sql_insert);
                            $bitacora_id = mysql_insert_id();


                            if ($res_insert) {
                                echo "Se ha insertado una nueva bitácora.";
                                // Código para el estado "Aceptado"
                                $sql = "SELECT r.*, a.*
                        FROM reportes_documentos_bitacora r
                        JOIN reportes_documentos a ON r.redo_id = a.redo_id
                        WHERE r.redb_id = $bitacora_id";
                                $resultado = mysql_fetch_assoc(mysql_query($sql));

                                $gerente_sms = $resultado['usua_id_gerente_sms'];
                                $gerente_departamento = $resultado['usua_id_gerente_departamento'];
                                $gerente_encargado_aprobacion = $resultado['usuario_encargado_aprobacion'];

                                $titulo = $resultado["redo_titulo"];
                                $descripcion = $resultado["redo_descripcion"];
                                echo "USUARIO EN SESSION: $user_id<br>";
                                echo "PRESIDENTE: $gerente_encargado_aprobacion<br>";
                                echo "Gerente Departamento ID: $gerente_departamento<br>";
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
                                $mail->isHTML(true);
                                $mail->Subject = 'NOTIFICACION DE DOCUMENTO: ' . $titulo;

                                // Consultar la plantilla
                                $plantilla_nombre = 'ACEPTAR-DOCUMENTO';
                                $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = '$plantilla_nombre'";
                                $plantilla_original = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

                                // Obtener el usca_id del usuario en sesión
                                $sql_usca = "SELECT usca_id FROM usuarios WHERE usua_id = '{$_SESSION["login_user"]}'";
                                $result_usca = mysql_query($sql_usca);
                                $row_usca = mysql_fetch_assoc($result_usca);
                                $session_user_usca_id = $row_usca["usca_id"];

                                // Determinar a quién enviar el correo según las nuevas reglas

                                // CASO 1: Si el usuario en sesión tiene usca_id 2, 3 o 4
                                if (in_array($session_user_usca_id, [2, 3, 4])) {
                                    // Enviar correo al gerente de departamento
                                    $stmt = "SELECT * FROM usuarios WHERE usua_id = $gerente_departamento";
                                    $usuario = mysql_fetch_assoc(mysql_query($stmt));

                                    if ($usuario) {
                                        // Personalizar plantilla
                                        $plantilla_personalizada = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                        $plantilla_personalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personalizada);
                                        $plantilla_personalizada = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personalizada);

                                        // Agregar enlace
                                        $plantilla_personalizada .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
                                            style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                                            Ver Documento
                                            </a>';

                                        $mail->clearAddresses();
                                        $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                        $mail->Body = $plantilla_personalizada;

                                        try {
                                            $mail->send();
                                            echo "Correo enviado al gerente de departamento: " . $usuario["usua_nombre"] . "<br>";
                                        } catch (Exception $e) {
                                            echo "No se pudo enviar el mensaje al gerente de departamento. Error: {$mail->ErrorInfo}<br>";
                                        }
                                    } else {
                                        echo "No se encontró el gerente de departamento.<br>";
                                    }
                                }
                                // CASO 2: Si quien acepta es el gerente de departamento
                                else if ($user_id == $gerente_departamento) {
                                    // 2.1: Enviar al gerente encargado de aprobación
                                    if (!empty($gerente_encargado_aprobacion)) {
                                        $stmt = "SELECT * FROM usuarios WHERE usua_id = $gerente_encargado_aprobacion";
                                        $usuario = mysql_fetch_assoc(mysql_query($stmt));

                                        if ($usuario) {
                                            // Personalizar plantilla
                                            $plantilla_personalizada = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                            $plantilla_personalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personalizada);
                                            $plantilla_personalizada = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personalizada);

                                            // Agregar enlace
                                            $plantilla_personalizada .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
                                                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                                                Ver Documento
                                                </a>';

                                            $mail->clearAddresses();
                                            $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                            $mail->Body = $plantilla_personalizada;

                                            try {
                                                $mail->send();
                                                echo "Correo enviado al gerente encargado de aprobación: " . $usuario["usua_nombre"] . "<br>";
                                            } catch (Exception $e) {
                                                echo "No se pudo enviar el mensaje al gerente encargado de aprobación. Error: {$mail->ErrorInfo}<br>";
                                            }
                                        } else {
                                            echo "No se encontró el gerente encargado de aprobación.<br>";
                                        }
                                    }

                                    // 2.2: Enviar a todos los usuarios con usca_id 2, 3, 4
                                    $sql_usuarios = "SELECT * FROM usuarios WHERE usca_id IN (2, 3, 4)";
                                    $result_usuarios = mysql_query($sql_usuarios);

                                    if ($result_usuarios && mysql_num_rows($result_usuarios) > 0) {
                                        while ($usuario = mysql_fetch_assoc($result_usuarios)) {
                                            // Personalizar plantilla para cada usuario
                                            $plantilla_personalizada = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla_original);
                                            $plantilla_personalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla_personalizada);
                                            $plantilla_personalizada = str_replace("[DESCRIPCION]", $descripcion, $plantilla_personalizada);

                                            // Agregar enlace
                                            $plantilla_personalizada .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '" 
                                                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                                                Ver Documento
                                                </a>';

                                            $mail->clearAddresses();
                                            $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                                            $mail->Body = $plantilla_personalizada;

                                            try {
                                                $mail->send();
                                                echo "Correo enviado al usuario con usca_id {$usuario["usca_id"]}: " . $usuario["usua_nombre"] . "<br>";
                                            } catch (Exception $e) {
                                                echo "No se pudo enviar el mensaje al usuario {$usuario["usua_nombre"]}. Error: {$mail->ErrorInfo}<br>";
                                            }
                                        }
                                    } else {
                                        echo "No se encontraron usuarios con usca_id 2, 3 o 4.<br>";
                                    }
                                }
                            } else {
                                echo "Error al insertar la nueva bitácora: " . mysql_error();
                            }
                        }
                    } else {
                        echo "No se encontró una bitácora existente para este redo_id.";
                    }
                } else {
                    echo "No se encontró el redo_id para el bitacora_id proporcionado.";
                }
            } else {
                echo "Faltan datos requeridos para procesar la solicitud.";
            }
        } elseif (isset($_POST["confirmacion"])) {
            // Aquí manejamos la confirmación de cambios en la bitácora del documento
        } else {
            $comentario = $_POST["comentario"];
            $bitacora_id = $_POST["bitacora_id"];
            $estado_id = $_POST["estado_id"];

            if (!empty($estado_id) && !empty($bitacora_id)) {
                // Verificar si el procesador_por está vacío o no
                $sql_check = "SELECT redb_procesado_por FROM reportes_documentos_bitacora WHERE redb_id = $bitacora_id";
                $res_check = mysql_query($sql_check);

                if ($res_check && mysql_num_rows($res_check) > 0) {
                    $row_check = mysql_fetch_assoc($res_check);
                    $procesado_por = $row_check["redb_procesado_por"];

                    if (empty($procesado_por)) {
                        // Actualizar la bitácora existente
                        $sql_update = "UPDATE reportes_documentos_bitacora
                                          SET redb_comentario = '$comentario', 
                                              rede_id = $estado_id, 
                                              redb_procesado_por = '{$_SESSION["login_user"]}',
                                              redb_firmado = 0
                                          WHERE redb_id = $bitacora_id";
                        $res_update = mysql_query($sql_update);

                        if ($res_update) {
                            echo "Bitácora actualizada con éxito.";
                        } else {
                            echo "Error al actualizar la bitácora: " . mysql_error();
                        }
                    } else {
                        // Insertar una nueva bitácora
                        $sql_last_bitacora = "SELECT * FROM reportes_documentos_bitacora WHERE redo_id = (SELECT redo_id FROM reportes_documentos_bitacora WHERE redb_id = $bitacora_id) ORDER BY redb_id DESC LIMIT 1";
                        $res_last = mysql_query($sql_last_bitacora);

                        if ($res_last && mysql_num_rows($res_last) > 0) {
                            $last_bitacora = mysql_fetch_assoc($res_last);

                            $sql_insert = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_comentario, redb_procesado_por) 
                                               VALUES ('{$last_bitacora['redo_id']}', NOW(), '{$last_bitacora['redb_ref']}', $estado_id, '$comentario', '{$_SESSION["login_user"]}')";
                            $res_insert = mysql_query($sql_insert);

                            if ($res_insert) {
                                echo "Se ha insertado una nueva bitácora.";
                                $nueva_bitacora_id = mysql_insert_id();
                            } else {
                                echo "Error al insertar la nueva bitácora: " . mysql_error();
                            }
                        } else {
                            echo "No se encontró una bitácora existente para este redo_id.";
                        }
                    }
                } else {
                    echo "Error al verificar el estado del procesador.";
                }
            } else {
                echo "Faltan datos requeridos para actualizar la bitácora.";
            }

            echo "Estado recibido: $estado_id"; // Add this at the start of the block

            // Código para el estado "Rechazado"

            $sql = "SELECT r.*, a.*
FROM reportes_documentos_bitacora r
JOIN reportes_documentos a ON r.redo_id = a.redo_id
WHERE r.redb_id = $nueva_bitacora_id";
            $resultado = mysql_fetch_assoc(mysql_query($sql));

            // Depuración - Verificar qué valores se están recuperando
            // echo "Valores de la consulta: <br>";
            // echo "Gerente SMS: " . (isset($resultado['usua_id_gerente_sms']) ? $resultado['usua_id_gerente_sms'] : "No definido") . "<br>";
            // echo "Gerente Departamento: " . (isset($resultado['usua_id_gerente_departamento']) ? $resultado['usua_id_gerente_departamento'] : "No definido") . "<br>";
            // echo "Gerente Encargado: " . (isset($resultado['usuario_encargado_aprobacion']) ? $resultado['usuario_encargado_aprobacion'] : "No definido") . "<br>";
            // echo "Usuario en sesión: " . (isset($_SESSION["login_user"]) ? $_SESSION["login_user"] : "No definido") . "<br>";
            // echo "COMENTARIO: " . (isset($resultado['redb_comentario']) ? $resultado['redb_comentario'] : "No definido") . "<br>";
            // echo "ID: " . (isset($resultado['redb_id']) ? $resultado['redb_id'] : "No definido") . "<br>";

            $gerente_sms = $resultado['usua_id_gerente_sms'] ?? null;
            $gerente_departamento = $resultado['usua_id_gerente_departamento'] ?? null;
            $gerente_encargado_aprobacion = $resultado['usuario_encargado_aprobacion'] ?? null;
            $session_user_id = $_SESSION["login_user"] ?? null;
            $comentario = $resultado["redb_comentario"] ?? '';
            $titulo = $resultado["redo_titulo"] ?? 'Documento sin título';
            $descripcion = $resultado["redo_descripcion"] ?? '';
            $redo_id = $resultado["redo_id"] ?? null;

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

            // Determinar a quién enviar el correo
            $destinatarios_agregados = false;

            // Obtener el rol del usuario en sesión
            $rol_query = "SELECT usca_id FROM usuarios WHERE usua_id = '$session_user_id'";
            $rol_result = mysql_query($rol_query);
            $rol_data = mysql_fetch_assoc($rol_result);
            $rol_usuario = $rol_data ? $rol_data['usca_id'] : null;

            echo "Rol del usuario en sesión: " . ($rol_usuario ? $rol_usuario : "No encontrado") . "<br>";

            // El usuario en sesión tiene usca_id 2, 3 o 4, se notificará al gerente de departamento
            if ($rol_usuario && in_array($rol_usuario, [2, 3, 4])) {
                echo "Condición 1: Usuario con rol especial - notificar al gerente de departamento<br>";

                if (!empty($gerente_departamento)) {
                    $stmt = "SELECT * FROM usuarios WHERE usua_id = '$gerente_departamento'";
                    $result = mysql_query($stmt);
                    $usuario = mysql_fetch_assoc($result);

                    if ($usuario && !empty($usuario["usua_mail"])) {
                        echo "Agregando destinatario (gerente departamento): " . $usuario["usua_mail"] . "<br>";
                        $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                        $destinatarios_agregados = true;
                    } else {
                        echo "El gerente de departamento no tiene email válido<br>";
                    }
                } else {
                    echo "No hay gerente de departamento definido<br>";
                }
            }
            // Si el usuario en sesión es el gerente de departamento, se notifican los usuarios con usca_id 2, 3 o 4
            elseif ($session_user_id == $gerente_departamento) {
                echo "Condición 2: Usuario es gerente de departamento - notificar a usuarios con roles 2, 3, 4<br>";

                $stmt = "SELECT * FROM usuarios WHERE usca_id IN (2, 3, 4)";
                $result = mysql_query($stmt);

                if (mysql_num_rows($result) > 0) {
                    while ($usuario = mysql_fetch_assoc($result)) {
                        if (!empty($usuario["usua_mail"])) {
                            echo "Agregando destinatario (usuario con rol especial): " . $usuario["usua_mail"] . "<br>";
                            $mail->addAddress($usuario["usua_mail"], $usuario["usua_nombre"]);
                            $destinatarios_agregados = true;
                        }
                    }
                } else {
                    echo "No se encontraron usuarios con roles 2, 3, 4<br>";
                }
            }

            // Verificar si se agregaron destinatarios antes de continuar
            if (!$destinatarios_agregados) {
                echo "No se pudo enviar el mensaje porque no se encontraron destinatarios válidos.<br>";
                // Email de soporte como fallback
                echo "Agregando email de soporte como fallback<br>";
                $mail->addAddress("correo_soporte@girag.aero", "Soporte Técnico");
            }

            if ($estado_id == 4) {
                $plantilla_nombre = 'NOTIFICACION-RECHAZO-DOCUMENTO';
            } else {
                $plantilla_nombre = 'NOTIFICACION-DOCUMENTO-ACEPTADO';
            }

            echo "Plantilla a utilizar: $plantilla_nombre<br>";

            // Consultar la plantilla correspondiente
            $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = '$plantilla_nombre'";
            $plantilla_result = mysql_query($stmt);
            $plantilla_row = mysql_fetch_assoc($plantilla_result);

            if ($plantilla_row) {
                $plantilla = $plantilla_row["cont_detalle"];

                // Asegúrate de que $usuario esté definido antes de usarlo
                if (isset($usuario) && isset($usuario["usua_nombre"])) {
                    $plantilla = str_replace("[USUA_ASIGNADO]", $usuario["usua_nombre"], $plantilla);
                } else {
                    $plantilla = str_replace("[USUA_ASIGNADO]", "Usuario", $plantilla);
                }

                $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                $plantilla = str_replace("[DESCRIPCION]", $descripcion, $plantilla);

                // Añadir comentario solo para estado rechazado
                if ($estado_id == 4) {
                    $plantilla = str_replace("[COMENTARIO]", $comentario, $plantilla);
                }

                $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '"
style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
Ver Documento
</a>';

                try {
                    $mail->isHTML(true);
                    $mail->Subject = 'NOTIFICACION DE DOCUMENTO: ' . $titulo;
                    $mail->Body = $plantilla;

                    // Mostrar destinatarios antes de enviar
                    echo "Destinatarios del correo: <br>";
                    foreach ($mail->getToAddresses() as $toAddress) {
                        echo "- " . $toAddress[0] . "<br>";
                    }

                    $mail->send();
                    echo 'El mensaje ha sido enviado<br>';
                } catch (Exception $e) {
                    echo "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}<br>";
                }
            } else {
                echo "No se encontró la plantilla de correo '$plantilla_nombre'<br>";
            }
        }

    case "PUT":
        $usua_id_sesion = $_SESSION["login_user"];
        $_PUT = json_decode(file_get_contents("php://input"), true);
        $redo_id = $_PUT["redo_id"];

        // Obtener info del documento
        $sql = "
                SELECT 
                    rd.redo_id, 
                    rd.usua_id_gerente_sms, 
                    rd.usua_id_gerente_departamento, 
                    rd.usuario_encargado_aprobacion,
                    rd.redt_id,
                    u1.usua_nombre AS nombre_gerente_sms,
                    u2.usua_nombre AS nombre_gerente_departamento,
                    u3.usua_nombre AS nombre_encargado_aprobacion
                FROM 
                    reportes_documentos rd
                LEFT JOIN 
                    usuarios u1 ON rd.usua_id_gerente_sms = u1.usua_id
                LEFT JOIN 
                    usuarios u2 ON rd.usua_id_gerente_departamento = u2.usua_id
                LEFT JOIN 
                    usuarios u3 ON rd.usuario_encargado_aprobacion = u3.usua_id
                WHERE 
                    rd.redo_id = $redo_id
            ";
        $result = mysql_query($sql);
        $usuarios = mysql_fetch_assoc($result);

        $gerente_departamento = $usuarios['usua_id_gerente_departamento'];
        $gerente_encargado_aprobacion = $usuarios['usuario_encargado_aprobacion'];
        $redt_id = $usuarios['redt_id'];

        $sql = "SELECT redb_ref FROM reportes_documentos_bitacora WHERE redo_id = $redo_id ORDER BY redb_id DESC LIMIT 1";
        $documento_actual = mysql_fetch_assoc(mysql_query($sql))["redb_ref"];
        $documento_path = "../manuales-uso/$documento_actual";

        if ($usua_id_sesion == $gerente_departamento) {
            // Gerente de Departamento firma
            $sql = "
                        SELECT usfi_ref
                        FROM usuarios_firmas
                        WHERE usua_id = $gerente_departamento
                    ";
            $firma_gerente = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];
            if (empty($firma_gerente)) {
                http_response_code(400);
                echo json_encode(["error" => "Firma del Gerente de Departamento no encontrada"]);
                die();
            }
            $templateProccessor = new TemplateProcessor($documento_path);
            $templateProccessor->setImageValue("FIRMA DE REVISADO POR DUEÑO DE PROCESO", [
                "path" => "../firmas-electronicas/$firma_gerente",
                "width" => 200,
                "height" => 100
            ]);
            $nuevo_documento = "" . $documento_actual;
            $pathToSave = "../manuales-uso/$nuevo_documento";
            $templateProccessor->saveAs($pathToSave);
            // Guardar en la bitácora
            $sql = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por, redb_firmado)
                            VALUES ($redo_id, NOW(), '$nuevo_documento', 5, $usua_id_sesion, 1)";
            mysql_query($sql);
            echo json_encode(["message" => "Firma del Gerente de Departamento agregada"]);

            // CORRECCIÓN: Obtener información del documento para el correo
            $sql_documento = "SELECT redo_titulo as titulo, redo_descripcion as descripcion 
                             FROM reportes_documentos 
                             WHERE redo_id = $redo_id";
            $resultado_documento = mysql_query($sql_documento);

            if ($resultado_documento && mysql_num_rows($resultado_documento) > 0) {
                $documento_info = mysql_fetch_assoc($resultado_documento);
                $titulo = $documento_info['titulo'];
                $descripcion = $documento_info['descripcion'];

                // 1. Obtener la plantilla de correo "NOTIFICACIÓN-FIRMA-DOCUMENTO"
                $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACIÓN-FIRMA-DOCUMENTO'";
                $resultado_plantilla = mysql_query($stmt);

                if ($resultado_plantilla && mysql_num_rows($resultado_plantilla) > 0) {
                    $plantilla = mysql_fetch_assoc($resultado_plantilla)["cont_detalle"];
                    // 2. Agregar botón al final de la plantilla
                    $plantilla .= '<br><a href="https://giraglogicdesa.girag.aero/index.php?p=reportes-detalles&id=' . $redo_id . '"
                        style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block;">
                        Ver Documento
                    </a>';

                    // 3. Consultar usuarios con usca_id = 2, 3, 4 y 27
                    $sql_usuarios_notificar = "SELECT usua_nombre, usua_mail FROM usuarios WHERE usca_id IN (2, 3, 4, 27)";
                    $result_usuarios_notificar = mysql_query($sql_usuarios_notificar);

                    // Verificar si hay usuarios para notificar
                    if ($result_usuarios_notificar && mysql_num_rows($result_usuarios_notificar) > 0) {
                        // 4. Enviar correo a cada uno
                        while ($row = mysql_fetch_assoc($result_usuarios_notificar)) {
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

                                // Reemplazo dinámico de campos en plantilla
                                $plantillaPersonalizada = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
                                $plantillaPersonalizada = str_replace("[DESCRIPCION]", $descripcion, $plantillaPersonalizada);
                                $plantillaPersonalizada = str_replace("[USUA_ASIGNADO]", $row["usua_nombre"], $plantillaPersonalizada);

                                $mail->isHTML(true);
                                $mail->Subject = 'Notificación de Firma de Documento: ' . $titulo;
                                $mail->Body = $plantillaPersonalizada;

                                $mail->send();
                                // Registrar envío exitoso (opcional)
                                error_log("Correo enviado a " . $row["usua_mail"] . " para documento $redo_id");
                            } catch (Exception $e) {
                                error_log("Error al enviar a " . $row["usua_mail"] . ": {$mail->ErrorInfo}");
                            }
                        }
                    } else {
                        error_log("No se encontraron usuarios con usca_id = 27 para notificar");
                    }
                } else {
                    error_log("No se encontró la plantilla de correo 'NOTIFICACIÓN-FIRMA-DOCUMENTO'");
                }
            } else {
                error_log("No se encontró información del documento con ID: $redo_id");
            }
        } // For the approval manager section (elseif block), replace the problematic code with:
        elseif ($usua_id_sesion == $gerente_encargado_aprobacion) {
            // 🛡️ Validar si necesita esperar la firma del gerente
            if ($redt_id != 6) {
                $sql_firma_gerente = "
                SELECT COUNT(*) AS firmado 
                FROM reportes_documentos_bitacora 
                WHERE redo_id = $redo_id 
                AND redb_procesado_por = $gerente_departamento 
                AND redb_firmado = 1
            ";
                $resultado_firma_gerente = mysql_fetch_assoc(mysql_query($sql_firma_gerente));
                if ($resultado_firma_gerente['firmado'] == 0) {
                    http_response_code(400);
                    echo json_encode(["error" => "Primero debe firmar el Gerente de Departamento"]);
                    die();
                }
            }

            // CORRECCIÓN: Obtener la firma del encargado de aprobación
            $sql = "
        SELECT usfi_ref
        FROM usuarios_firmas
        WHERE usua_id = $gerente_encargado_aprobacion
    ";
            $firma_encargado = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];
            if (empty($firma_encargado)) {
                http_response_code(400);
                echo json_encode(["error" => "Firma del Encargado de Aprobación no encontrada"]);
                die();
            }

            $templateProccessor = new TemplateProcessor($documento_path);
            $templateProccessor->setImageValue("FIRMA DE APROBADO POR SUPERVISOR DIRECTO", [
                "path" => "../firmas-electronicas/$firma_encargado",
                "width" => 200,
                "height" => 100
            ]);

            $nuevo_documento = "" . $documento_actual;
            $pathToSave = "../manuales-uso/$nuevo_documento";
            $templateProccessor->saveAs($pathToSave);

            // Rest of the code remains the same...

            // Cargar el documento Word generado
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($pathToSave);

            // Guardar el documento como HTML
            $htmlFilePath = "../manuales-uso/" . pathinfo($nuevo_documento, PATHINFO_FILENAME) . ".html";
            $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $xmlWriter->save($htmlFilePath);

            // Leer el contenido HTML generado
            $html = file_get_contents($htmlFilePath);

            // Normalizar el HTML eliminando tags innecesarios o problemáticos
            $html = str_replace(['<w:sym', '<w:br', '</w:p>', '</w:sym>', '</w:br>'], '', $html);
            $html = preg_replace('/<div[^>]*>/i', '<div>', $html); // Limpiar tags div

            // Aplicar CSS personalizado para ajustar el formato
            $customCss = "
    <style>
        body { font-family: Arial, sans-serif; }
        h1, h2, h3, h4, h5, h6 { page-break-before: avoid; }
        p { page-break-inside: avoid; }
    
        /*  🔽  NUEVO: limitar imágenes  🔽  */
        img {
            max-width: 180px;   /* o el ancho que quieras en puntos/píxeles */
            height: auto;       /* mantiene la proporción */
        }
    </style>";


            $html = $customCss . $html;

            // Configurar y utilizar MPDF 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'tempDir' => __DIR__ . '/temp',
                'format' => 'A4',
                'useActiveForms' => true,
            ]);

            $user_id = $_SESSION["login_user"];
            // Array de usuarios autorizados para imprimir
            $usuarios_autorizados = [123, 456, 789]; // Reemplaza con los IDs que necesites

            if (in_array($user_id, $usuarios_autorizados)) {
                // Usuario autorizado - sin restricciones
                $mpdf->WriteHTML($html);
            } else {
                // Usuarios no autorizados - restringir la impresión
                $mpdf->SetProtection(['copy', 'extract', 'assemble', 'fill-forms'], '', '');
                $mpdf->WriteHTML($html);
            }

            // Generar el archivo PDF y guardarlo
            $pdfName = pathinfo($nuevo_documento, PATHINFO_FILENAME) . ".pdf";
            $pdfPath = "../manuales-uso/" . $pdfName;
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);



            // Guardar en la bitácora
            $sql = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por, redb_firmado)
                            VALUES ($redo_id, NOW(), '$pdfName', 5, $usua_id_sesion, 1)";
            mysql_query($sql);

            echo json_encode(["message" => "Firma del Encargado de Aprobación agregada"]);
        } else {
            http_response_code(403);
            echo json_encode(["error" => "El usuario en sesión no está autorizado para firmar este documento"]);
        }
        $sql = "UPDATE reportes_documentos SET 
            redo_ref = '$pdfName', 
            rede_id = 3,
            redo_firmado =1  
            WHERE redo_id = $redo_id";

        mysql_query($sql);
}
