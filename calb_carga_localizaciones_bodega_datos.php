<?php
include('conexion.php');
$id = $_GET['id'];

$qsql = "SELECT * FROM carga_localizaciones_bodega WHERE calb_id = '$id'";
$rs = mysql_query($qsql);
$i = 0;

$nombre = mysql_result($rs, $i, 'calb_nombre');
$seccion = mysql_result($rs, $i, 'calb_seccion');
$x = mysql_result($rs, $i, 'calb_x');
$y = mysql_result($rs, $i, 'calb_y');
$estado = mysql_result($rs, $i, 'calb_estado');

echo $nombre . '||' . $seccion . '||' . $x . '||' . $y . '||' . $estado;
?>
