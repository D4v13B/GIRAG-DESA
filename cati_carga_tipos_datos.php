<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from carga_tipos
where cati_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'cati_id') . '||';
echo mysql_result($rs,$i,'cati_nombre') . '||';
?>
