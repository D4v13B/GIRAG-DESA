<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

error_log("Recibida petición de autocompletado para aeropuertos con término: " . $termino);

try {
    // Obtener el término de búsqueda
    $termino = isset($_GET['codigo_aeropuerto']) ? trim($_GET['codigo_aeropuerto']) : '';
    
    if (empty($termino)) {
        echo json_encode([]);
        exit;
    }
    
    // Escapar el término de búsqueda para prevenir inyección SQL
    $termino_escapado = mysql_real_escape_string($termino);
    
    // Consulta para buscar aeropuertos
    $sql = "SELECT aeco_id, aeco_codigo, aeco_nombre, 
            (SELECT pais_nombre FROM paises WHERE pais_id=a.pais_id) pais
            FROM aereopuertos_codigos a
            WHERE aeco_nombre LIKE '%$termino_escapado%' 
            OR aeco_codigo LIKE '%$termino_escapado%'
            ORDER BY aeco_nombre";
    
    $resultado = mysql_query($sql);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . mysql_error());
    }
    
    $resultados = [];
    while ($row = mysql_fetch_assoc($resultado)) {
        $resultados[] = [
            'id' => $row['aeco_id'],
            'value' => $row['aeco_nombre'],
            'label' => $row['aeco_nombre'] . ' (' . $row['aeco_codigo'] . ')',
            'codigo' => $row['aeco_codigo']
        ];
    }
    error_log("La consulta SQL es: " . $sql);
    error_log("Resultados obtenidos: " . print_r($resultados, true));
    
    echo json_encode($resultados);
    
} catch (Exception $e) {
    error_log("Error en obtener_aeropuertos.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

mysql_close();
?>