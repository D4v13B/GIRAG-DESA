<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from impacto_personas
where impe_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'impe_id') . '||';
echo mysql_result($rs,$i,'impe_impacto') . '||';
?>
