<?php

use PhpOffice\PhpWord\TemplateProcessor;

require "conexion.php";
require_once "vendor/autoload.php";
$id = $_GET["id"]; //ID del ultimo documento de la bitacora que van a aceptar

/**
 * Crear una peticion a la base de datos que me envie el ultimo archivo de la bitacora
 */

$sql = "SELECT * 
FROM reportes_documentos_bitacora
WHERE redo_id = $id 
ORDER BY redb_id DESC
LIMIT 1";

$documento = mysql_fetch_assoc(mysql_query($sql))["redb_ref"];

//Seleccionar la firma del gerente de departamento
$sql = "SELECT usfi_ref FROM usuarios_firmas us
INNER JOIN reportes_documentos rd ON us.usua_id = rd.usua_id_gerente_departamento
WHERE rd.redo_id = $id";

$firma_depa = mysql_fetch_assoc(mysql_query($sql))["usfi_ref"];

$templateProccessor = new TemplateProcessor("./manuales-uso/$documento");
$templateProccessor->setImageValue("FIRMAR_GERENTE_CALIDAD", function() use ($firma_depa){
   return ["path" => "firmas-electronicas/$firma_depa", "width" => 200, "height" => 100, "ratio" => false];
});

$pathToSave = "manuales-uso/firmado-".$documento;
$templateProccessor->saveAs($pathToSave);