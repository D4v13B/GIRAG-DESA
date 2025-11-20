<?php include('conexion.php');
$id=$_GET['id'];
$m_depa_nombre=$_POST['m_depa_nombre'];
$m_depa_correo=$_POST['m_depa_correo'];
$qsql = "update departamentos set 
depa_nombre='$m_depa_nombre', 
depa_correo='$m_depa_correo'
where depa_id='$id'";
mysql_query($qsql);
?>

