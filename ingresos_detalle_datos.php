<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from ingresos_detalle
where inde_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'inde_id') . '||';
echo mysql_result($rs,$i,'prod_id') . '||';
echo mysql_result($rs,$i,'inde_cantidad') . '||';
echo mysql_result($rs,$i,'inti_id') . '||';
echo mysql_result($rs,$i,'ingr_precio') . '||';
echo mysql_result($rs,$i,'inde_temp_code') . '||';
echo mysql_result($rs,$i,'inde_detalle') . '||';
?>
