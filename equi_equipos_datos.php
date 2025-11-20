<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from equipos
where equi_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'equi_id') . '||';
echo mysql_result($rs,$i,'equi_nombre') . '||';
?>
