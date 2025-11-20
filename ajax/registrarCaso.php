<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "../vendor/autoload.php";

include('../conexion.php');
include('../funciones.php');
include "mailerConfig.php";
// include('../seguridad.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Guardar todas las variables revibidas del POST
    $nombre_abierto_por = !empty($_POST["abierto_por"]) ? $_POST["abierto_por"] : "N/A";
    $correo = !empty($_POST["correo"]) ? $_POST["correo"] : "N/A";
    $descripcion = $_POST['descripcion'];
    $departamento = $_POST['departamento'] == "Escoger Departamento" ? 0 : $_POST["departamento"]; //Departamento de quien reporta
    $tipo = $_POST['tipo'];
    $ubicacion = $_POST['ubicacion'];
    $cacl_id = $_POST["cacl_id"];
    $cacd_id = $_POST["cacd_id"];
    $equipos = $_POST['equipos'];
    $fecha_incidencia = $_POST['fecha_incidencia'];
    $nota = $_POST['nota'];
    // $caus_id = $_POST["caus_id"];
    $cargo = $_POST["cargo_reporta"];
    // $frecuencia = $_POST['frecuencia'];
    // $inc_seg_op = $_POST['seg_op'];
    // $inc_procesos = $_POST['procesos'];
    // $imp_eco = $_POST['imp_eco'];
    $imp_per = $_POST['imp_per'];
    // $imp_med_amb = $_POST['imp_med_amb'];

    //Verficiar si estan llenos los campos
    // if(!empty($descripcion) && !empty($tipo) && !empty($ubicacion) && !empty($frecuencia) && !empty($inc_seg_op) && !empty($inc_procesos) && !empty($imp_eco) && !empty($imp_per) && !empty($imp_med_amb) && !empty($equipos) && !empty($nota) && !empty($ubicacion) && !empty($cacl_id)) { 
    if (!empty($descripcion) && !empty($tipo) && !empty($ubicacion) && !empty($cacl_id) and !empty($cacd_id) and !empty($cargo)) {
        try {
            // Query que sube los nombres del archivo y rutina que los sube tambien al server
            // $stmt = "INSERT INTO casos(caso_descripcion, depa_id, cati_id, inso_id, inpr_id, imec_id, impe_id, imma_id, equi_id, caso_fecha, caso_nota, caso_ubicacion, caso_nombre_abierto_por, caso_correo_abierto_por, cacl_id, cacd_id) VALUES('$descripcion', '$departamento', '$tipo', '$inc_seg_op', '$inc_procesos', '$imp_eco', '$imp_per', '$imp_med_amb', '$equipos', now(), '$nota', '$ubicacion', '$nombre_abierto_por', '$correo', '$cacl_id', '$cacd_id')";
            $stmt = "INSERT INTO casos(caso_descripcion, depa_id_quien_reporta, cati_id, equi_id, caso_fecha, caso_nota, caso_ubicacion, caso_nombre_abierto_por, caso_correo_abierto_por, cacl_id, cacd_id, caso_cargo_reporta, impe_id) VALUES('$descripcion', '$departamento', '$tipo', '$equipos', '$fecha_incidencia', '$nota', '$ubicacion', '$nombre_abierto_por', '$correo', '$cacl_id', '$cacd_id', '$cargo', '$imp_per')";
            $res = mysql_query($stmt);

            $last_caso = mysql_insert_id();

            //Notificacion de caso
            // $stmt = "SELECT * FROM usuarios a INNER JOIN usuarios_tipos b ON a.usti_id = b.usti_id WHERE b.usti_nombre LIKE '%Seguridad Operacional%'";
            $stmt = "SELECT * FROM usuarios WHERE usua_administrador_caso = 1";
            $res = mysql_query($stmt);

            //Buscar la plantilla
            $plantilla = mysql_fetch_assoc(mysql_query("SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NUEVO-CASO'"))["cont_detalle"];
            $plantilla = str_replace("[CASO_ID]", $last_caso, $plantilla);
            $plantilla = str_replace("[ENLAC]", "<a href='index.php?p=detalle-caso&caso_id=$last_caso'>Acceder al caso</a>", $plantilla);
            $usua_email_array = [];

            while ($fila = mysql_fetch_assoc($res)) {
                $usua_id = $fila["usua_id"];
                $usua_email = mysql_fetch_assoc(mysql_query("SELECT usua_mail FROM usuarios WHERE usua_id = '$usua_id'"))["usua_mail"];

                // print_r($usua_email);
                array_push($usua_email_array, $usua_email);

                $sql = "INSERT INTO usuarios_notificaciones(
                    usua_id,
                    usno_mensaje, 
                    usno_ref,
                    usno_tabla,
                    usno_tabla_id,
                    usno_tabla_campo) 
                    VALUES(
                    '$usua_id', 
                    'Nuevo caso reportado $last_caso', 
                    'index.php?p=detalle-caso&caso=$last_caso',
                    'casos', 
                    '$last_caso', 
                    'caso_id'
                    )";
                mysql_query($sql);
            }

            // Validacion para cacl_id == 2
            if ($cacl_id == 2) {
                // Agregar a Isabeth Hidalgo
                $query_isabeth = "SELECT usua_mail FROM usuarios WHERE usua_nombre LIKE 'Isabeth Hidalgo'";
                $result_isabeth = mysql_query($query_isabeth);
                if ($row_isabeth = mysql_fetch_assoc($result_isabeth)) {
                    array_push($usua_email_array, $row_isabeth["usua_mail"]);
                }

                // Agregar a Gabriel Diaz
                $query_gabriel = "SELECT usua_mail FROM usuarios WHERE usua_nombre LIKE 'Gabriel Diaz'";
                $result_gabriel = mysql_query($query_gabriel);
                if ($row_gabriel = mysql_fetch_assoc($result_gabriel)) {
                    array_push($usua_email_array, $row_gabriel["usua_mail"]);
                }
            }

            //Si hay respuesta, significa que si vamos a enviar email
            if (!empty($usua_email_array)) {
                enviar_email($smtp_username, "NOTIFICACIONES SMS Y CALIDAD", "NUEVO CASO REPORTADO", $plantilla, $usua_email_array, $smtp_username, $smtp_password, new PHPMailer());
            }

            //Verificar si hay documentos para crear la QUERY
            if (!empty($_FILES["archivos"]["name"][0])) {
                // Query que me guarda cada imagen en el server------------
                $last_id = $last_caso;
                $docs = $_FILES["archivos"];

                foreach ($docs["name"] as $key => $val) {
                    $nombre = $docs["full_path"][$key];
                    $renombrar = time() . "-" . $nombre;

                    $stmt = "INSERT INTO casos_documentos(cado_ref, caso_id, cado_nombre) VALUES('$renombrar', '$last_id', '$nombre')";

                    //Guardamos el documento
                    move_uploaded_file($docs["tmp_name"][$key], "../img/casos_docs/" . $renombrar);

                    // Ejecutamos la query de subida a cado
                    $res = mysql_query($stmt, $dbh);

                    if (!$res) {
                        throw new Exception("Error al ejecutar la consulta de subida de archivos: " . $con->error);
                    } else {
                        // echo "Documentos subido exitosamente";
                    }
                }
            }

            //Seleccionar todos los usuarios de SMS y enviarle la notifiacion de un nuevo caso

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    } else {
        http_response_code(400);
        echo "No se ha podido enviar, llene todos los campos correctamente";
    }
}