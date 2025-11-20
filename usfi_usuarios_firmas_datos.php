<?php include('conexion.php');
$qsql ="select * from usuarios_firmas
where ='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'usua_id') . '||';
?>
