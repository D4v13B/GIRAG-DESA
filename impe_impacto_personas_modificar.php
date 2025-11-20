<?php include('conexion.php');
$id=$_GET['id'];
$m_impe_impacto=$_POST['m_impe_impacto'];
$qsql = "update impacto_personas set 
impe_impacto='$m_impe_impacto'
where impe_id='$id'";
mysql_query($qsql);
?>

