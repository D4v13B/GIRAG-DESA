<?php
include('conexion.php');

$id = $_GET['id'];

$qsql = "
    SELECT cd.*, c.carg_fecha_registro
    FROM carga_detalles cd
    INNER JOIN carga c ON c.carg_id = cd.carg_id
    WHERE cd.cade_id = '$id'
";

$rs = mysql_query($qsql);
$i = 0;

echo mysql_result($rs, $i, 'cade_') . '||';
echo mysql_result($rs, $i, 'cade_id') . '||';
echo mysql_result($rs, $i, 'carg_fecha_registro') . '||';
echo mysql_result($rs, $i, 'cade_peso') . '||';
echo mysql_result($rs, $i, 'cade_piezas') . '||';
echo mysql_result($rs, $i, 'cade_desc') . '||';
echo mysql_result($rs, $i, 'cade_guia') . '||';
echo mysql_result($rs, $i, 'cade_tipo_id') . '||';

?>

