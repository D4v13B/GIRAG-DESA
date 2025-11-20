<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from incidencia_procesos
where inpr_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'inpr_id') . '||';
echo mysql_result($rs,$i,'inpr_incidencia') . '||';
?>
