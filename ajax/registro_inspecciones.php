<?php
// REGISTRO DONDE SE SELECCIONA EL TIPO DE INSPECCIÓN
include('../conexion.php');
include('../funciones.php');
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['login_user'])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit;
}

$usuario = $_SESSION['login_user'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar y sanitizar la entrada
    $inti_id = isset($_POST["inti_id"]) ? mysql_real_escape_string($_POST["inti_id"]) : null;
    
    if (!$inti_id) {
        http_response_code(400);
        echo json_encode(["error" => "inti_id es requerido"]);
        exit;
    }

    $sql = "INSERT INTO inspecciones (
        inti_id,
        usua_id_inspeccion,
        insp_fecha,
        insp_hora
    ) VALUES (
        '$inti_id',
        '$usuario',
        NOW(),
        NOW()
    )";

    // Ejecución de la consulta
    $result = mysql_query($sql);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => "Error en la base de datos: " . mysql_error()]);
        exit;
    }

    $last_insert_id = mysql_insert_id();

    // Establecer el tipo de contenido a JSON
    header('Content-Type: application/json');
    echo json_encode(["insp_id" => $last_insert_id]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Consulta para obtener todos los datos
    $sql = "SELECT
                i.insp_id,
                u.usua_nombre AS usua_id_inspeccion,
                i.insp_fecha,
                it.inti_nombre AS tipo_inspeccion,
                i.insp_reporte,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM inspecciones_referencias ir
                    LEFT JOIN inspecciones_fotos inf ON ir.inre_id = inf.inre_id AND inf.info_tipo = '2'
                    WHERE ir.insp_id = i.insp_id AND inf.info_id IS NOT NULL
                ) THEN 'Sí' ELSE 'No' END AS completado
            FROM
                inspecciones i
            JOIN
                inspecciones_tipos it ON i.inti_id = it.inti_id
            LEFT JOIN
                usuarios u ON i.usua_id_inspeccion = u.usua_id
            ORDER BY
                i.insp_fecha DESC";

    $result = mysql_query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => "Error en la base de datos: " . mysql_error()]);
        exit;
    }

    $inspecciones = [];
    while ($row = mysql_fetch_assoc($result)) {
        $inspecciones[] = $row;
    }

    // Establecer el tipo de contenido a JSON
    header('Content-Type: application/json');
    echo json_encode($inspecciones);

} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>
