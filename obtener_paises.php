<?php  
include('conexion.php');

$codigo_pais = $_REQUEST["codigo_pais"]; 
$qsql="SELECT pais_id, pais_nombre 
FROM paises 
WHERE pais_nombre LIKE '%$codigo_pais%' 
ORDER BY pais_nombre";
$rs = mysql_query($qsql);
$json=array();

while($row = mysql_fetch_array($rs)) 
{
	$json[]=array(
	'id'=> $row['pais_id'],
	'label'=> $row['pais_nombre']
	);
}
echo json_encode($json);
?>