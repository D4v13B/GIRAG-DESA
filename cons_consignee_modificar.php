<?php include('conexion.php');
$id=$_GET['id'];
$m_cons_nombre=$_POST['m_cons_nombre'];
$m_pais_id=$_POST['m_pais_id'];
$m_cons_ciudad=$_POST['m_cons_ciudad'];
$m_cons_direccion=$_POST['m_cons_direccion'];
$qsql = "update consignee set 
cons_nombre='$m_cons_nombre', 
pais_id='$m_pais_id', 
cons_ciudad='$m_cons_ciudad', 
cons_direccion='$m_cons_direccion'
where cons_id='$id'";
mysql_query($qsql);
?>

