<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from incidencia_seg_op
where inso_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'inso_id') . '||';
echo mysql_result($rs,$i,'inso_incidencia') . '||';
?>
