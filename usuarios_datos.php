<?php include('conexion.php');

$id=$_GET['id'];

$qsql ="select * from usuarios

where usua_id='$id'";

$rs=mysql_query($qsql);

$i=0;

echo mysql_result($rs,$i,'usua_id') . '||';

echo mysql_result($rs,$i,'usua_nombre') . '||';

echo mysql_result($rs,$i,'usti_id') . '||';

echo mysql_result($rs,$i,'usua_password') . '||';

echo mysql_result($rs,$i,'usua_nombre_completo') . '||';

echo mysql_result($rs,$i,'usua_mail') . '||';

echo mysql_result($rs,$i,'usua_sms_aprueba') . '||';

echo mysql_result($rs,$i,'usua_administrador_caso') . '||';

echo mysql_result($rs,$i,'usca_id') . '||';
echo mysql_result($rs,$i,'usua_cedula') . '||';

?>

