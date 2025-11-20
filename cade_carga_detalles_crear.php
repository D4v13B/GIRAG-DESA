<?php include('conexion.php');
$i_cade_peso=$_POST['i_cade_peso'];
$i_cade_piezas=$_POST['i_cade_piezas'];
$i_cade_desc=$_POST['i_cade_desc'];
$i_cade_guia=$_POST['i_cade_guia'];
$i_cade_tipo_id=$_POST['i_cade_tipo_id'];
$qsql = "insert into carga_detalles 
(
cade_peso
, 
cade_piezas
, 
cade_desc
, 
cade_guia
, 
cade_tipo_id
) 
values (
'$i_cade_peso', 
'$i_cade_piezas', 
'$i_cade_desc', 
'$i_cade_guia', 
'$i_cade_tipo_id')";
mysql_query($qsql);
?>

