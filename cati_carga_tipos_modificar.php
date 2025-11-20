<?php include('conexion.php');
$id=$_GET['id'];
$m_cati_nombre=$_POST['m_cati_nombre'];
$qsql = "update carga_tipos set 
cati_nombre='$m_cati_nombre'
where cati_id='$id'";
mysql_query($qsql);
?>

