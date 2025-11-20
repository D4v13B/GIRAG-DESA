<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from aereopuertos_codigos
where aeco_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'aeco_id') . '||';
echo mysql_result($rs,$i,'pais_id') . '||';
echo mysql_result($rs,$i,'aeco_codigo') . '||';
echo mysql_result($rs,$i,'aeco_nombre') . '||';
?>
