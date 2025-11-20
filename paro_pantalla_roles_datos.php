<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from pantalla_roles
where paro_id='$id'";
$rs=mysql_query($qsql); 
$i=0;
echo mysql_result($rs,$i,'paro_id') . '||';
echo mysql_result($rs,$i,'paro_pantalla') . '||';
echo mysql_result($rs,$i,'paro_nombre') . '||';
echo mysql_result($rs,$i,'paro_descripcion') . '||';
echo mysql_result($rs,$i,'paro_item_id') . '||';
echo mysql_result($rs,$i,'paro_item_tipo') . '||';
?>
