<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from shipper
where ship_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'ship_id') . '||';
echo mysql_result($rs,$i,'ship_nombre') . '||';
echo mysql_result($rs,$i,'ship_ciudad') . '||';
echo mysql_result($rs,$i,'pais_id') . '||';
echo mysql_result($rs,$i,'ship_direccion') . '||';
?>
