<?php include('conexion.php');
$id=$_GET['id'];
$m_casu_descripcion=$_POST['m_casu_descripcion'];
$m_cacl_id=$_POST['m_cacl_id'];
$qsql = "update casos_subclasificacion set 
casu_descripcion='$m_casu_descripcion', 
cacl_id='$m_cacl_id'
where casu_id='$id'";
mysql_query($qsql);
?>

