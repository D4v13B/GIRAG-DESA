<?php include('conexion.php'); 
$i_usua_id=$_GET['i_usua_id'];
$i_paro_id=$_GET['i_paro_id'];
//echo "PARAMETROS:$i_paro_id";
//echo "USAURIO:$i_usua_id";
//saco las opciones escogidas

//echo "usuario ID = $i_usua_id";
if($i_paro_id!='null')
{
	$arreglo = explode(',',$i_paro_id);
    foreach($arreglo as $valor) {
        //borro e inserto
		$qsql ="delete from usuarios_roles where paro_id=$valor and usua_id=$i_usua_id";
		mysql_query($qsql);
		
		$qsql = "insert into usuarios_roles (usua_id, paro_id) values (
		'$i_usua_id', 
		'$valor')";
		mysql_query($qsql);
		
		//echo $qsql;
    }
}
?>