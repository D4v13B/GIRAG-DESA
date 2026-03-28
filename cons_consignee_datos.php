<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from consignee
where cons_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'cons_id') . '||';
echo mysql_result($rs,$i,'cons_nombre') . '||';
echo mysql_result($rs,$i,'cons_telefono') . '||';
echo mysql_result($rs,$i,'cons_direccion') . '||';
?>
