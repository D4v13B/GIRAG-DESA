<?php 
include('../conexion.php');
include('../funciones.php');
function getExtension($str) 
{
         $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
}
 
$id = $_GET["id"];
$usuario=$_SESSION['login_user'];

$filename = stripslashes($_FILES['file']['name']);


$filename = preg_replace('/[^A-Za-z0-9.\-]/', '', $filename);

$extension = getExtension($filename);

$insertar = "INSERT INTO reportes_documentos_bitacora(redb_fecha,redo_id,rede_id) values (now(),'$id', 1)";
mysql_query($insertar);

$maximo = mysql_insert_id();
$target_path = "../manuales-uso/$maximo" . "_";
$target_path = $target_path . preg_replace('/[^A-Za-z0-9.\-]/', '', $_FILES['file']['name']);

if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) 
{

} 
else
{
echo "Error";
}

$qsql = "UPDATE reportes_documentos_bitacora SET redb_ref='$maximo"."_$filename' WHERE redb_id =$maximo";
mysql_query($qsql);

?>