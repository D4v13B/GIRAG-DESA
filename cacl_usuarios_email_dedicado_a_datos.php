<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from usuarios_email_dedicado_a
where usmd_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'usmd_id') . '||';
echo mysql_result($rs,$i,'usmd_nombre') . '||';
?>
