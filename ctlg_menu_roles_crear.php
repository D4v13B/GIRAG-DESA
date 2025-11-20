<?php include('conexion.php');
$i_usti_id=$_GET['i_usti_id'];
$i_menu_id=$_GET['i_menu_id'];

//saco las opciones escogidas
if($i_menu_id!='null')
{
	$arreglo = explode(',',$i_menu_id);
    foreach($arreglo as $valor) {
        //borro e inserto
		$qsql ="delete from menu_roles where menu_id=$valor and usti_id=$i_usti_id";
		mysql_query($qsql);
		
		$qsql = "insert into menu_roles (usti_id, menu_id) values (
		'$i_usti_id', 
		'$valor')";
		mysql_query($qsql);
    }
}
?>