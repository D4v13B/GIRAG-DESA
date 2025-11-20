<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from casos_subclasificacion
where casu_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'casu_id') . '||';
echo mysql_result($rs,$i,'casu_descripcion') . '||';
echo mysql_result($rs,$i,'cacl_id') . '||';
?>
