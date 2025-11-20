<?php 
include('conexion.php');
include('funciones.php');

$qsql = "SELECT a.usno_tabla, usno_tabla_campo, usno_tabla_estados, usno_tabla_columna_estado
				FROM usuarios_notificaciones a, usuarios_notificaciones_tablas_estados b
				WHERE a.usno_tabla=b.usno_tabla
				GROUP BY usno_tabla, usno_tabla_campo, usno_tabla_estados, usno_tabla_columna_estado";
$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;

$tr_notificaciones="<table>";
while($i<$num)
	{
	$tabla = mysql_result($rs, $i, 'usno_tabla');
	$columna_join = mysql_result($rs, $i, 'usno_tabla_campo');
	$usno_tabla_estados = mysql_result($rs, $i, 'usno_tabla_estados');
	$usno_tabla_estados_columna = mysql_result($rs, $i, 'usno_tabla_columna_estado');

	
	$qsql = "SELECT * FROM usuarios_notificaciones a, usuarios_notificaciones_tablas_estados b, $tabla c 
	WHERE a.usno_tabla=b.usno_tabla
	AND a.usno_tabla_id=c.$columna_join
	AND c.$usno_tabla_estados_columna IN ($usno_tabla_estados)";
	
	//echo $qsql;
	
	$rs_not = mysql_query($qsql);	
	$num_not = mysql_num_rows($rs_not);
	$j=0;
	while($j<$num_not)
		{
			$valor = mysql_result($rs_not, $j, 'usno_mensaje');
			$tr_notificaciones .= "<tr><td>$valor</td></tr>";
			$j++;	
		}
	$i++;
	}

echo $tr_notificaciones;
?>