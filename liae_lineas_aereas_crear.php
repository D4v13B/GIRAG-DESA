<?php
include('conexion.php');

// Obtener datos del formulario
$i_liae_nombre = $_POST['i_liae_nombre'] ?? '';
$i_pais_id = $_POST['i_pais_id'] ?? '';
$i_liae_prefijo = $_POST['i_liae_prefijo'] ?? '';
$i_liae_icao = !empty($_POST['i_liae_icao']) ? $_POST['i_liae_icao'] : null;
$i_liae_tres_digitos = !empty($_POST['i_liae_tres_digitos']) ? $_POST['i_liae_tres_digitos'] : null;
$i_liae_dk = !empty($_POST['i_liae_dk']) ? $_POST['i_liae_dk'] : null;

// Validación
if (empty($i_liae_nombre) || empty($i_pais_id) || empty($i_liae_prefijo) || empty($i_liae_dk)) {
    die("Por favor, completa todos los campos obligatorios.");
}

// Inicializar variable para el nombre del archivo
$nombre_archivo = null;

// Manejar la carga de archivos si existe
if (!empty($_FILES["i_liae_ref"]["name"])) {
    $tmp_name = $_FILES["i_liae_ref"]["tmp_name"];
    $name = $_FILES["i_liae_ref"]["name"];
    
    // Crear directorio si no existe
    $upload_dir = "img/liae_ref/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generar nombre único para evitar conflictos
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $nombre_archivo;
    
    // Validar tipo de archivo (opcional)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $allowed_types)) {
        die("Tipo de archivo no permitido. Solo se permiten: " . implode(', ', $allowed_types));
    }
    
    // Subir archivo al servidor
    if (!move_uploaded_file($tmp_name, $upload_path)) {
        die("Error al subir el archivo.");
    }
}

// Consulta con el nombre del archivo si existe
$qsql = "INSERT INTO lineas_aereas (liae_nombre, pais_id, liae_prefijo, liae_icao, liae_tres_digitos, liae_ref, liae_dk)
VALUES ('$i_liae_nombre', '$i_pais_id', '$i_liae_prefijo', " .
    ($i_liae_icao !== null ? "'$i_liae_icao'" : "NULL") . ", " .
    ($i_liae_tres_digitos !== null ? "'$i_liae_tres_digitos'" : "NULL") . ", " .
    ($nombre_archivo !== null ? "'$nombre_archivo'" : "NULL") . ", " .
    ($i_liae_dk !== null ? "'$i_liae_dk'" : "NULL") . ")";

// Ejecutar la consulta
if (!mysql_query($qsql)) {
    die("Error al insertar en la base de datos: " . mysql_error());
}

// Confirmación de éxito
echo "Registro insertado exitosamente.";
?>