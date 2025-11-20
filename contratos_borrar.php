<?php include('conexion.php'); ?>
<?php
$id = $_GET['id']; 
$qsql ="delete from contratos where cont_id=$id";
//echo $qsql;
mysql_query($qsql);
?>