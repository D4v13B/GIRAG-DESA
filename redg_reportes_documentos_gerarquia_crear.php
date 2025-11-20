
<!-- Contiene los cambios, para que se puedan crear carpetas dentro de la carpeta raíz -->
<?php

include('conexion.php');

// Obtener datos del POST
$i_redg_nombre = isset($_POST['i_redg_nombre']) ? $_POST['i_redg_nombre'] : '';
$i_redg_nivel = isset($_POST['i_redg_nivel']) ? (int)$_POST['i_redg_nivel'] : 0;
$i_redg_padre = isset($_POST['i_redg_padre']) ? (int)$_POST['i_redg_padre'] : 0;



// Ajustar el nivel y padre para la raíz
if ($i_redg_padre == 0) {
    $i_redg_nivel = 1; // Nivel 1 para la raíz
} else {
    // Si no es la raíz, se puede calcular el nivel basado en el padre si es necesario
    // Por ahora, dejamos el nivel proporcionado en el formulario
}

// Construir la consulta SQL
$qsql = "INSERT INTO reportes_documentos_gerarquia 
(
    redg_nombre,
    redg_nivel,
    redg_padre
) 
VALUES (
    '$i_redg_nombre', 
    '$i_redg_nivel', 
    '$i_redg_padre'
)";

// Ejecutar la consulta SQL
$result = mysql_query($qsql);


?>
