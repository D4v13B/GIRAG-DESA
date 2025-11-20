<?php 
include('conexion.php');
include('funciones.php');

// --- Configuración ---
$logFile = 'mysql_query_log.txt'; // Nombre del archivo de log
$logPath = __DIR__ . '/' . $logFile; // Ruta completa del archivo de log (en el mismo directorio del script)

// define a JSON Object class
class jsonOBJ {
    private $_arr;
    private $_arrName;

    function __construct($arrName){
        $this->_arrName = $arrName;
        $this->_arr[$this->_arrName] = array();

    }

    function toArray(){return $this->_arr;}
    function toString(){return json_encode($this->_arr);}

    function push($newObjectElement){
        $this->_arr[$this->_arrName][] = $newObjectElement; // array[$key]=$val;
    }

    function add($key,$val){
        $this->_arr[$this->_arrName][] = array($key=>$val);
    }
}

$guia=$_GET['guia'];

$jsonObj = new jsonOBJ("arreglo");


//ahora saco los datos del cliente y el paquete
$qsql = "SELECT caes_nombre, cade_peso, cade_notificada_fecha, cade_desc FROM carga_detalles a, carga_estado b
WHERE a.caes_id=b.caes_id
AND cade_guia='$guia'";
 
logMySQLQuery($qsql, $logPath);

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
if($num>0)
{
	$estado = mysql_result($rs, $i, 'caes_nombre');
	$fecha = mysql_result($rs, $i, 'cade_notificada_fecha');
	$peso = mysql_result($rs, $i, 'cade_peso');
	$descripcion = mysql_result($rs, $i, 'cade_desc');
	
	$jsonObj->add("estado",$estado); // from key:val pairs
	$jsonObj->add("fecha",$fecha); // from key:val pairs
	$jsonObj->add("peso",$peso); // from key:val pairs
	$jsonObj->add("descripcion",$descripcion); // from key:val pairs
}
else
{
$datos="DESCONOCIDO";

	$jsonObj->add("estado",$datos); // from key:val pairs
	$jsonObj->add("fecha",null); // from key:val pairs
	$jsonObj->add("peso",null); // from key:val pairs
	$jsonObj->add("descripcion",null); // from key:val pairs
}

logMySQLQuery($jsonObj->toString(), $logPath);

echo $jsonObj->toString();


// --- Función para registrar consultas ---
/**
 * Registra una consulta MySQL en un archivo de log.
 *
 * @param string $query La consulta SQL a registrar.
 * @param string $filepath La ruta completa del archivo de log.
 * @return void
 */
function logMySQLQuery($query, $filepath) 
{
    // Obtiene la fecha y hora actual en un formato legible
    $timestamp = date('Y-m-d H:i:s');

    // Formato de la entrada del log: [YYYY-MM-DD HH:MM:SS] Query: Tu_Consulta_SQL
    $logEntry = "[$timestamp] Query: " . $query . "\n";

    // Añade la entrada al archivo de log
    // FILE_APPEND: Asegura que el contenido se añade al final del archivo existente.
    // LOCK_EX: Bloquea el archivo durante la escritura para evitar problemas de concurrencia
    //          si varios procesos intentan escribir al mismo tiempo.
    file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX);
}

?>