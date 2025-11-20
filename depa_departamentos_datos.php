<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from departamentos
where depa_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'depa_id') . '||';
echo mysql_result($rs,$i,'depa_nombre') . '||';
echo mysql_result($rs,$i,'depa_correo') . '||';
?>
