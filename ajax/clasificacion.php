<?php
include('../conexion.php');
include('../funciones.php');

// print_r($_GET);

if($_SERVER["REQUEST_METHOD"] == "GET"){
   $response = [];
   $cacl_id = $_GET["cacl_id"];
   $stmt = "SELECT * FROM casos_subclasificacion WHERE cacl_id = $cacl_id";
   $res = mysql_query($stmt);

   while($fila = mysql_fetch_assoc($res)){
      array_push($response, $fila);
   }

   echo json_encode($response);
}