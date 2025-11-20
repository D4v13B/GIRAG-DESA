<?php
header("Content-Type: application/json");
session_start();
include "../conexion.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener todos los vuelos con JOINs para obtener nombres reales
    $sql = "SELECT 
                v.vuel_id,
                v.vuel_fecha,
                v.vuel_codigo,
                v.vuel_origen,
                v.vuel_destino,
                
                -- Datos de la aerolínea
                la.liae_nombre as aerolinea_nombre,
                
                -- Datos del aeropuerto de origen
                ao.aeco_nombre as aeropuerto_origen,
                ao.aeco_codigo as codigo_origen,
                
                -- Datos del aeropuerto de destino  
                ad.aeco_nombre as aeropuerto_destino,
                ad.aeco_codigo as codigo_destino
                
            FROM vuelos v
            
            -- JOIN con líneas aéreas
            LEFT JOIN lineas_aereas la ON v.liae_id = la.liae_id
            
            -- JOIN con aeropuerto de origen
            LEFT JOIN aereopuertos_codigos ao ON v.aeco_id_origen = ao.aeco_id
            
            -- JOIN con aeropuerto de destino
            LEFT JOIN aereopuertos_codigos ad ON v.aeco_id_destino = ad.aeco_id
            
            ORDER BY v.vuel_fecha DESC";
    
    $res = mysql_query($sql);
   
    if (!$res) {
        echo json_encode(['success' => false, 'message' => 'Error en consulta: ' . mysql_error()]);
        exit;
    }
   
    $vuelos = [];
    while ($row = mysql_fetch_assoc($res)) {
        // Mapear a los nombres que espera el frontend
        $vuelos[] = [
            'id' => $row['vuel_id'],
            'aerolinea' => $row['aerolinea_nombre'] ?? 'Sin aerolínea',
            'salida' => ($row['codigo_origen'] ? $row['codigo_origen'] . ' - ' : '') . ($row['aeropuerto_origen'] ?? $row['vuel_origen']),
            'entrada' => ($row['codigo_destino'] ? $row['codigo_destino'] . ' - ' : '') . ($row['aeropuerto_destino'] ?? $row['vuel_destino']),
            'fecha' => $row['vuel_fecha'] ?? 'Sin fecha',
            'codigo' => $row['vuel_codigo'] ?? 'Sin código'
        ];
    }
    echo json_encode($vuelos);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insertar vuelo individual
    $aerolinea_id = trim($_POST['aerolinea'] ?? '');
    $origen_id = trim($_POST['salida'] ?? '');
    $destino_id = trim($_POST['entrada'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    
    if (empty($aerolinea_id) || empty($origen_id) || empty($destino_id) || empty($codigo)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }
    
    // Escapar para evitar inyecciones SQL
    $aerolinea_id = mysql_real_escape_string($aerolinea_id);
    $origen_id = mysql_real_escape_string($origen_id);
    $destino_id = mysql_real_escape_string($destino_id);
    $codigo = mysql_real_escape_string($codigo);

    // Usar el mismo patrón que funciona en el otro código
    $stmt = "INSERT INTO vuelos (
        aeco_id_origen,
        aeco_id_destino,
        vuel_codigo,
        liae_id
    ) VALUES (
        '$origen_id',
        '$destino_id',
        '$codigo',
        '$aerolinea_id'
    )";

    $query_success = false;
    try {
        $res = mysql_query($stmt); // Usando tu función mysql_query personalizada
        if ($res) {
            $query_success = true; // Éxito - continuar con el resto del código
        }
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo json_encode([
                "success" => false,
                "message" => "El vuelo ya existe."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error en la base de datos: " . $e->getMessage()
            ]);
        }
        exit; // Sale aquí si hay error
    }

    // Si llegó hasta aquí, fue exitoso
    if ($query_success) {
        echo json_encode(['success' => true, 'message' => 'Vuelo creado exitosamente']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>