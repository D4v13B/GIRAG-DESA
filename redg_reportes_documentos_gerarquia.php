<?php
session_start();  

include_once "conexion.php";

$sql = "SELECT * FROM departamentos";

$idDepartamento = mysql_query($sql);
$usuaID = $_SESSION['login_user'];

// $sql = "SELECT usua_nombre FROM usuarios WHERE usua_id =$usuaID";
// $persona_calidad = mysql_fetch_assoc(mysql_query($sql));

// Estado del documento
$sql = "SELECT * FROM

reportes_documentos_estado p, reportes_documentos a

WHERE p.rede_id = a.rede_id";

$sql = "SELECT * FROM reportes_documentos_estado";

$reporte_id = mysql_query($sql);

// Nos trae todos los usuarios que son gerentes
$sql = "SELECT 
* 
FROM 
usuarios u
INNER JOIN 
usuarios_cargos uc 
ON 
u.usca_id = uc.usca_id
WHERE 
uc.ucsa_gerente = 1 ";

$usuario_gerente_revision = mysql_query($sql);

// Aprueban el documento
$sql = "SELECT 
* 
FROM 
usuarios u
INNER JOIN 
usuarios_cargos uc 
ON 
u.usca_id = uc.usca_id
WHERE 
uc.ucsa_gerente = 1 ";

$usuario_gerente_aprobacion = mysql_query($sql);

// Aprueban el documento
$sql = "SELECT 
* 
FROM 
usuarios u
INNER JOIN 
usuarios_cargos uc 
ON 
u.usca_id = uc.usca_id
WHERE 
uc.ucsa_gerente = 1 ";

$usuario_gerente_revisiones = mysql_query($sql);

// Consulta SQL para obtener los tipos de documentos
$sql = "SELECT * FROM reportes_documentos_tipos";
$result = mysql_query($sql);



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

<script>
// Agregar el campo multiselect al cargar la página
$(function() {
  const gerenteRevisionField = $("#gerenteField").first();
  
  // Crear el nuevo campo multiselect con estilo de tags (inicialmente oculto)
  if ($("#multiGerentesField").length === 0) {
    gerenteRevisionField.after(`
      <div class="form-group" id="multiGerentesField" style="display: none;">
        <label for="usua_id_gerentes_multiple">Gerentes Involucrados</label>
        <div class="dropdown">
          <button class="btn btn-default dropdown-toggle form-control text-left" type="button" 
                  id="dropdownGerentes" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" 
                  style="background-color: white; border: 1px solid #ced4da; text-align: left;">
            Seleccione los gerentes
            <span class="caret" style="float: right; margin-top: 8px;"></span>
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownGerentes" style="width: 100%; padding: 0; max-height: 300px; overflow-y: auto;">
            <li class="dropdown-header" style="background-color: #f8f9fa; padding: 8px 12px; border-bottom: 1px solid #dee2e6;">
              Seleccione sus gerentes
            </li>
            <?php 
            // Resetear el puntero del array de gerentes de revisión
            mysql_data_seek($usuario_gerente_revision, 0);
            while ($fila = mysql_fetch_assoc($usuario_gerente_revision)) : 
            ?>
              <li style="padding: 5px 12px;">
                <label style="width: 100%; margin-bottom: 0;">
                  <input type="checkbox" class="gerente-checkbox" name="usua_id_gerentes_multiple[]" 
                         value="<?php echo htmlspecialchars($fila["usua_id"]); ?>"
                         data-nombre="<?php echo htmlspecialchars($fila["usua_nombre"]); ?>"> 
                  <?php echo htmlspecialchars($fila["usua_nombre"]) ?>
                </label>
              </li>
            <?php endwhile ?>
          </ul>
        </div>
        
        <!-- Área de visualización de tags -->
        <div id="selectedGerentes" class="selected-tags-container" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px;">
          <!-- Aquí se agregarán los tags dinámicamente -->
        </div>
        
        <small class="form-text text-muted">Seleccione uno o varios gerentes de la lista</small>
      </div>
    `);

    // Aplicar estilos CSS para los tags
    $("<style>").text(`
      .selected-tag {
        display: inline-flex;
        align-items: center;
        background-color: #007bff;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        margin-right: 5px;
        margin-bottom: 5px;
        font-size: 14px;
      }
      .tag-remove {
        cursor: pointer;
        margin-left: 8px;
        font-weight: bold;
      }
      .selected-tags-container {
        min-height: 30px;
      }
    `).appendTo("head");

    // Evitar que el dropdown se cierre al hacer clic en los checkboxes
    $(document).on('click', '#multiGerentesField .dropdown-menu', function(e) {
      e.stopPropagation();
    });

    // Manejar la selección/deselección de gerentes
    $(document).on('change', '.gerente-checkbox', function() {
      const gerenteId = $(this).val();
      const gerenteNombre = $(this).data('nombre');
      
      if ($(this).is(':checked')) {
        // Agregar tag para el gerente seleccionado
        addGerenteTag(gerenteId, gerenteNombre);
      } else {
        // Eliminar tag si se desmarca
        removeGerenteTag(gerenteId);
      }
      
      updateDropdownText();
    });

    // Función para agregar un tag de gerente
    function addGerenteTag(id, nombre) {
      const tag = $(`
        <div class="selected-tag" data-id="${id}">
          ${nombre}
          <span class="tag-remove" data-id="${id}">&times;</span>
        </div>
      `);
      
      $('#selectedGerentes').append(tag);
      
      // Agregar evento para eliminar el tag
      $(tag).find('.tag-remove').on('click', function() {
        const gerenteId = $(this).data('id');
        // Desmarcar el checkbox correspondiente
        $(`.gerente-checkbox[value="${gerenteId}"]`).prop('checked', false);
        // Eliminar el tag
        removeGerenteTag(gerenteId);
        updateDropdownText();
      });
    }

    // Función para eliminar un tag de gerente
    function removeGerenteTag(id) {
      $(`.selected-tag[data-id="${id}"]`).remove();
    }

    // Función para actualizar el texto del botón dropdown
    function updateDropdownText() {
      const selectedCount = $('.gerente-checkbox:checked').length;
      const dropdownButton = $('#dropdownGerentes');
      
      if (selectedCount > 0) {
        dropdownButton.text(`${selectedCount} gerente(s) seleccionado(s)`);
      } else {
        dropdownButton.text('Seleccione los gerentes');
      }
    }
  }
  
  // Ejecutar toggleFields al cargar para establecer el estado inicial
  toggleFields();
});

function toggleFields() {
  // Usando jQuery para simplificar la selección de elementos
  const tipoDocumento = $("#documento_tipo").val();
  const gerenteRevision = $("#gerenteField").first();
  const multiGerentesField = $("#multiGerentesField");
  
  // Verificar si el valor del tipo de documento es igual a 6 (política)
  if (tipoDocumento === '6') {
    // Ocultar el campo de gerente de revisión
    gerenteRevision.hide();
    // Mostrar el campo de múltiples gerentes
    multiGerentesField.show();
  } else {
    // Mostrar el campo de gerente de revisión
    gerenteRevision.show();
    // Ocultar el campo de múltiples gerentes
    multiGerentesField.hide();
    // Limpiar la selección del multiselect y los tags
    $('.gerente-checkbox').prop('checked', false);
    $('#selectedGerentes').empty();
    $('#dropdownGerentes').text('Seleccione los gerentes');
  }
}


// Función para registrar el reporte, modificada para enviar múltiples gerentes cuando sea tipo 6
function registrarReporte() {
  const datos = new FormData($("#form-nuevo-reporte")[0]);
  datos.append("redg_id", $('#i_redg_padre').val());
  
  // Verificar si el tipo de documento es 6 (política)
  const tipoDocumento = $("#documento_tipo").val();
  
  if (tipoDocumento === '6') {
    // Obtener los IDs de los gerentes seleccionados
    const gerentesSeleccionados = [];
    $('.gerente-checkbox:checked').each(function() {
      gerentesSeleccionados.push($(this).val());
    });
    
    // Verificar si hay gerentes seleccionados y enviarlos
    if (gerentesSeleccionados.length > 0) {
      // Enviar los IDs de gerentes como un array para procesar en el backend
      // No sobreescribimos los datos sino que añadimos un nuevo campo
      datos.append("gerentes_multiples", JSON.stringify(gerentesSeleccionados));
    } else {
      alert("Debe seleccionar al menos un gerente para este tipo de documento.");
      return false;
    }
  }
  
  $.ajax({
    url: "ajax/reportes-documentos.php",
    method: "POST",
    contentType: false,
    processData: false,
    data: datos,
    success: function(res) {
      resetForm();
      alert("Documento enviado exitosamente");
      mostrar();
      $('#exampleModal').modal('hide');
      
    },
    error: function(xhr, status, error) {
      alert("Error al enviar el documento: " + error);
    }
  });
  return false; // Prevenir cualquier comportamiento por defecto
}
function resetForm() {
    $('#form-nuevo-reporte').trigger('reset');
    // Después de resetear el formulario, volvemos a evaluar los campos
    toggleFields();
  }
</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


<!-- <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=https://giraglogicdesa.girag.aero/manuales-uso/firmado-93_ContratodeGarantadeVentas.docx&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe> -->
<div id='separador'>

  <table width='' class=filtros>
    <tr>
    <tr>

      <?php echo entrada('input', 'Carpeta', 'f_redg_nombre', '150') ?>

      <input type='hidden' id='f_redg_nivel' value=1>

    </tr>
    <tr>
      <!-- Ajuste de nombre de los botones -->


      <td class='tabla_datos'>

        <div id='b_mostrar'><a href='javascript:mostrar()' class="btn btn-primary"><i class="fa-solid fa-eye"></i> Ver</a></div>
      </td>

      <td>
        <div id='dmodal' style='text-align:right'><a href='#' class="btn btn-primary"><i class="fa-solid fa-folder-plus"></i> Crear Carpeta</a></div>
      </td>

      <td>
        <div style='text-align:right'><button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal"><i class="fa-solid fa-file-circle-plus"></i> Subir archivo</button></div>
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
          <input type="hidden" name="usua_id_gerente_sms" value="<?php echo $usuaID; ?>">

          <div class="form-group">

            <label for="redo_titulo"> Titulo</label>

            <input type="text" id="redo_titulo" class="form-control" name="redo_titulo" placeholder="Ingrese el título">

          </div>

          <!-- Descripción -->

          <div class="form-group">

            <label for="redo_descripcion"> Descripción</label>

            <input type="text" id="redo_descripcion" class="form-control" name="redo_descripcion" placeholder="Ingrese la descripción">

          </div>

          <!-- Se utilizara para definir el tipo de documentos, y como es su proceso de aprobación, las politicas son las unicas con un proceso de aprobación distinto -->

          <!-- Tipo de Documento -->
          <label for="documento_tipo">Tipo de Documento</label>
          <select id="documento_tipo" class="form-control custom-select" name="documento_tipo" onchange="toggleFields()">
            <option selected disabled>Seleccione una opción</option>
            <?php while ($fila = mysql_fetch_assoc($result)) : ?>
              <option value="<?php echo htmlspecialchars($fila["redt_id"]); ?>">
                <?php echo htmlspecialchars($fila["redt_nombre"]); ?>
              </option>
            <?php endwhile; ?>
          </select>

          <!--Encargado de Revisión -->
          <div class="form-group" id="gerenteField">
            <label for="usua_id_gerente_depa">Gerente Encargado de Revisión</label>
            <select class="form-control custom-select" name="usua_id_gerente_revision">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($usuario_gerente_revisiones)) : ?>
                <option value="<?php echo htmlspecialchars($fila["usua_id"]); ?>"><?php echo htmlspecialchars($fila["usua_nombre"]) ?></option>              <?php endwhile ?>
            </select>
          </div>
           <!-- Encargado de Aceptación -->
           <div class="form-group" id="gerenteField">
            <label for="usua_id_gerente_depa">Gerente Encargado de Aprobación</label>
            <select class="form-control custom-select" name="usua_id_gerente_aprobacion">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($usuario_gerente_aprobacion)) : ?>
                <option value="<?php echo htmlspecialchars($fila["usua_id"]); ?>"><?php echo htmlspecialchars($fila["usua_nombre"]) ?></option>              <?php endwhile ?>
            </select>
          </div>

          <!-- Departamento -->
          <div class="form-group" id="departamentoField">
            <label for="depa_id">Departamento</label>
            <select id="<?php echo $fila["depa_id"] ?>" class="form-control custom-select" name="depa_id" value="<?php echo $fila["depa_id"] ?>">
              <option selected disabled>Seleccione una opción</option>
              <?php while ($fila = mysql_fetch_assoc($idDepartamento)) : ?>
                <option value="<?php echo $fila["depa_id"] ?>"><?php echo $fila["depa_nombre"] ?></option>
              <?php endwhile ?>
            </select>
          </div>
        
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
  $(function() {
    //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL
    $(".btn_borrar_documento").hide();
    <?php echo pantalla_roles("index.php?p=redg_reportes_documentos_gerarquia_mostrar", $_SESSION["login_user"]) ?>
    
    //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL
    $(".btn_inspeccionar_documento").hide();
    <?php echo pantalla_roles("index.php?p=redg_reportes_documentos_gerarquia", $_SESSION["login_user"]) ?>
    
  })
 
 
</script>