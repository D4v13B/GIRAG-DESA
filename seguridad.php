<?php
session_start();
$user_check=$_SESSION['login_user'];

if($user_check=='')
{
if($_SERVER["REQUEST_URI"]!='/index.php?p=login')
	{
	header("Location: login.php?empresa=xxxyyy" . date('Ymdst'));
	}
//

}
if($_SERVER["REQUEST_URI"]=='/index.php')
	{
	//header("Location: index.php?p=tareas");
	//header("Location: index.php?p=clientes");
	header("Location: index.php?home=0");
	}
$rol = obtener_rol($user_check);
//echo $_SESSION['login_user'] . "--";

?>