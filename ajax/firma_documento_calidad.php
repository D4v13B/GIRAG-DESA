


case "PUT":
        $usua_id_sesion = $_SESSION["login_user"];
        $_PUT = json_decode(file_get_contents("php://input"), true);
        $redo_id = $_PUT["redo_id"];
        $sql = "
            SELECT 
                rd.redo_id, 
                rd.usua_id_gerente_sms, 
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

        // Traer el último documento aceptado
        $sql = "SELECT * FROM reportes_documentos_bitacora WHERE redo_id = $redo_id AND rede_id = 5 ORDER BY redb_id DESC LIMIT 1";
        $documento = mysql_fetch_assoc(mysql_query($sql))["redb_ref"];

        // Seleccionar la firma dependiendo del usuario en sesión
        $sql = "SELECT us.usfi_ref FROM usuarios_firmas us
                        INNER JOIN reportes_documentos rd ON us.usua_id = rd.usua_id_gerente_departamento
                        WHERE rd.redo_id = $redo_id AND us.usua_id = $usua_id_sesion";

        $firma_usuario = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];

        if (empty($firma_usuario)) {
            // Si el usuario en sesión no es el gerente de departamento, verificar si es el presidente
            $sql = "SELECT uf.usfi_ref
        FROM usuarios u
        INNER JOIN usuarios_firmas uf ON u.usua_id = uf.usua_id
        INNER JOIN reportes_documentos rd ON rd.usuario_encargado_aprobacion = u.usua_id
        WHERE rd.redo_id =  $redo_id AND u.usua_id = $usua_id_sesion";
        $firma_encargado_aprobacion= mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];


            if (empty($firma_encargado_aprobacion)) {
                echo json_encode(["error" => "Firma del presidente no encontrada"]);
                http_response_code(400);
                die();
            }
        }

        // Procesar la plantilla con las firma seleccionadas
        $templateProccessor = new TemplateProcessor("../manuales-uso/$documento");

        if (!empty($firma_usuario)) {
            
            $templateProccessor->setImageValue("FIRMA DE REVISADO POR DUEÑO DE PROCESO", [
                "path" => "../firmas-electronicas/$firma_usuario",
                "width" => 200,
                "height" => 100,
                "ratio" => false
            ]);

            $nuevo_documento = "f" . $documento;
            $pathToSave = "../manuales-uso/$nuevo_documento";
            $templateProccessor->saveAs($pathToSave);

            // Insertar en reporte_documento_bitacora con el estado 'Aceptado'
            $sql = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por)
                        VALUES ($redo_id, NOW(), '$nuevo_documento', 5, $usua_id_sesion)";
            mysql_query($sql);

            // Enviar correo al presidente solicitando firma
            $sql = "SELECT usua_mail FROM usuarios WHERE usua_id =$gerente_encargado_aprobacion ";
            $presidente_mail = mysql_fetch_assoc(mysql_query($sql))["usua_mail"];

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
            $mail->addAddress($presidente_mail, 'Presidente');

            $sql = "SELECT * FROM reportes_documentos WHERE redo_id = $redo_id";
            $resultado = mysql_fetch_assoc(mysql_query($sql));
            $titulo = $resultado["redo_titulo"];
            $descripcion = $resultado["redo_descripcion"];

            $stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'NOTIFICACION-FIRMA-DOCUMENTO'";
            $plantilla = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

            $plantilla = str_replace("[NOMBRE_DOCUMENTO]", $titulo, $plantilla);
            $plantilla = str_replace("[DESCRIPCION]", $descripcion, $plantilla);
            $plantilla = str_replace("[USUARIO]", $_SESSION["login_user"], $plantilla);

            try {
                $mail->isHTML(true);
                $mail->Subject = 'Solicitud de Firma: ' . $titulo;
                $mail->Body = $plantilla;
                $mail->send();
                echo 'El mensaje ha sido enviado al presidente';
            } catch (Exception $e) {
                echo "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}";
            }
        } elseif (!empty($firma_presidente)) {
            // Asignar firma del presidente
            $templateProccessor->setImageValue("FIRMA DE APROBADO POR SUPERVISOR DIRECTO", [
                "path" => "../firmas-electronicas/$firma_presidente",
                "width" => 200,
                "height" => 100,
                "ratio" => false
            ]);
            // Crear el nuevo documento Word
            $nuevo_documento = "firmado-" . $documento;
            $pathToSave = "../manuales-uso/$nuevo_documento";
            $templateProccessor->saveAs($pathToSave);

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
</style>";

            $html = $customCss . $html;

            // Configurar y utilizar MPDF para convertir el HTML a PDF
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'tempDir' => __DIR__ . '/temp', 'format' => 'A4']);
            $mpdf->WriteHTML($html);

            // Generar el archivo PDF y guardarlo
            $pdfName = pathinfo($nuevo_documento, PATHINFO_FILENAME) . ".pdf"; // Nombre del archivo PDF
            $pdfPath = "../manuales-uso/" . $pdfName; // Ruta completa para guardar el PDF
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

            // Insertar en la base de datos el registro de bitácora con el nuevo PDF generado
            $sql = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por)
            VALUES ($redo_id, NOW(), '$pdfName', 5, $usua_id_sesion)";
            mysql_query($sql);

            // Actualizar la referencia de reportes_documentos con el documento firmado y el estado
            $sql = "UPDATE reportes_documentos SET 
                redo_ref = '$pdfName',
                rede_id = 3  ,
                redo_firmado =1
                WHERE redo_id = $redo_id";
            mysql_query($sql);
        }

        break;
}
