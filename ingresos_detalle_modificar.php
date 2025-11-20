<?php include('conexion.php');
$id=$_POST['id'];
$m_prod_id=$_POST['m_prod_id'];
$m_inde_detalle=$_POST['m_inde_detalle'];
$m_inde_cantidad=$_POST['m_inde_cantidad'];
$m_inti_id=$_POST['m_inti_id'];
$m_ingr_precio=$_POST['m_ingr_precio'];
$m_inde_temp_code=$_POST['m_inde_temp_code'];
$qsql = "update ingresos_detalle set 
prod_id='$m_prod_id', 
inde_cantidad='$m_inde_cantidad', 
inti_id='$m_inti_id', 
inde_detalle='$m_inde_detalle',
ingr_precio='$m_ingr_precio'
where inde_id='$id'";
mysql_query($qsql);
?>




