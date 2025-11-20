<?php  

include('conexion.php');



$codigo_vuelo = $_REQUEST["codigo_vuelo"]; 
$qsql = "SELECT vuel_id, vuel_codigo AS vuel_nombre
         FROM vuelos
         WHERE vuel_codigo LIKE '%$codigo_vuelo%'
         ORDER BY vuel_codigo";

$rs = mysql_query($qsql);


$json=array();



while($row = mysql_fetch_array($rs)) 

{

	$json[]=array(

	'id'=> $row['vuel_id'],

	'label'=> $row['vuel_nombre']

	);

}

echo json_encode($json);

?>