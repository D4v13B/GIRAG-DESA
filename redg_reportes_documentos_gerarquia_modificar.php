<?php include('conexion.php');
$id=$_GET['id'];
$m_redg_nombre=$_POST['m_redg_nombre'];
$m_redg_nivel=$_POST['m_redg_nivel'];
$m_redg_padre=$_POST['m_redg_padre'];
$qsql = "update reportes_documentos_gerarquia set 
redg_nombre='$m_redg_nombre', 
redg_nivel='$m_redg_nivel', 
redg_padre='$m_redg_padre'
where redg_id='$id'";
mysql_query($qsql);
?>

