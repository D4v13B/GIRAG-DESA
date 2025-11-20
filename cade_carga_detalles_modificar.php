<?php include('conexion.php');
$id=$_GET['id'];
$m_cade_peso=$_POST['m_cade_peso'];
$m_cade_piezas=$_POST['m_cade_piezas'];
$m_cade_desc=$_POST['m_cade_desc'];
$m_cade_guia=$_POST['m_cade_guia'];
$m_cade_tipo_id=$_POST['m_cade_tipo_id'];
$qsql = "update carga_detalles set 
cade_peso='$m_cade_peso', 
cade_piezas='$m_cade_piezas', 
cade_desc='$m_cade_desc', 
cade_guia='$m_cade_guia', 
cade_tipo_id='$m_cade_tipo_id'
where cade_id='$id'";
mysql_query($qsql);
?>

