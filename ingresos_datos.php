<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from ingresos
where ingr_id='$id'";
$rs=mysql_query($qsql); 
$i=0;
echo mysql_result($rs,$i,'ingr_id') . '||';
echo mysql_result($rs,$i,'clie_id') . '||';
echo mysql_result($rs,$i,'ingr_fecha') . '||';
echo mysql_result($rs,$i,'ingr_numero_factura') . '||';
echo mysql_result($rs,$i,'infp_id') . '||';
echo mysql_result($rs,$i,'inen_id') . '||';
echo mysql_result($rs,$i,'inpa_id') . '||';
?>
