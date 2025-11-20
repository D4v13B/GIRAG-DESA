<?php
header('Content-Type: application/json');
include('../conexion.php');
include('../funciones.php');

$response = array('success' => false, 'data' => array(), 'message' => '');

try {
    // Query para obtener inspecciones con sus departamentos
    $query = "SELECT 
                it.inti_id,
                it.inti_nombre,
                it.depa_id,
                id.depa_nombre
              FROM inspecciones_tipos it
              INNER JOIN departamentos id ON it.depa_id = id.depa_id
              ORDER BY it.inti_nombre ASC";
    
    $result = mysql_query($query);
    
    if (!$result) {
        $response['message'] = 'Error en la consulta: ' . mysql_error();
        echo json_encode($response);
        exit();
    }
    
    $inspections = array();
    
    while ($row = mysql_fetch_assoc($result)) {
        $inspections[] = array(
            'id' => intval($row['inti_id']),
            'name' => $row['inti_nombre'],
            'department_id' => intval($row['depa_id']),
            'department_name' => $row['depa_nombre']
        );
    }
    
    $response['success'] = true;
    $response['data'] = $inspections;
    $response['message'] = 'Inspecciones obtenidas correctamente';
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);

// Cerrar conexión
if(isset($conexion)) {
    mysql_close($conexion);
}
?>