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
    $cadd_piezas = mysql_real_escape_string($_POST["cadd_piezas_n"]);
    $cadd_cantidad = mysql_real_escape_string($_POST["cadd_cantidad_n"]);
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
    $sql = "INSERT INTO carga_detalle_detalles(cadd_descripcion, cadd_peso, cati_id, cade_id,cadd_piezas,cadd_cantidad)
        VALUES ('$cadd_descripcion', '$cadd_peso', '$cati_id', '$cade_id','$cadd_piezas','$cadd_cantidad')";

    if (!mysql_query($sql)) {
      die("Error al insertar en carga_detalle_detalles: " . mysql_error());
    }

    $last_id = mysql_insert_id();

    // Obtener la suma de cadd_peso y cadd_piezas de todos los registros que coincidan con el mismo cade_id
    $last_id = mysql_insert_id();

// Obtener la suma de cadd_peso, cadd_piezas y cadd_cantidad de todos los registros del mismo cade_id
$sql_sum = "SELECT 
                SUM(cadd_peso) AS total_peso, 
                SUM(cadd_piezas) AS total_piezas, 
                SUM(cadd_cantidad) AS total_cantidad
            FROM carga_detalle_detalles 
            WHERE cade_id = '$cade_id'";

$result_sum = mysql_query($sql_sum);

if (!$result_sum) {
  die("Error al calcular las sumas: " . mysql_error());
}

$row = mysql_fetch_assoc($result_sum);
$total_peso = $row['total_peso'];
$total_piezas = $row['total_piezas'];
$total_cantidad = $row['total_cantidad'];

// Actualizar la tabla carga_detalles con las sumas calculadas
$sql_update = "UPDATE carga_detalles 
               SET cade_peso = '$total_peso', 
                   cade_piezas = '$total_piezas',
                   cade_cantidad = '$total_cantidad'
               WHERE cade_id = '$cade_id'";

if (!mysql_query($sql_update)) {
  die("Error al actualizar carga_detalles: " . mysql_error());
}



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


// Buscar la carg_id, guía, peso y cantidad del cade_id
$sql = "SELECT * FROM carga_detalles WHERE cade_id = '$cade_id'";
$carga_detalle_info = mysql_fetch_assoc(mysql_query($sql));
$carg_id = $carga_detalle_info["carg_id"];
$cade_cantidad = $carga_detalle_info["cade_cantidad"];
$cade_guia = $carga_detalle_info["cade_guia"];
$cade_peso = $carga_detalle_info["cade_peso"]; // <-- peso total de la guía

// Obtener aerolínea (liae_id) de la carga para AIT por aerolínea
$row_carga = mysql_fetch_assoc(mysql_query("SELECT liae_id FROM carga WHERE carg_id = '$carg_id'"));
$aerolinea = isset($row_carga["liae_id"]) && $row_carga["liae_id"] !== '' ? $row_carga["liae_id"] : null;
// Con aerolínea: solo AIT de esa línea (no todas tienen AIT → no usar genérico). Sin aerolínea: AIT genérico si existe.
if ($aerolinea !== null && $aerolinea !== '') {
   $liae_esc = mysql_real_escape_string($aerolinea);
   $q_ait_case = "SELECT case_id AS ait_case_id FROM carga_servicios WHERE case_es_ait = 1 AND liae_id='$liae_esc' LIMIT 1";
   $q_ait_monto = "SELECT case_monto AS ait_monto FROM carga_servicios WHERE case_es_ait = 1 AND liae_id='$liae_esc' LIMIT 1";
} else {
   $q_ait_case = "SELECT case_id AS ait_case_id FROM carga_servicios WHERE case_es_ait = 1 AND liae_id IS NULL LIMIT 1";
   $q_ait_monto = "SELECT case_monto AS ait_monto FROM carga_servicios WHERE case_es_ait = 1 AND liae_id IS NULL LIMIT 1";
}
$ait_case_id = obtener_valor($q_ait_case, 'ait_case_id');
$ait_monto = (float) obtener_valor($q_ait_monto, 'ait_monto');
$AIT = $ait_monto * (float) $cade_peso;

$cade_tipo_id_serv = isset($carga_detalle_info['cade_tipo_id']) ? (int) $carga_detalle_info['cade_tipo_id'] : 0;
$ait_case_esc = ($ait_case_id !== '' && $ait_case_id !== null) ? mysql_real_escape_string($ait_case_id) : '';
$sql_ait_servicio = ($ait_case_esc !== '') ? " OR (cs.case_es_ait = 1 AND cs.case_id = '$ait_case_esc')" : '';

// INSERTAR CARGOS (AUTOMÁTICOS Y NO AUTOMÁTICOS) QUE NO EXISTAN AÚN PARA ESA GUÍA
$sql = "INSERT INTO carga_cargos (
    carg_id, cade_guia, case_id, caca_monto, caca_fecha, usua_id, caca_facturado, caca_itbms
)
SELECT
    '$carg_id',
    '$cade_guia',
    cs.case_id,
    (
      CASE
        WHEN cs.cadt_id IN (7,8) THEN $cade_cantidad * cs.case_monto
        WHEN cs.case_peso_minimo > 0 AND $cade_peso > cs.case_peso_minimo THEN $cade_peso * cs.case_monto_max
        ELSE cs.case_monto
      END
      +
      CASE
        WHEN cs.case_ait = 1 AND cs.cadt_id NOT IN (7,8) THEN $AIT
        ELSE 0
      END
    ) AS caca_monto,
    NOW(),
    $usua_id,
    0,
    CASE
        WHEN cs.cadt_id IN (7,8) THEN ($cade_cantidad * cs.case_monto) * 0.07
        WHEN cs.case_itbms = 1 AND cs.case_peso_minimo > 0 AND $cade_peso > cs.case_peso_minimo THEN ($cade_peso * cs.case_monto_max) * 0.07
        WHEN cs.case_itbms = 1 THEN cs.case_monto * 0.07
        ELSE 0
    END
FROM carga_servicios cs
LEFT JOIN carga_cargos cc
    ON cc.cade_guia = '$cade_guia'
    AND cc.case_id = cs.case_id
WHERE cc.case_id IS NULL
  AND (
        (cs.case_automatico = 1 AND cs.cadt_id = 0)
        OR (cs.case_automatico = 1 AND cs.cadt_id = $cade_tipo_id_serv)
        OR (cs.case_automatico = 0 AND cs.cadt_id = $cade_tipo_id_serv)
        $sql_ait_servicio
      )
";

mysql_query($sql);

// Actualizar AIT: asignar monto y case_id correcto por aerolínea (cualquier fila AIT insertada)
if ($ait_case_esc !== '') {
   $sql = "UPDATE carga_cargos cc
        INNER JOIN carga_servicios cs ON cs.case_id = cc.case_id AND cs.case_es_ait = 1 AND cs.cadt_id NOT IN (7,8)
        SET cc.caca_monto = '$AIT', cc.case_id = '$ait_case_esc'
        WHERE cc.cade_guia = '$cade_guia'";
   mysql_query($sql);
}

    break;
}
