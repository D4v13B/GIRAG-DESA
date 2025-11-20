<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from casos_frecuencia
where cafr_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'cafr_id') . '||';
echo mysql_result($rs,$i,'cafr_nombre') . '||';
?>
