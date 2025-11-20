<?php



include_once "conexion.php";



$sql = "SELECT * FROM usuarios";

$usuario_firma = mysql_query($sql);


$sql = "SELECT * ,(SELECT usua_nombre FROM usuarios WHERE usuarios.usua_id = usuarios_firmas.usua_id) usua_nombre FROM usuarios_firmas ORDER BY";
$usuarios = mysql_query($sql);


?>





<div class="container">



   <section class="card d-flex justify-content-center col-12 col-md-6 m-auto">



      <div class="">


         <!-- Form registro  usuario firma -->



         <form class="card-body" id="usuario_firma" enctype="multipart/form-data">

            <h2> Registrar Firma</h2>

            <!-- ID Usuarios -->

            <div class="form-group">

               <label for="usua_id_gerente">Usuario</label>

               <select id="<?php echo $fila["usua_id"] ?>" class="form-control custom-select" name="usuario_firma" value="<?php echo $fila["usua_id"] ?>">



                  <option selected disabled>Seleccione un Usuario</option>



                  <?php while ($fila = mysql_fetch_assoc($usuario_firma)) : ?>



                     <option value="<?php echo $fila["usua_id"] ?>"><?php echo $fila["usua_nombre"] ?></option>



                  <?php endwhile ?>

               </select>



            </div>

            <!-- Archivo  -->



            <div class="form-group">



               <label for="usfi_ref">Escoga un archivo</label>



               <input class="form-control" type="file" id="usfi_ref" name="usfi_ref"/>







            </div>



            <div>







               <div class="text-center">



                  <button type="submit" class="btn btn-success btn-lg">Guardar</button>



               </div>







               <!-- <button type="reset" class="btn btn-success">ANULAR</button>-->







            </div>







         </form>



      </div>



   </section>



</div>



<script>
   function resetForm() {



      $('#usuario_firma').trigger('reset')



   }
   
   function registrarFirma() {



      const datos = new FormData($("#usuario_firma")[0])







      $.ajax({



         url: "ajax/registrar-firmas.php",



         method: "POST",



         contentType: false,



         processData: false,



         data: datos,



         success: res => {



            // Aquí se ejecuta si el formulario es válido



            alert("Mensaje enviado exitosamente");







         },



         complete: function() {







            resetForm();







         }



      })



   }

   $("document").ready(function() {

      $("#usuario_firma").validate({
         rules: {

            usuario_firma: "required"
            // usfi_ref: "required",
         },



         messages: {
            usuario_firma: "<span style='color: red; font-size: smaller;'>Complete este campo</span>"
            // usfi_ref: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
         },



         submitHandler: function(form) {

            registrarFirma();

         }

      });

   });
</script>