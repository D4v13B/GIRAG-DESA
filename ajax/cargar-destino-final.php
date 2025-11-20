<?php
include "../conexion.php";
require "../excelReader/excel_reader2.php";
require "../excelReader/SpreadsheetReader.php";

// Función para quitar tildes y normalizar texto
function normalizarTexto($texto) {
    $texto = strtolower(trim($texto));
    $texto = strtr(utf8_decode($texto),
        utf8_decode('áéíóúñü'),
        'aeiounu');
    return utf8_encode($texto);
}

switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        if (isset($_FILES["excel"]["full_path"]) && !empty($_FILES["excel"]["full_path"])) {
            $path = "../vuelosExcel/";
            $nombreArchivo = time() . "-" . $_FILES["excel"]["name"];
            $tmpName = $_FILES["excel"]["tmp_name"];

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            if (move_uploaded_file($tmpName, $path . $nombreArchivo)) {
                $reader = new SpreadsheetReader($path . $nombreArchivo);

                foreach ($reader as $key => $row) {
                    if ($key == 0) continue; // Saltar encabezados

                    $paisNombreExcel = $row[0];
                    $codigoAeropuerto = $row[1];
                    $nombreAeropuerto = $row[2];

                    // Validación de campos obligatorios
                    if (empty($paisNombreExcel) || empty($codigoAeropuerto) || empty($nombreAeropuerto)) {
                        continue;
                    }

                    // Normalizar país del Excel
                    $paisNormalizado = normalizarTexto($paisNombreExcel);

                    // Buscar ID del país normalizado
                    $sqlPais = "SELECT pais_id, pais_nombre FROM paises";
                    $resPais = mysql_query($sqlPais);
                    $paisId = null;

                    while ($pais = mysql_fetch_assoc($resPais)) {
                        $paisDB = normalizarTexto($pais['pais_nombre']);
                        if ($paisDB == $paisNormalizado) {
                            $paisId = $pais['pais_id'];
                            break;
                        }
                    }

                    if (!$paisId) {
                        // Saltar si no se encontró el país
                        continue;
                    }

                    // Insertar en la tabla aereopuertos_codigos
                    $codigoAeropuerto = mysql_real_escape_string($codigoAeropuerto);
                    $nombreAeropuerto = mysql_real_escape_string($nombreAeropuerto);

                    $insertSql = "INSERT INTO aereopuertos_codigos(pais_id, aeco_codigo, aeco_nombre)
                                  VALUES('$paisId', '$codigoAeropuerto', '$nombreAeropuerto')";
                    mysql_query($insertSql);
                }

                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "No se pudo subir el archivo"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Archivo no recibido"]);
        }
        break;
}
?>
