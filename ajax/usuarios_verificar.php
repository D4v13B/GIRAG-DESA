<?php 
session_start();

include "../conexion.php";

switch($_SERVER["REQUEST_METHOD"]){
   case "POST":
      if(isset($_POST["usua_id"])){
         /*
         Aqui vamos a decir que no esta verificado y vamos a resetear la PASSWORD, crearemos un token para el usuario y asi poder verificar el usuario mediante token
         */

         //Vamos a buscar la password generica
         $password_generica = mysql_fetch_assoc(mysql_query("SELECT para_valor FROM parametros WHERE para_nombre = 'password_generica'"))["para_valor"];

         $usua_id = $_POST["usua_id"];
         $token = "girag-usua:$usua_id".time();
         $token = hash("sha224", $token);
         
         mysql_query("UPDATE usuarios SET usua_verificado = 0, usua_token = '$token', usua_password = '$password_generica' WHERE usua_id = $usua_id");

         if(!mysql_error()){
            echo json_encode(["success" => true]);
         }else{
            echo json_encode(["success" => false]);
         }
      }
      break;
}





?>