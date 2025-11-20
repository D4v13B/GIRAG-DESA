<?php
use PhpOffice\PhpWord\TemplateProcessor;
include '../conexion.php';
include '../funciones.php';
session_start();

//Documento que me sube los nuevos documentos de cada tarea
switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        //Recibir la confirmacion por parte del gerente de SMS, cambiar el estado del documento y publicarlo 
        $usua_id_gerente_calidad = $_SESSION["login_user"];
        $_POST = json_decode(file_get_contents("php://input"), true);

        $redo_id = $_POST["redo_id"];

        //Me trae el ultimo documento de la base de datos que esta aceptado
        $sql = "SELECT * FROM reportes_documentos_bitacoras WHERE redo_id = $redo_id AND rede_id = 3 ORDER BY redb_id DESC LIMIT 1";
        $documento = mysql_fetch_assoc(mysql_query($sql))["redb_id"];

        //Seleccionar la firma del gerente de departamento
        $sql = "SELECT usfi_ref FROM usuarios_firmas us
        INNER JOIN reportes_documentos rd ON us.usua_id = rd.usua_id_gerente_departamento
        WHERE rd.redo_id = $redo_id";

        $firma_gerente_depa = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];

        // Seleccionar la firma de gerente de sms
        $sql = "SELECT usfi_ref FROM usuarios_firmas us
        WHERE usua_id = $usua_id_gerente_calidad";

        $firma_gerente_sms = mysql_fetch_assoc(mysql_query($sql))["usti_ref"];

        $templateProccessor = new TemplateProcessor("./manuales-uso/$documento");
        $templateProccessor->setImageValue("FIRMAR_GERENTE_DEPARTAMENTO", function () use ($firma_depa) {
            return [
                "path" => "../firmas-electronicas/$firma_depa",
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

        //Actualizar la referencia de reportes_documentos con el documento firmado y el estado
        $sql = "UPDATE reportes_documentos_bitacoras SET 
        redo_ref = 'firmado-$documento',
        rede_id = 3  
        WHERE redo_id = $redo_id";
        mysql_query($sql);
        break;
}


