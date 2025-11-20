<?php
use PHPMailer\PHPMailer\PHPMailer;

// Conexión a la base de datos
$server = "143.198.137.170";
$usuario = "dunderio_giragdesa";
$password = "Girag_2024";
$db_nombre = "dunderio_giragdesa";

require "./vendor/autoload.php";
include "./conexion.php";
include __DIR__ . "/funciones.php";
include __DIR__ . "/ajax/mailerConfig.php";

try {
   $pdo = new PDO("mysql:host=$server;dbname=$db_nombre;charset=utf8", $usuario, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   // Consulta de detalles con fecha notificada y sea import
   $stmt = $pdo->query("SELECT 
  cade_id, 
  cade_guia,
  cons_email, 
  cade_notificada_fecha 
FROM 
  carga_detalles a
INNER JOIN consignee b ON a.cons_id = b.cons_id 
WHERE 
  cade_notificada_fecha IS NOT NULL
  AND DATEDIFF(NOW(), cade_notificada_fecha) > 8;
");

   $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $hoy = new DateTime();
   foreach($res as $r){
      $fechaNotificada = new DateTime($r['cade_notificada_fecha']);
      $diferencia = $hoy->diff($fechaNotificada)->days;

      $mensaje = generarMensajeHTML($r["cade_guia"], $r["cade_id"], $diferencia);

      enviar_email($smtp_username, "GIRAG INFO", "RETRASO EN RECOGIDA DE CARGA", $mensaje , [$r["cons_email"]], $smtp_username, $smtp_password, new PHPMailer());

   }

   
} catch (PDOException $e) {
   echo "Error de conexión: " . $e->getMessage();
}

function generarMensajeHTML($guia, $id, $dias)
{
   return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc;">
  <h2 style="color: #333;">Notificación de Retraso en su Envío</h2>
  <p>Estimado cliente,</p>
  <p>Su envío con número de guía <strong>$guia</strong> (ID: <strong>$id</strong>) ha presentado un retraso en su recogida de más de <strong>$dias días</strong>.</p>
  <p style="margin-top: 30px;">Atentamente,<br><strong>Equipo de Logística de GIRAG</strong></p>
</div>
HTML;
}
