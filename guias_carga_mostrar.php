<?php include('conexion.php'); ?>

<script src='jquery/sorter/tablesort.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.number.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.date.min.js'></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    
         <?php

// Validar conexión
if (!$conn) {
    http_response_code(500);
    echo "Error en la conexión con la base de datos.";
    exit;
}

// Validar entrada
$f_carg_guia = isset($_POST['f_carg_guia']) ? trim($_POST['f_carg_guia']) : '';
$f_carg_guia = "%$f_carg_guia%"; // Añadir comodines para la búsqueda LIKE

// Preparar consulta
$stmt = $conn->prepare("
    SELECT 
        carg_id,
        cati_id,
        carg_guia,
        vuel_id,
        aeco_id_destino_final,
        usua_id_creador,
        carg_fecha_registro,
        carg_recepcion_real,
        liae_id,
        caes_id
    FROM casos 
    WHERE carg_guia LIKE ? 
    LIMIT 100
");
$stmt->bind_param("s", $f_carg_guia);

// Ejecutar y verificar resultados
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['cati_id']}</td>
                    <td>{$row['carg_guia']}</td>
                    <td>{$row['vuel_id']}</td>
                    <td>{$row['aeco_id_destino_final']}</td>
                    <td>{$row['usua_id_creador']}</td>
                    <td>{$row['carg_fecha_registro']}</td>
                    <td>{$row['carg_recepcion_real']}</td>
                    <td>{$row['liae_id']}</td>
                    <td>{$row['caes_id']}</td>
                    <td>
                        <button class='btn btn-info btn-sm'>Ver</button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No se encontraron resultados</td></tr>";
    }
} else {
    echo "<tr><td colspan='10'>Error al ejecutar la consulta</td></tr>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>