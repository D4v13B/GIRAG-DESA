<?php

include "../conexion.php";

//Documento que me sube los nuevos documentos de cada tarea
if($_SERVER["REQUEST_METHOD"] == "POST"){
   //Actualizar los documentos de una tarea en especifico
   print_r($_FILES);
   // print_r($_POST);

   $task_id = $_POST["tarea_id"];

   foreach($_FILES["new_docs"]["name"] as $key => $value){
      $new_ref = time() ."-" .$_FILES["new_docs"]["name"][$key];
      $nombre = $_FILES["new_docs"]["name"][$key];

      // echo $new_ref. PHP_EOL. $nombre;

      $stmt = "INSERT INTO tareas_documentos(tado_ref, cate_id, tado_nombre) VALUES('$new_ref', '$task_id', '$nombre')";
      $res = mysql_query($stmt);

      if(move_uploaded_file($_FILES["new_docs"]["tmp_name"][$key], "../img/casos_docs/".$new_ref)){
         echo "Documentos subidos";
      }

   }

}elseif($_SERVER["REQUEST_METHOD"] == "GET"){
   
      //Me trae los documentos de una tarea especifica
      $tarea_id = $_GET["tarea_id"];
      $response = [];
      
      $stmt = "SELECT * FROM tareas_documentos WHERE cate_id = $tarea_id";
      $res = mysql_query($stmt);
      
      while ($fila = mysql_fetch_assoc($res)) {
         array_push($response, $fila);
      }
      
      echo json_encode($response);


}elseif($_SERVER["REQUEST_METHOD"] == "DELETE"){
   // Eliminar un documento de tareo especifico
   $datos = json_decode(file_get_contents("php://input"), true);

   // Elimina el documento
   if(isset($datos["doc_id"])){
      $doc_id = $datos["doc_id"];
      
      $stmt = "DELETE FROM tareas_documentos WHERE tado_id = $doc_id";
      mysql_query($stmt);
      
      echo "Documento correctamente borrado";
   }
}


?>