<?php include('conexion.php'); 

if(isset($_GET["id_archivo"])){

   $id = $_GET["id_archivo"];

   $qsql = "DELETE FROM reportes_documentos WHERE redo_id = $id";
   mysql_query($qsql);
   
}else{
   
   $id = $_GET['id'];
   
   $qsql ="delete from reportes_documentos_gerarquia where redg_id=$id";
   
   mysql_query($qsql);
}

?>



