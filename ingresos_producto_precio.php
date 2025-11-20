<?php include('conexion.php');

$id=$_GET['id'];
$clientId = (isset($_GET['clienid']) && $_GET['clienid']!="")? $_GET['clienid'] :""; 

$qsql ="select prod_precio, itbms from productos where prod_id='$id'";

$rs=mysql_query($qsql); 

$num=mysql_num_rows($rs);

$i=0;

	$total = mysql_result($rs,$i,'prod_precio');
	$itbms = mysql_result($rs,$i,'itbms');
	echo $total.'|'.$itbms;

?>

