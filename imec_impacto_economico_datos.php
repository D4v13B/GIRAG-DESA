<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from impacto_economico
where imec_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'imec_id') . '||';
echo mysql_result($rs,$i,'imec_nombre') . '||';
?>
