<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from dias_feriados
where dife_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'dife_id') . '||';
echo mysql_result($rs,$i,'dife_descripcion') . '||';
echo mysql_result($rs,$i,'dife_fecha') . '||';
?>
