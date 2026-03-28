<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from usuarios_email_config
where usec_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'usec_id') . '||';
echo mysql_result($rs,$i,'usec_email') . '||';
echo mysql_result($rs,$i,'usec_password') . '||';
echo mysql_result($rs,$i,'usmd_id') . '||';
echo mysql_result($rs,$i,'usec_estado') . '||';
echo mysql_result($rs,$i,'usec_smtp') . '||';
?>
