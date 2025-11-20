<?php  
include('conexion.php');
$codigo_guia = $_REQUEST["codigo_guia"];

$qsql = "
    SELECT DISTINCT
        cd.cade_id,
        cd.cade_guia,
        c.carg_guia,
        c.carg_fecha_registro,
        COALESCE(cd.cade_guia, c.carg_guia, '') AS guia_nombre
    FROM carga_detalles cd
    INNER JOIN carga c ON c.carg_id = cd.carg_id
    WHERE (cd.cade_guia LIKE '%$codigo_guia%' AND cd.cade_guia IS NOT NULL AND cd.cade_guia != '')
       OR (c.carg_guia LIKE '%$codigo_guia%' AND c.carg_guia IS NOT NULL AND c.carg_guia != '')
    ORDER BY guia_nombre
";

$rs = mysql_query($qsql);
$json = array();

while($row = mysql_fetch_array($rs))
{
    // Solo agregar si la guía no está vacía
    $guia_nombre = trim($row['guia_nombre']);
    if (!empty($guia_nombre)) {
        $json[] = array(
            'id' => $row['cade_id'],
            'label' => $guia_nombre,
            'cade_guia' => $row['cade_guia'],
            'carg_guia' => $row['carg_guia'],
            'fecha_registro' => $row['carg_fecha_registro']
        );
    }
}

echo json_encode($json);
?>