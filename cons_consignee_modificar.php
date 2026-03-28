<?php include('conexion.php');
$id=$_GET['id'];
$m_cons_nombre=$_POST['m_cons_nombre'];
$m_cons_telefono=$_POST['m_cons_telefono'];
$m_cons_direccion=$_POST['m_cons_direccion'];
$qsql = "update consignee set 
cons_nombre='$m_cons_nombre', 
cons_telefono='$m_cons_telefono', 
cons_direccion='$m_cons_direccion'
where cons_id='$id'";
mysql_query($qsql);
?>

