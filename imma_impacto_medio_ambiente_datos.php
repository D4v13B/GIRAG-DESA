<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from impacto_medio_ambiente
where imma_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'imma_id') . '||';
echo mysql_result($rs,$i,'imma_impacto') . '||';
?>
