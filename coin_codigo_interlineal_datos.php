<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from codigo_interlineal
where coin_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'coin_id') . '||';
echo mysql_result($rs,$i,'coin_codigo') . '||';
echo mysql_result($rs,$i,'coin_descripcion') . '||';
?>
