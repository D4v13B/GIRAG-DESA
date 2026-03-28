<?php

include '../conexion.php';

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
      /**
       * Aqui vamos a recibir el @var $_GET["id"] que va a ser el id del reporte en el cual vamos a hacer  el proceso de 
       * 1. Enviar la retroalimentacion del nuevo documento
       */

     

   case "POST":
      if (isset($_POST["redo_titulo"])) {
         /**
          * Recibiremos la informacion general para crear un nuevo documento e insertaremos el primer registro en las bitacoras para su revision
          * Titulo del manual o reporte
          * Descripcion
          * El gerente del proceo
          * El proceo o seccion que pertenece -- departamento
          */
         $titulo = $_POST["redo_titulo"];
         $descripcion = $_POST["redo_descripcion"];
         $usuaIdGerentedepa = $_POST["usua_id_gerente_depa"];
         $usuaIdGerentesms = $_POST["usua_id_gerente_sms"];
         //$redeId = $_POST["rede_id"] != 1 or isset($_POST["rede_id"]) ? $_POST["rede_id"] : 1;
         $depaId = $_POST["depa_id"];
         $comentario = !empty($_POST["comentario"]) ? $_POST["comentario"] : "No hay retroalimentacion";
         $redgId = $_POST["redg_id"];

         /**
          * Vamos a recibir el documento
          */

         if (!empty($_FILES["documento"]["name"])) { //Verificamos si existe el archivo del documento enviado y empezamos a tratarlo para guardarlo en el server

            $sql = "INSERT INTO reportes_documentos(
            redo_titulo, 
            redo_descripcion, 
            usua_id_gerente_departamento, 
           
            depa_id,
            rede_id,
            redg_id,
            usua_id_gerente_sms
            )
            
            VALUES(
               '$titulo',
               '$descripcion',
               '$usuaIdGerentedepa',
             
               '$depaId',
               1,
               '$redgId',
               '$usuaIdGerentesms'
            )";

            mysql_query($sql);

            if (mysql_error()) {
               http_response_code(400);
               echo "Error en el SQL";
               die();
            }

            /**
             * Agarramos el id del registro del manual para poder insertar el primer registro en la bitácora
             * @var $lastIdReporte
             * Vamos a procesar el documento para guardarlo en el server e insertar el registro de la bitacora
             */

            $lastIdReporte = mysql_insert_id();

            $manualReferencia = time() . "-" . $_FILES["documento"]["name"];

            if (move_uploaded_file($_FILES["documento"]["tmp_name"], "../manuales-uso/" . $manualReferencia)) {

               $sql = "INSERT INTO reportes_documentos_bitacora(
               
               redb_ref,
               redb_fecha,
               redo_id)
               
               VALUES(
                  
                  '$manualReferencia', 
                  now(), 
                  '$lastIdReporte'
                  )";

               mysql_query($sql);
               echo "Todas las operaciones han sido realizadas con exito";
            } else {
               http_response_code(400);
               echo "No podemos subir el archivo verif";
            }
         } else {
            http_response_code(400);
            echo "No existe un archivo al cual darle seguimiento";
         }
      } elseif (isset($_POST["confirmacion"])) {
         /**
          * Aqui vamos a rechazar o aceptar un cambio que se realizo a la bitacora del documento
          */
      }
      break;
}
