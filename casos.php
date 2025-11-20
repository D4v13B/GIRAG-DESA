<?php
//if(isset($_SERVER["HTTP_REFERER"])){
//
//   $referer = $_SERVER["HTTP_REFERER"];
//   $parsedUrl = parse_url($referer);
//
//   //Obtener el dominio
//   $domain = $parsedUrl["host"];
//   if($domain != "https://giraglogicdesa.girag.aero/"){
//      header("Location:https://giraglogic.girag.aero/casos.php");
//   }
//   echo $referer;
//}

include('conexion.php');
include('funciones.php');
// include('seguridad.php');

// $id_depa = $_GET["depa"];

$stmt = "SELECT * FROM casos_clasificacion";
$casos_clasificacion = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM casos_descripciones_tipicas";
$cadt = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM departamentos WHERE depa_id != 0 ORDER BY depa_nombre";
$depas = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM casos_frecuencia";
$frecuencia = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM casos_tipos";
$tipos = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM incidencia_seg_op";
$inc_seg_op = mysql_query($stmt, $dbh);

$stmt = "SELECT* FROM incidencia_procesos";
$inc_procesos = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM impacto_economico";
$imp_Económico = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM impacto_personas";
$imp_personas = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM impacto_medio_ambiente";
$imp_ambiente = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM equipos WHERE equi_id != 0 ORDER BY equi_nombre";
$equipos = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM cargos_usuarios";
$cargos = mysql_query($stmt);

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Casos</title>
  <script src="plugins/jQuery/jquery-3.4.1.min.js"></script>
  <script src="plugins/jQueryUI/jquery-ui.min.js"></script>
  <link href="plugins/jQueryUI/jquery-ui.min.css" rel="stylesheet" type="text/css" />
  <?php include 'index_links.php'; ?>
</head>

<style>
  .loader {
    width: 15px;
    aspect-ratio: 1;
    background: #fff;
    border-radius: 50%;
    animation: l6 1s infinite linear alternate;
    position: fixed;
    left: 50%;
    bottom: 0;
    right: 0;
    top: 50%;
  }

  .loader-background {
    display: none;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    position: fixed;
    background-color: rgba(75, 75, 75, .5);
    z-index: 1000;
  }

  @keyframes l6 {
    0% {
      box-shadow: 15px 0, -25px 0
    }

    50% {
      box-shadow: 15px 0, -15px 0
    }

    100% {
      box-shadow: 25px 0, -15px 0
    }
  }
</style>

<!-- CUERPO PARA PASAR AL PROYECTO -->

<div class="loader-background">
  <div class="loader"></div>
</div>

<body class="container mt-3">


  <div id="info">

  </div>

  <div class="col-12 col-md-6 container mb-4">

    <img src="https://giraglogic.girag.aero/img/Girag.png" alt="Girag logo" class="mb-3" width="200px">
    <h4>Reporte de incidentes y accidentes (FT-SMS-01)</h4>
    <hr>
    <form id="formulario" enctype="multipart/form-data">
      <!-- Abierto por -->
      <div class="form-group">
        <label for="abierto_por">Abierto por</label>
        <input type="text" class="form-control" id="abierto_por" placeholder="Tu nombre...(opcional)" name="abierto_por">
      </div>
      <!--Correo -->
      <div class="form-group">
        <label for="correo">Correo</label>
        <input type="email" class="form-control" id="correo" placeholder="Correo...(Opcional)" name="correo">
      </div>
      <!-- Descripcion -->
      <div class="form-group">

        <label for="descripcion" class="form-label">Descripción</label>
        <input class="form-control" list="descripciones_tipicas" id="descripcion" placeholder="Presiona para buscar o escribir..." name="descripcion">
        <datalist id="descripciones_tipicas">
          <?php while ($fila = mysql_fetch_assoc($cadt)) : ?>
            <option value="<?php echo $fila["cadt_nombre"] ?>">
            <?php endwhile ?>
      </div>
      </datalist>
      <!-- Departamento -->
      <div class="form-group">
        <label for="departamento">Departamento de quien reporta</label>
        <select class="form-control" id="departamento" name="departamento">
          <option selected>Escoger Departamento</option>

          <?php while ($fila = mysql_fetch_assoc($depas)) : ?>
            <option value="<?php echo $fila["depa_id"] ?>"><?php echo $fila["depa_nombre"] ?></option>
          <?php endwhile ?>
        </select>
      </div>
      <!-- Cargo del usuarios -->
      <div class="form-group">
        <label for="cargo_reporta">Cargo que ocupas</label>
        <input type="text" class="form-control" id="abierto_por" placeholder="Tu cargo de puesto" name="cargo_reporta">
      </div>
      <!-- Tipo de caso -->
      <div class="form-group">
        <label for="tipo">Tipo de caso</label>
        <select class="form-control" id="tipo" name="tipo">
          <option selected disabled>Escoger tipo</option>
          <?php while ($fila = mysql_fetch_assoc($tipos)) : ?>
            <option value="<?php echo $fila["cati_id"] ?>"><?php echo $fila["cati_nombre"] ?></option>
          <?php endwhile ?>
        </select>
      </div>
      <!-- Ubicacion -->
      <div class="form-group">
        <label for="ubicacion">Ubicación</label>
        <input type="text" class="form-control" id="ubicacion" placeholder="Ubicacion donde sucedio?..." name="ubicacion">
      </div>
      <!-- Equipos -->
      <div class="form-group">
        <label for="equipos">Equipos</label>
        <select class="form-control" id="equipos" name="equipos">
          <option selected disabled>Escoger equipos</option>
          <option value="0">N/A</option>
          <?php while ($fila = mysql_fetch_assoc($equipos)) : ?>
            <option value="<?php echo $fila["equi_id"] ?>"><?php echo $fila["equi_nombre"] ?></option>
          <?php endwhile ?>
        </select>
      </div>
      <!-- Fecha de incidencia -->
      <div class="form-group">
        <label for="fecha_incidencia">Fecha de incidencia</label>
        <input type="datetime-local" class="form-control" id="fecha_incidencia" name="fecha_incidencia">
      </div>
      <!-- Frecuencia -->
      <!-- <div class="form-group">
        <label for="frecuencia">Frecuencia</label>
        <select class="form-control" id="frecuencia" name="frecuencia">
          <option selected>Escoger frecuencia</option>
          <?php //while ($fila = mysql_fetch_assoc($frecuencia)) : 
          ?>
            <option value="<?php //echo $fila["cafr_id"] 
                            ?>"><?php //echo $fila["cafr_nombre"] 
                                ?></option>
          <?php //endwhile 
          ?>
        </select>
      </div> -->
      <!-- Seguridad operacional -->
      <!-- <div class="form-group">
        <label for="seg_op">Incidencia en la Seguridad Operacional</label>
        <select class="form-control" id="seg_op" name="seg_op">
          <option selected>Escoger Incidencia en la Seguridad Operacional</option>
          <?php //while ($fila = mysql_fetch_assoc($inc_seg_op)) : 
          ?>
            <option value="<?php //echo $fila["inso_id"] 
                            ?>"><?php //echo $fila["inso_nombre"] 
                                ?></option>
          <?php //endwhile 
          ?>
        </select>
      </div> -->
      <!-- Inicidencia de procesos -->
      <!-- <div class="form-group">
        <label for="procesos">Incidencia en los procesos</label>
        <select class="form-control" id="procesos" name="procesos">
          <option selected>Escoger Incidencia de procesos</option>
          <?php //while ($fila = mysql_fetch_assoc($inc_procesos)) : 
          ?>
            <option value="<?php //echo $fila["inpr_id"] 
                            ?>"><?php //echo $fila["inpr_nombre"] 
                                ?></option>
          <?php //endwhile 
          ?>
        </select>
      </div> -->
      <!-- Impacto Económico -->
      <!-- <div class="form-group">
        <label for="imp_eco">Impacto Económico</label>
        <select class="form-control" id="imp_eco" name="imp_eco">
          <option selected>Escoger Impacto Económico</option>
          <?php //while ($fila = mysql_fetch_assoc($imp_Económico)) : 
          ?>
            <option value="<?php //echo $fila["imec_id"] 
                            ?>"><?php //echo $fila["imec_nombre"] 
                                ?></option>
          <?php //endwhile 
          ?>
        </select>
      </div> -->
      <!-- Hora de incidencia -->
      <!-- <div class="form-group">
        <label for="fecha_incidencia">Hora de incidencia</label>
        <input type="time" class="form-control" id="hora_incidencia" name="hora_incidencia" pattern="[0-9]{2}:[0-9]{2}">
      </div> -->

      <!-- Impacto en las personas -->
      <div class="form-group">
        <label for="imp_per">Impacto en las personas</label>
        <select class="form-control" id="imp_per" name="imp_per">
          <option selected>Escoger Impacto en las personas</option>
          <?php while ($fila = mysql_fetch_assoc($imp_personas)) : ?>
            <option value="<?php echo $fila["impe_id"] ?>"><?php echo $fila["impe_nombre"] ?></option>
          <?php endwhile ?>
        </select>
      </div>
      <!-- Impacto al medio ambiente -->
      <!-- <div class="form-group">
        <label for="imp_med_amb">Impacto al medio ambiente</label>
        <select class="form-control" id="imp_med_amb" name="imp_med_amb">
          <option selected>Escoger Impacto en el medio ambiente</option>
          <?php //while ($fila = mysql_fetch_assoc($imp_ambiente)) : 
          ?>
            <option value="<?php //echo $fila["imma_id"] 
                            ?>"><?php //echo $fila["imma_nombre"] 
                                ?></option>
          <?php //endwhile 
          ?>
        </select>
      </div> -->


      <!-- Nota -->
      <div class="form-group">
        <label for="nota">Nota</label>
        <textarea class="form-control" id="nota" rows="3" placeholder="Que / Como / Cuando sucedio?" name="nota"></textarea>
      </div>
      <!-- Seccion de clasificacion -->
      <label for="nota">Clasificacion del caso</label>
      <div class="input-group flex-column mb-3">
        <?php while ($fila = mysql_fetch_assoc($casos_clasificacion)) : ?>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="cacl_id" id="<?php echo $fila["cacl_id"] ?>" value="<?php echo $fila["cacl_id"] ?>" onclick="mostrarSubclasificacion(<?php echo $fila['cacl_id'] ?>)">
            <label class="form-check-label" for="<?php echo $fila["cacl_id"] ?>">
              <?php echo $fila["cacl_nombre"] ?>
            </label>
            <!-- Seccion de subclasificacion -->
            <div class="sucl" id="sucl_se<?php echo $fila['cacl_id'] ?>" style="display:none; margin:left;">
              <label for="nota">Subclasificacion</label>
              <div class="input-group flex-column mb-3">

              </div>
            </div>
          </div>
        <?php endwhile ?>
        <div id="direccion-error"></div>
      </div>

      <!-- Archivos -->
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="archivos">Subir evidencias</span>
        </div>
        <div class="custom-file">
          <input type="file" class="custom-file-input" id="archivos" aria-describedby="archivos" name="archivos[]" multiple>
          <label class="custom-file-label" for="archivos">Buscar</label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Registrar caso</button>
    </form>

  </div>

  <script>
    


    const inputs = document.querySelectorAll("input")

    inputs.forEach(inp => {
      inp.autocomplete = "off"
    })

    registrarCaso = () => {

      let datos = new FormData($("#formulario")[0])

      $.ajax({
        method: "POST",
        url: "ajax/registrarCaso.php",
        data: datos,
        contentType: false,
        processData: false,
        beforeSend: () => {
          $(".loader-background").css("display", "block");
        },
        success: (msg) => {
          alert("Caso registrado de manera exitosa");
          console.log(msg);
          $("#formulario").trigger("reset")
        },
        error: (xhr, textStatus, errorThrown) => {
          alert("Llenar todos los campos correctamente");
        },
        complete: () => {
          $(".loader-background").css("display", "none");

        }
      })

    }

    function mostrarSubclasificacion(cacl_id) {
      // console.log("Clasificación seleccionada:", cacl_id);
      $(".sucl").hide();

      $.ajax({
        url: "ajax/clasificacion.php",
        method: "GET",
        data: {
          cacl_id: cacl_id,
        },
        success: res => {
          // console.log("Respuesta del servidor:", res);
          let datos = JSON.parse(res);
          // console.log("Datos parseados:", datos);

          let sucl_se = $("#sucl_se" + cacl_id);
          sucl_se.empty();

          // Iterar sobre los datos de las subclasificaciones y crear elementos HTML
          datos.forEach(subclasificacion => {
            let div = $("<div>").addClass("form-check");
            let input = $("<input>").addClass("form-check-input").attr({
              type: "radio",
              name: "cacd_id",
              id: "cacd_id" + subclasificacion.casu_id,
              value: subclasificacion.casu_id
            });
            let label = $("<label>").addClass("form-check-label").attr({
              for: "cacd_id" + subclasificacion.casu_id
            }).text(subclasificacion.casu_descripcion);
            div.append(input, label);
            sucl_se.append(div);
          });

          // Mostrar el contenedor de subclasificación actual y ocultar los demás

          sucl_se.show();
        },
        complete: () => {

        },
        error: function(xhr, status, error) {
          console.error("Error al obtener las subclasificaciones:", status, error);
        }
      });
    }

    $("document").ready(function() {

      $("#formulario").validate({

        rules: {
          descripcion: "required",
          departamento: "required",
          tipo: "required",
          ubicacion: "required",
          equipos: "required",
          fecha_incidencia: "required",
          nota: "required",
          cacl_id: "required"
        },
        messages: {
          descripcion: "<span style='color: red; font-size: smaller;'>Ingrese la descripcion </span>",
          departamento: "<span style='color: red; font-size: smaller;'>Seleccione el departamento </span>",
          tipo: "<span style='color: red; font-size: smaller;'> Seleccione el tipo de caso </span>",
          ubicacion: "<span style='color: red; font-size: smaller;'>Ingrese la ubicación</span>",
          equipos: "<span style='color: red; font-size: smaller;'>Seleccione el equipo</span>",
          fecha_incidencia: "<span style='color: red; font-size: smaller;'>Seleccione la fecha</span>",
          nota: "<span style='color: red; font-size: smaller;'>Ingrese la nota</span>",
          cacl_id: "<span style='color: red; font-size: smaller;'>Seleccione la clasificación</span>"

        },
        errorPlacement: function(error, element) {
          if (element.attr("name") == "cacl_id") {
            error.appendTo("#direccion-error");
          } else {
            error.insertAfter(element);
          }
        },
        submitHandler: function(form) {

          // Envía el formulario después de mostrar el mensaje
          registrarCaso();
          // Aquí se ejecuta si el formulario es válido


        }

      });

    });
  </script>

</body>

<!-- FIN DEL CUERPO DEL PROYECTO -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.world.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- FastClick -->
<script src="plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>

<!-- Toastr -->
<link href="jquery/toastr.css" rel="stylesheet" />
<script src="jquery/toastr.js"></script>

</html>
