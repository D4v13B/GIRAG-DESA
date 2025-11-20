<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

error_log("Recibida petición de autocompletado para aerolíneas con término: " . $termino);

try {
    // Obtener el término de búsqueda
    $termino = isset($_GET['codigo_aerolineas']) ? trim($_GET['codigo_aerolineas']) : '';
    
    if (empty($termino)) {
        echo json_encode([]);
        exit;
    }

    // Escapar el término de búsqueda para prevenir inyección SQL
    $termino_escapado = mysql_real_escape_string($termino);
    
    // Consulta para buscar aerolíneas
    $sql = "SELECT liae_id, liae_nombre, liae_prefijo, liae_icao 
            FROM lineas_aereas 
            WHERE liae_nombre LIKE '%$termino_escapado%' 
            OR liae_prefijo LIKE '%$termino_escapado%'
            OR liae_icao LIKE '%$termino_escapado%'
            ORDER BY liae_nombre";
    
    $resultado = mysql_query($sql);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . mysql_error());
    }
    
    $resultados = [];
    while ($row = mysql_fetch_assoc($resultado)) {
        $resultados[] = [
            'id' => $row['liae_id'],
            'value' => $row['liae_nombre'],
            'label' => $row['liae_nombre'] . ' (' . $row['liae_prefijo'] . ')',
            'codigo' => $row['liae_prefijo']
        ];
    }
    error_log("La consulta SQL es: " . $sql);
    error_log("Resultados obtenidos: " . print_r($resultados, true));

    echo json_encode($resultados);
    
} catch (Exception $e) {
    error_log("Error en obtener_aerolineas.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

mysql_close();
?>