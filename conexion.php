<?php
include('mtomi.php');

$username="dunderio_usr_girag";
$password="Girag_2024!";
$database="dunderio_girag";
$dbh=mysql_connect("143.198.137.170",$username,$password) or die ('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ($database) or die( "Unable to select database");
?>