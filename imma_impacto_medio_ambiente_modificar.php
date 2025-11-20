<?php include('conexion.php');
$id=$_GET['id'];
$m_imma_impacto=$_POST['m_imma_impacto'];
$qsql = "update impacto_medio_ambiente set 
imma_impacto='$m_imma_impacto'
where imma_id='$id'";
mysql_query($qsql);
?>

