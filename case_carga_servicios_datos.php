<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from carga_servicios
where case_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'case_id') . '||';
echo mysql_result($rs,$i,'case_nombre') . '||';
echo mysql_result($rs,$i,'cadt_id') . '||';
echo mysql_result($rs,$i,'case_monto') . '||';
echo mysql_result($rs,$i,'case_itbms') . '||';
echo mysql_result($rs,$i,'case_reembolsable') . '||';
echo mysql_result($rs,$i,'case_peso_minimo') . '||';
echo mysql_result($rs,$i,'case_cuenta') . '||';
echo mysql_result($rs,$i,'case_ait') . '||';
echo mysql_result($rs,$i,'case_es_ait') . '||';
echo mysql_result($rs,$i,'case_monto_max') . '||';
echo mysql_result($rs,$i,'liae_id') . '||';
?>
