<?php
error_reporting(E_ALL);
ini_set("display_errors", "1");

include('conexion.php');
include('funciones.php');
$id = $_GET['id'];
$contenido = $_GET['contenido'];
$contenido = str_replace("|","?",$contenido);
$contenido = str_replace("[","&",$contenido);

ob_start();

include($contenido);
$output0 = ob_get_clean();

ob_start();
?>
<style>
table
{
border-width: 0px 0px 0px 0px;
border-spacing: 0;
border-collapse: collapse;
border-style: solid;
margin-left: auto;
margin-right: auto;
width: 100%;
}

td
{
font-family: Arial, Helvetica, sans-serif;
font-size: 8pt;
}
 
.encabezado
{
background-color:#04407c;
}

.encabezado td
{
text-align:center;
color:#ffffff;
font-size: 8pt;
}

.tabla_datos
{
    font-family: arial;
    font-size: 8pt;
}

.linea
{
    font-family: arial;
    font-size: 8pt;
}
</style>


<page>
<?php echo $output0 ?>
</page>
<?php
$content = ob_get_clean();
/*
include("mpdf/mpdf.php");

$mpdf=new mPDF('', 'Letter'); 
$mpdf->SetHTMLFooter($footer);
$mpdf->WriteHTML($content);
//$mpdf->Output();
$url_parsed = parse_url($urls . "/" . $contenido);
parse_str($url_parsed['query']);
//$id = $url_parts[0];
$mpdf->Output("facturas/facturas_" . $id .".pdf",'F');
*/

require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/custom/temp_files']);
$mpdf->WriteHTML($content);
$mpdf->Output("facturas/facturas_" . $id .".pdf",'F');


exit;
?>