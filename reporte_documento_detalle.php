<?php

use PhpOffice\PhpWord\TemplateProcessor;

require __DIR__ . "/../vendor/autoload.php";
include '../conexion.php';
include '../funciones.php';
session_start();

// error_reporting(0);

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":

        if (isset($_GET["id_bitacora"])) {
            // extrae el comentario de la base de datos
            $redb_id = $_GET["id_bitacora"];
            $sql = "SELECT * FROM reportes_documentos_bitacora WHERE redb_id = $redb_id";
            $res = mysql_query($sql);

            // Verificar si se encontraron resultados
            if (mysql_num_rows($res) > 0) {
                $fila = mysql_fetch_assoc($res);
                echo json_encode($fila);
            } else {
                // No se encontraron registros
                echo json_encode(array("error" => "No se encontraron registros para el ID de bitacora proporcionado"));
            }
        } else {

            /**
             * 
             *Solo cuando existe @var $redo_id
             */
            $redo_id = $_GET["redo_id"];
            $sql = "SELECT *, (SELECT rede_nombre FROM reportes_documentos_estado WHERE rd.rede_id = rede_id) estado 
            FROM reportes_documentos_bitacora rd WHERE redo_id = $redo_id";
            $res = mysql_query($sql);

            $ultimo_estado;
            $response_html = "";
            while ($fila = mysql_fetch_assoc($res)) {
                $ultimo_estado = $fila["rede_id"];
                $buttons = "";

                if ($fila["rede_id"] == 4) {
                    $buttons .= '<button class="btn btn-info btn-sm traer-retro" data-id-bitacora="' . $fila["redb_id"] . '"><i class="fa-solid fa-comment"></i></button>';
                } elseif ($fila["rede_id"] == 1) {
                    $buttons .= '<button class="btn btn-success btn-sm aprobar-reporte" id="aceptar" data-tipo="Aprobado" data-estado="3" data-bitacora="' . $fila["redb_id"] . '"><i class="fa-solid fa-check-to-slot"></i></button>
                    
                    <button id="rechazado" data-tipo="Rechazado" data-estado="4" data-bitacora="' . $fila["redb_id"] . '" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-formulario-rechazo"><i class="fa-solid fa-circle-xmark"></i></button>';
                }

                $response_html .= '<tr>
                <td> ' . $fila["redb_id"] . '</td>

                <td>
                  <a href="manuales-uso/' . $fila["redb_ref"] . '">' . $fila["redb_ref"] . '</a>
                </td>

                <td>' . $fila["redb_fecha"] . '</td>
                <td>' . $fila["estado"] . '</td>
                <td>
                ' . $buttons . '
                </td>
              </tr>';
            }

            if ($ultimo_estado == 3) {
                $response_html .= '<script>
                $("#aceptar_reporte").show()
                </script>';
            } else {
                $response_html .= '<script>
                $("#aceptar_reporte").hide()
                </script>';
            }

            echo $response_html;
        }
        break;

    case "POST":
        if (isset($_POST["tipo"]) and $_POST["tipo"] == 'Aprobado') {

            $bitacora_id = $_POST["bitacora_id"];
            $estado_id = $_POST["estado_id"];
            if (!empty($estado_id) && !empty($bitacora_id)) {

                $stmt = "UPDATE reportes_documentos_bitacora SET rede_id = '$estado_id' WHERE redb_id = '$bitacora_id'";
                mysql_query($stmt);
            }
        } else {

            $comentario = $_POST["comentario"];
            $bitacora_id = $_POST["bitacora_id"];
            $estado_id = $_POST["estado_id"];
            $redo_id = $_POST["redo_id"];
            if (!empty($estado_id) && !empty($bitacora_id)) {

                $stmt = "UPDATE reportes_documentos_bitacora SET redb_comentario = '$comentario', rede_id = '$estado_id' WHERE redb_id = '$bitacora_id'";
                mysql_query($stmt);
            }
        }

        // if ($estado_id == 3 && !empty($redo_id)) {
        //     $stmt = "UPDATE reportes_documentos SET rede_id = $estado_id WHERE redo_id = $redo_id";
        //     // mysql_query($stmt);
        // } else {
        //     http_response_code(400);
        //     echo "Error: ID de registro no proporcionado";
        // }
        break;
    case "PUT":

        echo "asdasdasdasd";

        //Recibir la confirmacion por parte del gerente de SMS, cambiar el estado del documento y publicarlo  
        $usua_id_gerente_calidad = $_SESSION["login_user"];
        $_PUT = json_decode(file_get_contents("php://input"), true);

        $redo_id = $_PUT["redo_id"];

        //Me trae el ultimo documento de la base de datos que esta aceptado
        $sql = "SELECT * FROM reportes_documentos_bitacora WHERE redo_id = $redo_id AND rede_id = 3 ORDER BY redb_id DESC LIMIT 1";
        $documento = mysql_fetch_assoc(mysql_query($sql))["redb_ref"];


        //Seleccionar la firma del gerente de departamento
        $sql = "SELECT usfi_ref FROM usuarios_firmas us
        INNER JOIN reportes_documentos rd ON us.usua_id = rd.usua_id_gerente_departamento
        WHERE rd.redo_id = $redo_id";

        $firma_gerente_depa = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];

        if (empty($firma_gerente_depa)) {
            echo "Firma de gerente departamento no encontrada";
            http_response_code(400);
            die();
        }

        // Seleccionar la firma de gerente de sms
        $sql = "SELECT usfi_ref FROM usuarios_firmas us
        WHERE usua_id = $usua_id_gerente_calidad";

        $firma_gerente_sms = mysql_fetch_assoc(mysql_query($sql))['usfi_ref'];
        if (empty($firma_gerente_sms)) {
            echo json_encode(["error" => "Firma de gerente SMS no encontrada"]);
            http_response_code(400);
            die();
        }

        $templateProccessor = new TemplateProcessor("../manuales-uso/$documento");
        $templateProccessor->setImageValue("FIRMAR_GERENTE_DEPARTAMENTO", function () use ($firma_gerente_depa) {
            return [
                "path" => "../firmas-electronicas/$firma_gerente_depa",
                "width" => 200,
                "height" => 100,
                "ratio" => false
            ];
        });
        $templateProccessor->setImageValue("FIRMAR_GERENTE_SMS", function () use ($firma_gerente_sms) {
            return [
                "path" => "../firmas-electronicas/$firma_gerente_sms",
                "width" => 200,
                "height" => 100,
                "ratio" => false
            ];
        });

        $pathToSave = "../manuales-uso/firmado-" . $documento;
        $templateProccessor->saveAs($pathToSave);

        //Actualizar la referencia de reportes_documentos con el documento firmado y el estado [ya sirve]
        $sql = "UPDATE reportes_documentos SET 
        redo_ref = 'firmado-" . $documento . "',
        rede_id = 3  
        WHERE redo_id = $redo_id";
        mysql_query($sql);

        break;
}
