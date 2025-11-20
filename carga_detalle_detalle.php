<?php

include "./../funciones.php";
include "./../conexion.php";
session_start();
$usua_id = $_SESSION["login_user"];

switch ($_SERVER["REQUEST_METHOD"]) {
  case "GET":
    $cade_id = $_GET["cade_id"];

    $sql = "SELECT
    a.*,
    b.*,
    GROUP_CONCAT(clb.calb_nombre SEPARATOR ', ') AS localizaciones
FROM carga_detalle_detalles a
         JOIN carga_detalle_tipo b ON a.cati_id = b.cade_tipo_id
         LEFT JOIN carga_detalle_detalle_localizacion cdl ON a.cadd_id = cdl.cadd_id
         LEFT JOIN carga_localizaciones_bodega clb ON cdl.calb_id = clb.calb_id
WHERE a.cade_id = $cade_id
GROUP BY a.cadd_id";

    $res = mysql_query($sql);

    $data = [];

    while ($row = mysql_fetch_assoc($res)) {
      $data[] = $row;
    }

    echo json_encode($data);

    break;

  case "POST":

    // Escapar datos para evitar inyección SQL
    $cadd_descripcion = mysql_real_escape_string($_POST["cadd_descripcion_n"]);
    $cadd_peso = mysql_real_escape_string($_POST["cadd_peso_n"]);
    $cati_id = mysql_real_escape_string($_POST["cade_tipo_id"]);
    $cade_id = mysql_real_escape_string($_POST["cade_id"]);
    $calb_id_raw = $_POST["calb_id"];

    // Convertir string a arreglo y limpiar espacios
    $calb_id = array_map('trim', explode(',', $calb_id_raw));

    // Validar que todos los elementos son números (por seguridad)
    $calb_id = array_filter($calb_id, function ($id) {
      return is_numeric($id);
    });

    // Insertar en carga_detalle_detalles
    $sql = "INSERT INTO carga_detalle_detalles(cadd_descripcion, cadd_peso, cati_id, cade_id)
        VALUES ('$cadd_descripcion', '$cadd_peso', '$cati_id', '$cade_id')";

    if (!mysql_query($sql)) {
      die("Error al insertar en carga_detalle_detalles: " . mysql_error());
    }

    $last_id = mysql_insert_id();

    // Insertar localizaciones si hay
    if (!empty($calb_id)) {
      $values_localizaciones = "";

      foreach ($calb_id as $local) {
        $values_localizaciones .= "('$last_id', '$local'), ";
      }

      $values_localizaciones = rtrim($values_localizaciones, ', ');

      $sql = "INSERT INTO carga_detalle_detalle_localizacion(cadd_id, calb_id)
             VALUES $values_localizaciones";

      if (!mysql_query($sql)) {
        die("Error al insertar en carga_detalle_localizacion: " . mysql_error());
      }
    }

    break;
}
