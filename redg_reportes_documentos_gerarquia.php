<?php

include_once "conexion.php";

$sql = "SELECT * FROM departamentos";

$idDepartamento = mysql_query($sql);



$sql = "SELECT * FROM

reportes_documentos_estado p, reportes_documentos a

WHERE p.rede_id = a.rede_id";

$sql = "SELECT * FROM reportes_documentos_estado";



$reporte_id = mysql_query($sql);



$sql = "SELECT * FROM
usuarios p, usuarios_tipos a
WHERE p.usti_id = a.usti_id AND a.usti_nombre LIKE '%Gerente%'";

$usuario_id = mysql_query($sql);

$sql = "SELECT * FROM
usuarios p, usuarios_tipos a
WHERE p.usti_id = a.usti_id AND a.usti_nombre LIKE '%Gerente%'";

$usuario_gerente = mysql_query($sql);

?>

<script>
  function crear() {

    $('#result').load('redg_reportes_documentos_gerarquia_crear.php'

      ,

      {

        'i_redg_nombre': $('#i_redg_nombre').val(),

        'i_redg_nivel': $('#i_redg_nivel').val(),

        'i_redg_padre': $('#i_redg_padre').val()

      }

      ,

      function() {

        $('#modal').hide('slow');

        $('#overlay').hide();

        mostrar();

      }

    );

  }

  function modificar() {

    $('#result').load('redg_reportes_documentos_gerarquia_modificar.php?id=' + $('#h2_id').val()

      ,

      {

        'm_redg_id': $('#m_redg_id').val(),

        'm_redg_nombre': $('#m_redg_nombre').val(),

        'm_redg_nivel': $('#m_redg_nivel').val(),

        'm_redg_padre': $('#m_redg_padre').val()

      }

      ,

      function() {

        $('#modal2').hide('slow');

        $('#overlay2').hide();

        mostrar();

      }

    );

  }

  function borrar_archivo(id)

  {

    var agree = confirm('¿Está seguro?');

    if (agree) {

      $('#result').load('redg_reportes_documentos_gerarquia_borrar.php?id_archivo=' + id

        ,

        function()

        {

          mostrar();

        }

      );

    }

  }

  function borrar(id)

  {

    var agree = confirm('¿Está seguro?');

    if (agree) {

      $('#result').load('redg_reportes_documentos_gerarquia_borrar.php?id=' + id

        ,

        function()

        {

          mostrar();

        }

      );

    }

  }

  function editar(id)

  {

    $('#modal2').show();

    $('#overlay2').show();

    $('#modal2').center();

    $('#h2_id').val(id);

    $.get('redg_reportes_documentos_gerarquia_datos.php?id=' + id, function(data) {

      var resp = data;

      r_array = resp.split('||');

      //alert(r_array[0]);

      $('#m_redg_nombre').val(r_array[1]);

      $('#m_redg_nivel').val(r_array[2]);

      $('#m_redg_padre').val(r_array[3]);

    });

  }

  function mostrar() {

    $('#datos_mostrar').load('redg_reportes_documentos_gerarquia_mostrar.php?nochk=jjjlae222'

      +
      "&f_redg_nombre=" + $('#f_redg_nombre').val()

      +
      "&f_redg_nivel=" + $('#f_redg_nivel').val()

      +
      "&f_redg_padre=" + $('#i_redg_padre').val()

    );
  }



  function abrir_carpeta(nivel, id)

  {

    n_nivel = nivel * 1;

    n_nivel = n_nivel + 1;



    $('#i_redg_nivel').val(n_nivel); //ajusta en que nivel estoy para la creación de las nuevas carpetas

    $('#f_redg_nivel').val(n_nivel)

    $('#i_redg_padre').val(id);



    $('#datos_mostrar').load('redg_reportes_documentos_gerarquia_mostrar.php?nochk=jjjlae222'

      +
      "&f_redg_nombre=" + $('#f_redg_nombre').val()

      +
      "&f_redg_nivel=" + $('#f_redg_nivel').val()

      +
      "&f_redg_padre=" + id

    );

  }
</script>
<div id='separador'>

  <table width='' class=filtros>

    <tr>
    <tr>

      <?php echo entrada('input', 'Carpeta', 'f_redg_nombre', '150') ?>

      <input type='hidden' id='f_redg_nivel' value=1>

    </tr>
    <tr>

      <td class='tabla_datos'>
        <div id='b_mostrar'><a href='javascript:mostrar()' class="btn btn-primary"><i class="fa-solid fa-eye"></i></a></div>
      </td>

      <td>
        <div id='dmodal' style='text-align:right'><a href='#' class="btn btn-primary"><i class="fa-solid fa-folder-plus"></i></a></div>
      </td>

      <td>
        <div style='text-align:right'><button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal"><i class="fa-solid fa-file-circle-plus"></i></button></div>
      </td>

    </tr>

  </table>

</div>

<div id='columna6'>

  <div id='datos_mostrar'></div>

</div>

<!--MODAL-->

<div id='overlay'></div>

<div id='modal'>
  <div id='content'>

    <input type='hidden' id=i_redg_nivel>

    <input type='hidden' id=i_redg_padre>



    <table>

      <tr>

        <td class='etiquetas'>Carpeta:</td>

        <td><input type='text' id=i_redg_nombre size=40 class='entradas'></td>

      </tr>

      <tr>

        <td colspan=2><a href='javascript:crear()' class='botones'>Crear</a></td>

      </tr>

    </table>

  </div>

  <a href='#' id='close'>close</a>

</div>



<div id='overlay2'></div>

<div id='modal2'>
  <div id='content2'>

    <input type=hidden id=h2_id>

    <input type='hidden' id=m_redg_nivel size=40 class='entradas'>

    <table>

      <tr>

        <td class='etiquetas'>Carpeta:</td>

        <td><input type='text' id=m_redg_nombre size=40 class='entradas'></td>

      </tr>

      <tr>

        <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>

      </tr>

    </table>

  </div>

  <a href='javascript:void(0);' id='close2'>close</a>

</div>



<div id=result></div>

<!-- MODAL DE NUEVO DOCUMENTO DENTRO DE UN NIVEL O CARPETA -->

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-file-circle-plus"></i>Nuevo documento</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">


        <!-- Form registro  reporte documento  -->

        <form id="form-nuevo-reporte" enctype="multipart/form-data">

          <!-- Titulo -->

          <div class="form-group">

            <label for="redo_titulo"> Titulo</label>

            <input type="text" id="redo_titulo" class="form-control" name="redo_titulo" placeholder="Ingrese el título">

          </div>

          <!-- Descripción -->

          <div class="form-group">

            <label for="redo_descripcion"> Descripción</label>

            <input type="text" id="redo_descripcion" class="form-control" name="redo_descripcion" placeholder="Ingrese la descripción">

          </div>

          <!-- ID gerente -->
          <div class="form-group">
            <label for="usua_id_gerente_sms">Gerente de Seguridad Ocupacional</label>
            <select class="form-control custom-select" name="usua_id_gerente_sms">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($usuario_id)) : ?>
                <option value="<?php echo $fila["usua_id"] ?>"><?php echo $fila["usua_nombre"] ?></option>
              <?php endwhile ?>
            </select>
          </div>

  <!-- ID gerente -->
  <div class="form-group">
            <label for="usua_id_gerente_depa">Gerente de Departamento</label>
            <select class="form-control custom-select" name="usua_id_gerente_depa">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($usuario_gerente)) : ?>
                <option value="<?php echo $fila["usua_id"] ?>"><?php echo $fila["usua_nombre"] ?></option>
              <?php endwhile ?>
            </select>
          </div>

          <!-- Id del detalle -->

          <!-- <div class="form-group">

            <label for="rede_id"> Estado</label>

            <select id="<?php echo $fila["rede_id"] ?>" class="form-control custom-select" name="rede_id" value="<?php echo $fila["rede_id"] ?>">

              <option selected disabled>Seleccione una opción</option>

              <?php while ($fila = mysql_fetch_assoc($reporte_id)) : ?>

                <option value="<?php echo $fila["rede_id"] ?>"><?php echo $fila["rede_nombre"] ?></option>

              <?php endwhile ?>



            </select>

          </div> -->
          <!--  -->


          <!-- Id del departamento -->

          <div class="form-group">

            <label for="depa_id"> Departamento</label>
            <select id="<?php echo $fila["depa_id"] ?>" class="form-control custom-select" name="depa_id" value="<?php echo $fila["depa_id"] ?>">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($idDepartamento)) : ?>
                <option value="<?php echo $fila["depa_id"] ?>"><?php echo $fila["depa_nombre"] ?></option>
              <?php endwhile ?>
            </select>
          </div>

 




          <!-- Comentario-->

          <!-- <div class="form-group">

            <label for="comentario"> Comentario</label>

            <input type="text" id="comentario" class="form-control" name="comentario">



          </div> -->



          <!-- Archivo  -->

          <div class="form-group">

            <label for="redo_ref">Escoga un archivo</label>

            <input class="form-control" type="file" id="redo_ref" name="documento" accept=".pdf,.doc,.docx" />



          </div>

          <div>



            <div class="text-center">

              <!-- <button type="submit" class="btn btn-success btn-lg">ENVIAR</button> -->

              <span onclick="registrarReporte()" class="btn btn-success">Enviar</span>

            </div>



            <!-- <button type="reset" class="btn btn-success">ANULAR</button>-->



          </div>



        </form>


      </div>
    </div>
  </div>
</div>

<script>
  function resetForm() {

    $('#form-nuevo-reporte').trigger('reset')

  }



  function registrarReporte() {

    const datos = new FormData($("#form-nuevo-reporte")[0])
    datos.append("redg_id", $('#i_redg_padre').val())


    $.ajax({

      url: "ajax/reportes-documentos.php",

      method: "POST",

      contentType: false,

      processData: false,

      data: datos,

      success: res => {

        // Aquí se ejecuta si el formulario es válido

        alert("Mensaje enviado exitosamente");
        $("#exampleModal").modal('hide');
        mostrar();



      },

      complete: function() {



        resetForm();



      }

    })

  }



  // $("document").ready(function() {



  //   $("#form-nuevo-reporte").validate({



  //     rules: {



  //       redo_titulo: "required",

  //       redo_descripcion: "required",

  //       usua_id_gerente: "required",

  //       rede_id: "required",

  //       depa_id: "required",

  //       comentario: "required",

  //       documento: {
  //         "required": false
  //       }





  //     },

  //     messages: {

  //       redo_titulo: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       redo_descripcion: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       usua_id_gerente: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       rede_id: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       depa_id: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       comentario: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",

  //       documento: "<span style='color: red; font-size: smaller;'>Complete este campo</span>"







  //     },

  //     submitHandler: function(form) {

  //       registrarReporte();

  //     }

  //   });



  // });
</script>