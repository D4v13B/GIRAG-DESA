<?php include('conexion.php');
$id=$_GET['id'];
$m_imec_nombre=$_POST['m_imec_nombre'];
$qsql = "update impacto_economico set 
imec_nombre='$m_imec_nombre'
where imec_id='$id'";
mysql_query($qsql);
?>

