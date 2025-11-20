<?php include('conexion.php'); ?>
<?php
$usuario=$_GET['usuario'];
$id=$_GET['uid']; 
$estado=$_GET['estado'];

$qsql ="update usuarios set usua_activo='$estado'";
$qsql = $qsql . " where usua_id='$id'";
mysql_query($qsql);
//echo $qsql;
//actualizo las tareas

$qsql ="update asignaciones_tareas set usua_id='$usuario' ";
$qsql = $qsql . " where usua_id='$id'";
mysql_query($qsql);
?>