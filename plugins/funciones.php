<?php



function obtener_rol($id)

{

$qsql ="select usti_id from usuarios a where usua_id=$id";

$result = mysql_query($qsql);

$i=0;



return mysql_result($result,$i, 'usti_id');



}



function nombre_completo($id)

{

	$qsql ="select usua_nombre usua_nombre_completo from usuarios where usua_id='$id'";

	$rs=mysql_query($qsql);

	$i=0;

	return mysql_result($rs, $i, 'usua_nombre_completo');

}



function actualizar_estados()

{

//cambio todos los apartamentos a disponibles

$qsql = "update activos set stat_id=1 where stat_id<>2";

mysql_query($qsql);



$qsql = "select acti_id from flujos where fluj_tipo in (2,3) and fluj_hasta>=now()";

$rs = mysql_query($qsql);

$num_rs = mysql_num_rows($rs);

$i=0;

while ($i<$num_rs)

	{

	$activo = mysql_result($rs, $i, 'acti_id');

	$qsql ="update activos set stat_id=3 where acti_id=$activo";

	mysql_query($qsql);

	$i++;

	}



}



function actualizar_meses()

{

//saco el numero de mes acutual

$qsql = "SELECT DATE_FORMAT(NOW(),'%m') mes,DATE_FORMAT(NOW(),'%Y') anio";

$mes = obtener_valor($qsql, 'mes');

$anio = obtener_valor($qsql, 'anio');

//busco la combinaci�n en la tabla meses

$qsql ="select count(*) cant from meses where mes='$mes' and anio='$anio'";

//si count es 0 entonces lo inserto

$bandera = obtener_valor($qsql, 'cant');

if($bandera==0)

	{

	$qsql = "insert into meses (mes, anio) values ('$mes', '$anio')";

	mysql_query($qsql);

	}

}



function armar_fecha_palabras($fecha)

{

$anio = substr($fecha,0,4);

$mes = substr($fecha, 4,2);

$dia = substr($fecha, 6,2);



if($mes=='01') {$mesl = 'Enero';} 

if($mes=='02') {$mesl = 'Febrero';} 

if($mes=='03') {$mesl = 'Marzo';} 

if($mes=='04') {$mesl = 'Abril';} 

if($mes=='05') {$mesl = 'Mayo';} 

if($mes=='06') {$mesl = 'Junio';} 

if($mes=='07') {$mesl = 'Julio';} 

if($mes=='08') {$mesl = 'Agosto';} 

if($mes=='09') {$mesl = 'Septiembre';} 

if($mes=='10') {$mesl = 'Octubre';} 

if($mes=='11') {$mesl = 'Noviembre';} 

if($mes=='12') {$mesl = 'Diciembre';} 



$armada = $dia . " de " . $mesl . " de " .$anio;



return $armada;



}



function obtener_valor($qsql, $campo)

{

$result = mysql_query($qsql);

$i=0;

$cant = mysql_num_rows($result);

//echo $qsql;

if($cant==0)

	{

	echo $qsql;

	}



$retorno = mysql_result($result,$i, $campo);



	return $retorno;



}



function obtener_parametro($id)

{

return obtener_valor("select para_valor from parametros where para_id=$id", "para_valor");

}



function obtener_parametro_nombre($id)

{

return obtener_valor("select para_valor from parametros where para_nombre='$id'", "para_valor");

}



function bitacora($id, $desde, $hasta)

{

$qsql ="select date_format(clbi_fecha, '%d/%m/%Y') fecha, clbi_detalle from clientes_bitacora where clie_id=$id";

$qsql = $qsql . " and date_format(clbi_fecha, '%Y%m%d')>=$desde";

$qsql = $qsql . " and date_format(clbi_fecha, '%Y%m%d')<=$hasta";

$qsql = $qsql . " order by clbi_fecha desc";

$result = mysql_query($qsql);

$num_proy = mysql_num_rows($result);

$i=0;

$bita="";

while ($i<$num_proy)

	{

	$bita = $bita . mysql_result($result, $i, 'fecha') . ' - ' . mysql_result($result, $i, 'clbi_detalle') . '<br>';

	$i++;

	}

if($bita=='')

{$bita='No hay bit&aacute;cora para este cliente';}

return $bita;

}



function armar_fecha($fecha)

{

$anio = substr($fecha,0,4);

$mes = substr($fecha, 4,2);

$dia = substr($fecha, 6,2);



$armada = $anio . "-" . $mes . "-" .$dia;



return $armada;



}



function cliente_propiedad($id)

{

$qsql = "select proy_nombre, concat(prpr_piso, prpr_letra) activo from proyectos a, proyectos_propiedades b";

$qsql = $qsql . " where a.proy_id=b.proy_id and b.clie_id=$id";



$rs = mysql_query($qsql);

$num_rs = mysql_num_rows($rs);

$i=0;

$activo="";

while ($i<$num_rs)

	{

	$activo = "<b>" . mysql_result($rs, $i, 'proy_nombre') . "</b>" . ": " . mysql_result($rs, $i, 'activo');

	$i++;

	}



return $activo;

}





function obtener_mes($mes)

{

if($mes=='01') {return 'Ene';}

if($mes=='02') {return 'Feb';}

if($mes=='03') {return 'Mar';}

if($mes=='04') {return 'Abr';}

if($mes=='05') {return 'May';}

if($mes=='06') {return 'Jun';}

if($mes=='07') {return 'Jul';}

if($mes=='08') {return 'Ago';}

if($mes=='09') {return 'Sep';}

if($mes=='10') {return 'Oct';}

if($mes=='11') {return 'Nov';}

if($mes=='12') {return 'Dic';}

}





function obtener_mes_completo_ingles($mes)

{

if($mes=='1') {return 'JANUARY';}

if($mes=='2') {return 'FEBRUARY';}

if($mes=='3') {return 'MARCH';}

if($mes=='4') {return 'APRIL';}

if($mes=='5') {return 'MAY';}

if($mes=='6') {return 'JUNE';}

if($mes=='7') {return 'JULY';}

if($mes=='8') {return 'AUGUST';}

if($mes=='9') {return 'SEPTEMBER';}

if($mes=='10') {return 'OCTUBER';}

if($mes=='11') {return 'NOVEMBER';}

if($mes=='12') {return 'DECEMBER';}

}



function latino_html($cadena)

{

$cadena = str_replace("�","&aacute;",$cadena);

$cadena = str_replace("�","&eacute;",$cadena);

$cadena = str_replace("�","&iacute;",$cadena);

$cadena = str_replace("�","&oacute;",$cadena);

$cadena = str_replace("�","&uacute;",$cadena);

$cadena = str_replace("�","&ntilde;",$cadena);



$cadena = str_replace("�","&Aacute;",$cadena);

$cadena = str_replace("�","&Eacute;",$cadena);

$cadena = str_replace("�","&Iacute;",$cadena);

$cadena = str_replace("�","&Oacute;",$cadena);

$cadena = str_replace("�","&Uacute;",$cadena);

$cadena = str_replace("�","&Ntilde;",$cadena);



return $cadena;



}



function quitar_las_tildes($cadena) {

$no_permitidas= array ("�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","Ù","� ","è","ì","ò","ù","�","�","â","�","î","ô","û","Â","Ê","Î","Ô","Û","�","ö","Ö","ï","ä","�","�","Ï","Ä","Ë");

$permitidas   = array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U" ,"a" ,"e" ,"i" ,"o" ,"u" ,"c","C","a" ,"e","i" ,"o" ,"u" ,"A" ,"E" ,"I" ,"O" ,"U" ,"u","o" ,"O" ,"i" ,"a" ,"e","U","I","A" ,"E");

$texto = str_replace($no_permitidas, $permitidas ,$cadena);

$texto = limpiar_caracteres_especiales($texto);

return $texto;

}



function limpiar_caracteres_especiales($s) {

	$s = preg_replace("/[����]/","a",$s);

	$s = preg_replace("/[����]/","A",$s);

	$s = preg_replace("/[���]/","I",$s);

	$s = preg_replace("/[���]/","i",$s);

	$s = preg_replace("/[���]/","e",$s);

	$s = preg_replace("/[���]/","E",$s);

	$s = preg_replace("/[�����]/","o",$s);

	$s = preg_replace("/[����]/","O",$s);

	$s = preg_replace("/[���]/","u",$s);

	$s = preg_replace("/[���]/","U",$s);

	$s = str_replace("�","c",$s);

	$s = str_replace("�","C",$s);

	$s = str_replace("�","n",$s);

	$s = str_replace("�","N",$s);

	$s = str_replace(" ","-",$s);

	//para ampliar los caracteres a reemplazar agregar lineas de este tipo:

	//$s = str_replace("caracter-que-queremos-cambiar","caracter-por-el-cual-lo-vamos-a-cambiar",$s);

	return $s;

}



function es_super($usuario)

{

	$qsql = "select usua_super from usuarios where usua_id=$usuario";

	$retorno = obtener_valor($qsql, 'usua_super');

	

	return $retorno;

	

}



function enviar_email($from, $from_name, $subject, $mensaje, $email)

{

$mail = new PHPMailer;

$mail->IsSMTP();



$mail->Host = "smtp.gmail.com";

$mail->SMTPDebug  = 1;  

$mail->SMTPAuth = true;

$mail->SMTPSecure = "tls";  

$mail->Port = 587;

define('GUSER', 'ichavez@gsrelocationpanama.com'); // GMail username

define('GPWD', 'ivania2289'); // GMail password

$mail->Username = GUSER;

$mail->Password = GPWD;

$mail->WordWrap = 50;

$mail->IsHTML(true);



$mail->From = $from;

$mail->FromName = $from_name;

$mail->Subject = $subject;

$mensaje= "<body style='font-family:Verdana, Arial, Helvetica'>" . $mensaje . '</body>';



//multiple correos

	$em = 0;

	if (strpos($email,';') !== false) 

		{

			$ccs = explode(';',$email);

			foreach($ccs as $key) 

			{

				if($em==0)

				{

				$mail->AddAddress($key);

				}

				else

				{

				//$mail->AddAddress($key);

				$mail->AddCC($key, $key);

				}

				$em++;

			}

		}

		else

		{

		$mail->AddAddress($email);

		}



$mail->Body = $mensaje;

$mail->Send();

}



function catalogo($tabla, $etiqueta, $order, $id, $tid, $tnombre,$todo,$multiple, $ancho, 

		$where='', $onclick='', $concatenado='', $nconcatenado='', $div='', $add_new='', $add_new_text='')

{

//primero leo la tabla

$qsql ="select $tid, $tnombre $concatenado from $tabla $where order by $order";

//echo $qsql;

if($concatenado!='') $tnombre=$nconcatenado;

$frs = mysql_query($qsql);

$fnum = mysql_num_rows($frs); 

$fi=0;

$mult='';

$resultado='';

if($multiple==1) 

{

	$mult = " multiple='multiple' "; 

	$lclase="class='etiquetas'";

	$iclase="class='entradas_multiples'";

	//$resultado="<script>$(function () { $('#$id').multiselect({selectedList: 1}); });</script>";

	$resultado="<script>$(function () { $('#$id').multipleSelect({filter: true}); });</script>";

	} 

else {

	$lclase="class='etiquetas'";

	$iclase="class='entrada'";

	}

if($ancho!="") $ancho = " style='width: $ancho" . "px !important'";





if($div=='')

{

	$resultado .= "<td $lclase>$etiqueta:</td>

	<td><select id=$id name=$id $iclase $mult $ancho $onclick>";

}

else

{

	$resultado .= "<div $lclase>$etiqueta:</div>

	<div><select id=$id name=$id $iclase $mult $ancho $onclick>";

}



if($todo==1) $resultado .= "<option value=''>TODOS</option>";

if($todo==2) $resultado .= "<option value=''></option>";

if($todo==3) $resultado .= "<option value='0'>N/A</option>";



while($fi<$fnum)

{

$option_valor = mysql_result($frs, $fi, $tid);

$option_nombre = mysql_result($frs, $fi, $tnombre);

$resultado .= "<option value='$option_valor'>$option_nombre</option>";

$fi++;

}



if($add_new==1)

{

	$resultado .= "<option value='-1' style='background: rgba(150, 150, 150, 0.3);'>$add_new_text</option>";

}



if($div=='')

{

	$resultado .= "</select></td>";

}

else

{

	$resultado .= "</select></div>";

}



return $resultado;

}



function entrada($tipo, $etiqueta, $id, $tamanio='', $min='', $max='', $onchange='', $div = "")

{

	//SI ES TIPO INPUT

	if($tipo=='input')

	{

	$resultado ="<td class='etiquetas'>$etiqueta:</td>";

	$resultado .= "<td class='entrada'><input type='text' id='$id' name='$id' style='width:" . $tamanio ."px !important' 

		placeholder='$etiqueta' class='entrada_input' autocomplete='off' $onchange value='$min'></td>	";

	}

	if($tipo=='fecha')

	{

		if($div != "" ){	
			$resultado = "
			<div class='form-group' style='width: $tamanio px !important'>
				<label for='$id'>$etiqueta: </label>
				<input type='date' class='form-control' id='$id' name='$id' autocomplete='off'>
		 	</div>";
			
		}else{
		 	$resultado ="<td class='etiquetas'>$etiqueta:</td>";
			
			$resultado .= "<td class='entrada'><input type='text' id='$id' name='$id' style='width:" . $tamanio ."px !important'  autocomplete='off'></td>	";
			
			$resultado .= "<script>$('#$id').datepicker({ dateFormat: 'yymmdd' });</script>";
		}

	}

	if($tipo=='number')

	{

	$resultado ="<td class='etiquetas'>$etiqueta:</td>";

	$resultado .= "<td class='entrada'><input type='number' id='$id' name='$id' style='width:" . $tamanio ."px !important' min='$min' max='$max' placeholder='$etiqueta'  autocomplete='off'></td>	";

	}

	return $resultado;

}



function entrada_div($tipo, $etiqueta, $id, $tamanio='', $min='', $max='')

{

	//SI ES TIPO INPUT

	if($tipo=='input')

	{

	$resultado ="<div>$etiqueta:</div>";

	$resultado .= "<div><input type='text' id='$id' name='$id' style='width:" . $tamanio ."px !important'></div>";

	}

	if($tipo=='fecha')

	{

	$resultado ="<div>$etiqueta:</div>";

	$resultado .= "<div><input type='text' id='$id' name='$id' style='width:" . $tamanio ."px !important'></div>";

	$resultado .= "<script>$('#$id').datepicker({ dateFormat: 'yymmdd' });</script>";

	}

	if($tipo=='number')

	{

	$resultado ="<div>$etiqueta:</div>";

	$resultado .= "<div><input type='number' id='$id' name='$id' style='width:" . $tamanio ."px !important' min='$min' max='$max' placeholder='$etiqueta'></div>";

	}

	return $resultado;

}



function insertar_comillas($arreglo)

{

	$resultado = explode(",", $arreglo);

	$arreglo='';

	foreach ($resultado as $valor) 

	{

	$arreglo .= "'$valor',";

	}

	$arreglo  = rtrim($arreglo, ",");

		

	return $arreglo;

}





function obtener_mes_completo($mes)

{

if($mes=='1') {return 'ENERO';}

if($mes=='2') {return 'FEBREO';}

if($mes=='3') {return 'MARZO';}

if($mes=='4') {return 'ABRIL';}

if($mes=='5') {return 'MAYO';}

if($mes=='6') {return 'JUNIO';}

if($mes=='7') {return 'JULIO';}

if($mes=='8') {return 'AGOSTO';}

if($mes=='9') {return 'SEPTIEMBRE';}

if($mes=='10') {return 'OCTUBRE';}

if($mes=='11') {return 'NOVIEMBRE';}

if($mes=='12') {return 'DICIEMBRE';}

}



function splitNewLine($text) {

    $code=preg_replace('/\n$/','',preg_replace('/^\n/','',preg_replace('/[\r\n]+/',"\n",$text)));

    return explode("\n",$code);

}



function cumpleaneros()

{

	$qsql ="SELECT COUNT(*) cant FROM clientes WHERE DATE_FORMAT(clie_fecha_nacimiento, '%m%d')=DATE_FORMAT(NOW(), '%m%d')";

	 return obtener_valor($qsql, "cant");

}



function renovaciones()

{

	$qsql ="SELECT COUNT(*) cant FROM polizas WHERE  DATE_FORMAT(poli_fecha_fin, '%Y%m%d')=DATE_FORMAT(NOW(), '%Y%m%d')";

	 return obtener_valor($qsql, "cant");

}



function jquery_catalogo($tabla, $etiqueta, $order, $id, $tid, $tnombre, $todo, $multiple, $ancho, $where='', $onclick='', $sololectura='', $selected='', $concatenado='', $desabilitado='')

{

//debo devolver el select vacio con el id que recibo

if($sololectura!="") $sololectura = " disabled ";

if($ancho!="") $ancho = ' style="width: ' . $ancho . 'px !important"';

$resultado="";

if($multiple==1)

{

	$mult = " multiple='multiple' ";

	$lclase="class='etiquetas'";

	$iclase="class='entradas_multiples'";

	//$resultado.="<script>$(function () { $('#$id').multipleSelect({filter: true}); });</script>";	

}	

elseif($multiple==2)

{

	$mult = " multiple='multiple' ";

	$lclase="class='etiquetas'";

	$iclase="class='entradas_multiples'";

	//$resultado.="<script>$(function () { $('#$id').multipleSelect({filter: true,single: true}); });</script>";

}

else

{

	$lclase="class='etiquetas'";

	$iclase="class='entradas'";

}

$resultado .= "<script>$(function () { llenar_combo('$tabla', '$etiqueta', '$order', '$id', '$tid', '$tnombre', '$todo', '$multiple', '$ancho', '$where', '$onclick', '$sololectura', '$selected', '$concatenado'); });</script>";

$resultado .= "<td $lclase>$etiqueta:</td>";

$resultado .= "<td><select id=$id name=$id $onclick $iclase $mult $ancho $sololectura></select></td>";



return $resultado;

}

?>