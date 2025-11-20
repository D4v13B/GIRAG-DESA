<?php include('conexion.php'); 

$pid = $_GET['pid'];
$qsql ="select * from contratos 
where cont_id='$pid'";

$rs=mysql_query($qsql);
$i=0;
 
echo mysql_result($rs,$i,'cont_nombre') . "||";
echo mysql_result($rs,$i,'cont_detalle') . "||";
echo mysql_result($rs,$i,'coti_id') . "||";
echo "||";
echo mysql_result($rs,$i,'cocl_id') . "||";
echo mysql_result($rs,$i,'cont_version') . "||";
?>