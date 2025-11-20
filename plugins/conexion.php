<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE); 

$username="dunderio_gsarhu";
$password="Siuma_2229";
$database="dunderio_siuma_arhu";
$dbh=mysql_connect("localhost",$username,$password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($database) or die( "Unable to select database");
?>