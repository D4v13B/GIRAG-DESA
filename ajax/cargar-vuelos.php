<?php
// Limpiar cualquier output previo
ob_clean();

include "../conexion.php";
require "../PHPExcel/PHPExcel.php";
require "../excelReader/excel_reader2.php";
require "../excelReader/SpreadsheetReader.php";

// Desactivar la visualización de errores para evitar contaminar el JSON
error_reporting(0);
ini_set('display_errors', 0);

// Asegurar que devolvemos JSON
header('Content-Type: application/json; charset=utf-8');

// Solo aceptar método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

if (!isset($_FILES["excel"]["tmp_name"]) || empty($_FILES["excel"]["tmp_name"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Archivo no recibido"]);
    exit;
}

// Ruta de guardado
$path = "../vuelosExcel/";
$nombreArchivo = time() . "-" . basename($_FILES["excel"]["name"]);
$tmpName = $_FILES["excel"]["tmp_name"];

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

// Mover archivo
if (!move_uploaded_file($tmpName, $path . $nombreArchivo)) {
    echo json_encode(["success" => false, "message" => "No se pudo guardar el archivo"]);
    exit;
}

// Registrar en tabla de documentos
mysql_query("INSERT INTO vuelos_docs(vudo_nombre) VALUES('".mysql_real_escape_string($nombreArchivo)."')");

// Leer archivo Excel
$reader = new SpreadsheetReader($path . $nombreArchivo);

$valores = [];
$errores = [];
$vuelosExistentes = [];
$vuelosInsertados = 0;
$vuelosSinInsertar = 0;

// Preparar lista de códigos para chequear duplicados
$codigosArchivo = [];
foreach ($reader as $row) {
    if (!isset($row[3])) continue;
    $codigosArchivo[] = strtoupper(trim($row[3]));
}

// Consultar duplicados existentes en la BD
if (!empty($codigosArchivo)) {
    $codigosStr = "'" . implode("','", array_map('mysql_real_escape_string', $codigosArchivo)) . "'";
    $sqlExistentes = "SELECT UPPER(TRIM(vuel_codigo)) as codigo FROM vuelos WHERE UPPER(TRIM(vuel_codigo)) IN ($codigosStr)";
    $resExistentes = mysql_query($sqlExistentes);
    while ($rowExist = mysql_fetch_assoc($resExistentes)) {
        $vuelosExistentes[] = $rowExist['codigo'];
    }
}

foreach ($reader as $key => $row) {
    if (!isset($row[0], $row[1], $row[2], $row[3])) continue;

    $liae = mysql_real_escape_string(trim($row[0]));
    $origen = mysql_real_escape_string(trim($row[1]));
    $destino = mysql_real_escape_string(trim($row[2]));
    $codigoVuelo = strtoupper(mysql_real_escape_string(trim($row[3])));

    if (empty($liae) || empty($origen) || empty($destino) || empty($codigoVuelo)) continue;

    // Si ya existe, marcar y continuar
    if (in_array($codigoVuelo, $vuelosExistentes)) {
        $vuelosSinInsertar++;
        continue;
    }

    // Validación línea aérea
    $resLinea = mysql_query("SELECT liae_id FROM lineas_aereas WHERE liae_nombre='$liae' LIMIT 1");
    if (!$resLinea || mysql_num_rows($resLinea) == 0) {
        $errores[] = "Vuelo $codigoVuelo: No se encontró la línea aérea '$liae'";
        $vuelosSinInsertar++;
        continue;
    }
    $liaeId = mysql_fetch_assoc($resLinea)["liae_id"];

    // Verificar aeropuerto origen
    $resOrigen = mysql_query("SELECT aeco_id FROM aereopuertos_codigos WHERE aeco_codigo='$origen' LIMIT 1");
    if (!$resOrigen || mysql_num_rows($resOrigen) == 0) {
        $errores[] = "Vuelo $codigoVuelo: No se encontró el aeropuerto de origen '$origen'";
        $vuelosSinInsertar++;
        continue;
    }
    $aecoOrigen = mysql_fetch_assoc($resOrigen)["aeco_id"];

    // Verificar aeropuerto destino
    $resDestino = mysql_query("SELECT aeco_id FROM aereopuertos_codigos WHERE aeco_codigo='$destino' LIMIT 1");
    if (!$resDestino || mysql_num_rows($resDestino) == 0) {
        $errores[] = "Vuelo $codigoVuelo: No se encontró el aeropuerto de destino '$destino'";
        $vuelosSinInsertar++;
        continue;
    }
    $aecoDestino = mysql_fetch_assoc($resDestino)["aeco_id"];

    // Preparar INSERT
    $valores[] = "('$liaeId', '$aecoOrigen', '$aecoDestino', '$codigoVuelo')";
}

// INSERT masivo con IGNORE para no romper si hay duplicados
if (!empty($valores)) {
    $valuesInsert = implode(',', $valores);
    $sql = "INSERT IGNORE INTO vuelos (liae_id, aeco_id_origen, aeco_id_destino, vuel_codigo) VALUES $valuesInsert";
    $res = mysql_query($sql);
    if ($res) {
        $vuelosInsertados = mysql_affected_rows();
    } else {
        $errores[] = "Error al insertar los vuelos: " . mysql_error();
    }
}

// Preparar mensaje final
$mensaje = "";
if ($vuelosInsertados > 0) $mensaje .= "Vuelos insertados: $vuelosInsertados\n";
if ($vuelosSinInsertar > 0) $mensaje .= "Vuelos no insertados: $vuelosSinInsertar\n";
if (!empty($vuelosExistentes)) $mensaje .= "Los siguientes vuelos ya existen: " . implode(', ', $vuelosExistentes) . "\n";
if (!empty($errores)) {
    $mensaje .= "Sin información de aereopuertos:\n";
    foreach ($errores as $error) $mensaje .= " • $error\n";
}

if ($vuelosInsertados === 0 && empty($errores)) {
    $mensaje = "No se insertó ningún vuelo. Todos los vuelos ya existen.\n";
}

echo json_encode([
    "success" => true,
    "message" => $mensaje,
    "vuelos_insertados" => $vuelosInsertados,
    "vuelos_sin_insertar" => $vuelosSinInsertar,
    "vuelos_existentes" => $vuelosExistentes,
    "errores" => $errores
]);
?>
