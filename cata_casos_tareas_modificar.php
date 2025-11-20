<?php include('conexion.php');

$id=$_GET['id'];

$m_cate_nombre=$_POST['m_cate_nombre'];

$m_cate_descripcion=$_POST['m_cate_descripcion'];

$m_cate_fecha_cierre=$_POST['m_cate_fecha_cierre'];

$m_cate_estado=$_POST['m_cate_estado'];

$m_depa_id=$_POST['m_depa_id'];

$m_usua_id=$_POST['m_usua_id'];

$qsql = "update casos_tareas set 

cate_nombre='$m_cate_nombre', 

cate_descripcion='$m_cate_descripcion', 

cate_fecha_cierre='$m_cate_fecha_cierre', 

cate_estado='$m_cate_estado', 

depa_id='$m_depa_id', 

usua_id='$m_usua_id'

where cate_id='$id'";

mysql_query($qsql);

?>



