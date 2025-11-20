<?php
include('conexion.php');

// Debug: Mostrar todos los datos recibidos
echo "Datos POST recibidos:<br>";
print_r($_POST);
echo "<br><br>";

// Obtener datos del formulario
$m_liae_id = $_POST['m_liae_id'] ?? '';
$m_liae_nombre = $_POST['m_liae_nombre'] ?? '';
$m_pais_id = $_POST['m_pais_id'] ?? '';
$m_liae_prefijo = $_POST['m_liae_prefijo'] ?? '';
$m_liae_icao = !empty($_POST['m_liae_icao']) ? $_POST['m_liae_icao'] : null;
$m_liae_tres_digitos = !empty($_POST['m_liae_tres_digitos']) ? $_POST['m_liae_tres_digitos'] : null;
$m_liae_dk = !empty($_POST['m_liae_dk']) ? $_POST['m_liae_dk'] : null;

// Debug: Mostrar el ID específicamente
echo "ID recibido: '$m_liae_id'<br>";

// Validar campos obligatorios
if (empty($m_liae_id) || empty($m_liae_nombre) || empty($m_pais_id) || empty($m_liae_prefijo) || empty($m_liae_dk)) {
    die("Por favor, completa todos los campos obligatorios. ID: '$m_liae_id'");
}

// Empezar construcción del UPDATE
$update_query = "UPDATE lineas_aereas SET
    liae_nombre = '$m_liae_nombre',
    pais_id = '$m_pais_id',
    liae_prefijo = '$m_liae_prefijo',
    liae_icao = " . ($m_liae_icao !== null ? "'$m_liae_icao'" : "NULL") . ",
    liae_tres_digitos = " . ($m_liae_tres_digitos !== null ? "'$m_liae_tres_digitos'" : "NULL") . ",
    liae_dk = " . ($m_liae_dk !== null ? "'$m_liae_dk'" : "NULL");

// Manejar archivo si se subió uno nuevo
if (!empty($_FILES["m_liae_ref"]["name"])) {
    $tmp_name = $_FILES["m_liae_ref"]["tmp_name"];
    $name = $_FILES["m_liae_ref"]["name"];
    $upload_path = "img/liae_ref/" . basename($name);
    
    if (move_uploaded_file($tmp_name, $upload_path)) {
        $update_query .= ", liae_ref = '$name'";
    } else {
        die("Error al subir el archivo.");
    }
}

// Completar la consulta con WHERE
$update_query .= " WHERE liae_id = '$m_liae_id'";

// Mostrar la consulta final para depurar
echo "Consulta ejecutada: <br>$update_query<br><br>";

// Ejecutar consulta
if (!mysql_query($update_query)) {
    die("❌ Error al actualizar en la base de datos: " . mysql_error());
}

echo "✅ Registro modificado exitosamente.";
?>