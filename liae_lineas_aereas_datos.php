<?php
include('conexion.php');
$id = $_GET['id'];

$qsql = "SELECT * FROM lineas_aereas WHERE liae_id = '$id'";
$rs = mysql_query($qsql); // Asumes que estás usando mysql_* y no mysqli_* ni PDO

$i = 0;
echo mysql_result($rs, $i, 'liae_id') . '||';
echo mysql_result($rs, $i, 'liae_nombre') . '||';
echo mysql_result($rs, $i, 'pais_id') . '||';
echo mysql_result($rs, $i, 'liae_ref') . '||';
echo mysql_result($rs, $i, 'liae_prefijo') . '||';
echo mysql_result($rs, $i, 'liae_icao') . '||';
echo mysql_result($rs, $i, 'liae_tres_digitos') . '||';
echo mysql_result($rs, $i, 'liae_dk');
?>
