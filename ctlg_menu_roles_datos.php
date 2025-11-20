<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from menu_roles
where mero_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'mero_id') . '||';
echo mysql_result($rs,$i,'usti_id') . '||';
echo mysql_result($rs,$i,'menu_id') . '||';
?>