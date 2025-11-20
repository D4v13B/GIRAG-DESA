<?php
include('conexion.php');
include('funciones.php');

$depa_id = $_GET["depa"];
$tipo_contrato = $_GET["tipo"];
$url = "https://giraglogicdesa.girag.aero/casos.php"; 

// Verificar que file_get_contents funcione correctamente
$qr_code = file_get_contents("https://api.e-integracion.com/a-qr.php?url=".$url);
if ($qr_code === FALSE) {
    die("Error al obtener el código QR");
}

// Ejecutar la consulta y verificar su resultado
$stmt = "SELECT * FROM contratos WHERE cont_nombre = 'FORM'";
$result = mysql_query($stmt);
if (!$result) {
    die("Error en la consulta SQL: " . mysql_error());
}

$row = mysql_fetch_assoc($result);
if (!$row) {
    die("No se encontraron resultados para la consulta SQL");
}

$contenido = $row["cont_detalle"];

// Reemplazar [QR] en el contenido con el código QR obtenido
$contenido = str_replace("[QR]", $qr_code, $contenido);

require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf([
    'tempDir' => __DIR__ . '/temp',
    'format' => [200, 300]
]);

$mpdf->WriteHTML($contenido, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output(); // Puedes especificar un nombre de archivo aquí si lo deseas, por ejemplo: $mpdf->Output("qr/qr_" . $depa_id . ".pdf", 'F');
exit;
?>
