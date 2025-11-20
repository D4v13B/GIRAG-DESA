<?php include('conexion.php');
$id=$_GET['id'];
$m_coin_codigo=$_POST['m_coin_codigo'];
$m_coin_descripcion=$_POST['m_coin_descripcion'];
$qsql = "update codigo_interlineal set 
coin_codigo='$m_coin_codigo', 
coin_descripcion='$m_coin_descripcion'
where coin_id='$id'";
mysql_query($qsql);
?>

