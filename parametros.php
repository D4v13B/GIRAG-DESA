<?php 

switch($_SERVER["REQUEST_METHOD"]){
   case "POST":
      include "conexion.php";

      $password = $_POST["password_generica"];

      mysql_query("UPDATE parametros SET para_valor = '$password' WHERE para_nombre = 'password_generica'");

      if(!mysql_error()){
         echo json_encode(["success" => true]);
      }else{
         echo json_encode(["success" => false]);
      }

      exit;
}

$res = mysql_fetch_assoc(mysql_query("SELECT para_valor FROM parametros WHERE para_nombre = 'password_generica'"))


?>

<h2>Parámetros de sistema</h2>
<div class="container">
<form id="formulario_parametros">
  <div class="form-group">
    <label for="formGroupExampleInput">Password genérica</label>
    <input type="text" class="form-control" name="password_generica" value="<?php echo $res["para_valor"]?>">
  </div>
  <button class="btn btn-primary" id="btn-enviar">Modificar parámetros</button>
</form>
</div>

<script>

   $(document).ready(function(){
      $("#btn-enviar").on("click", actualizarParametros)
   })

   function actualizarParametros(e){
      e.preventDefault()
      let formData = new FormData($("#formulario_parametros")[0])

      $.ajax({
         url: "parametros.php",
         method: "POST",
         processData: false,
         contentType: false,
         data: formData,
         success: res => {
            res = JSON.parse(res)

            if(res.success){
               Swal.fire({
                  icon: "success",
                  title: "Parámetros actualizados"
               })

               // location.reload()
            }
         }
      })
   }
</script>