<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from carga
where carg_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'carg_id') . '||';
echo mysql_result($rs,$i,'cati_id') . '||';
echo mysql_result($rs,$i,'carg_guia') . '||';
echo mysql_result($rs,$i,'vuel_id') . '||';
echo mysql_result($rs,$i,'aeco_id_destino_final') . '||';
echo mysql_result($rs,$i,'usua_id_creador') . '||';
echo mysql_result($rs,$i,'carg_fecha_registro') . '||';
echo mysql_result($rs,$i,'carg_recepcion_real') . '||';
echo mysql_result($rs,$i,'liae_id') . '||';
echo mysql_result($rs,$i,'caes_id') . '||';
?>
