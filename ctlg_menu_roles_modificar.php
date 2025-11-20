<?php include('conexion.php');
$id=$_GET['id'];
$m_usti_id=$_GET['m_usti_id'];
$m_menu_id=$_GET['m_menu_id'];
$qsql = "update menu_roles set 
usti_id='$m_usti_id', 
menu_id='$m_menu_id'
where mero_id='$id'";
mysql_query($qsql);
?>