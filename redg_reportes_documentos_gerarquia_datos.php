<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from reportes_documentos_gerarquia
where redg_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'redg_id') . '||';
echo mysql_result($rs,$i,'redg_nombre') . '||';
echo mysql_result($rs,$i,'redg_nivel') . '||';
echo mysql_result($rs,$i,'redg_padre') . '||';
?>
