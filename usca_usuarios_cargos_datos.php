<?php include('conexion.php');
$id=$_GET['id'];
$qsql ="select * from usuarios_cargos
where usca_id='$id'";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'usca_id') . '||';
echo mysql_result($rs,$i,'usca_nombre') . '||';
echo mysql_result($rs,$i,'ucsa_gerente') . '||';
?>
