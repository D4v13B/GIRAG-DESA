<?php include('conexion.php');
$m_usua_id=$_POST['m_usua_id'];
$qsql = "update usuarios_firmas set 
usua_id='$m_usua_id'
where ='$id'";
mysql_query($qsql);
?>

