<?php 
session_start();
$user_check=$_SESSION['login_user'];

include_once('conexion.php'); 
include('funciones.php'); 
include_once("PHPMailer_v5.1/class.phpmailer.php"); 

$id = $_GET['id'];


//saco el template
$qsql = "SELECT cote_detalle FROM correos_templates WHERE cote_nombre='Email Factura'";
$machote = obtener_valor($qsql, "cote_detalle");

//Debo reemplazar el nombre del cliente y el monto a pagar
$qsql ="SELECT clie_nombre, clie_mail, date_format(ingr_fecha, '%m') mes
FROM ingresos a, clientes b 
WHERE a.clie_id=b.clie_id 
AND ingr_id='$id'
";
$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
$mes_numero = mysql_result($rs, $i, 'mes');
$cliente = mysql_result($rs, $i, 'clie_nombre');
$clie_mail = mysql_result($rs, $i, 'clie_mail');
$clie_mail .= ';luis@e-integracion.com';
$mes = obtener_mes($mes_numero);

$machote = str_replace('[CLIENTE]',$cliente, $machote);
$machote = str_replace('[MES]',$mes, $machote);


enviar_email('', 'GRUPO ITEMU, S.A.', 'Factura de Servicio', $machote, $clie_mail, "facturas/facturas_" . $id . ".pdf");  


mysql_query("UPDATE ingresos SET ingr_notificada=1 WHERE ingr_id='$id'");
?>