<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from casos_tareas
where cate_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'cate_id') . '||';
echo mysql_result($rs,$i,'cate_nombre') . '||';
echo mysql_result($rs,$i,'cate_descripcion') . '||';
echo mysql_result($rs,$i,'cate_fecha_cierre') . '||';
echo mysql_result($rs,$i,'cate_estado') . '||';
echo mysql_result($rs,$i,'depa_id') . '||';
echo mysql_result($rs,$i,'usua_id') . '||';
?>
