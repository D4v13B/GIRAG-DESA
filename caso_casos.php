<script>
  function crear() {
    $('#result').load('caso_casos_crear.php', {
        'i_caso_descripcion': $('#i_caso_descripcion').val(),
        'i_usua_id_abierto': $('#i_usua_id_abierto').val(),
        'i_caes_id': $('#i_caes_id').val(),
        'i_depa_id': $('#i_depa_id').val(),
        'i_cati_id': $('#i_cati_id').val(),
        'i_inso_id': $('#i_inso_id').val(),
        'i_inpr_id': $('#i_inpr_id').val(),
        'i_imec_id': $('#i_imec_id').val(),
        'i_impe_id': $('#i_impe_id').val(),
        'i_imma_id': $('#i_imma_id').val(),
        'i_equi_id': $('#i_equi_id').val(),
        'i_caso_fecha': $('#i_caso_fecha').val(),
        'i_caso_nota': $('#i_caso_nota').val(),
        'i_usua_id_aprobado': $('#i_usua_id_aprobado').val(),
        'i_usua_id_asignado': $('#i_usua_id_asignado').val(),
        'i_depa_id_asignado': $('#i_depa_id_asignado').val()
      },
      function() {
        $('#modal').hide('slow');
        $('#overlay').hide();
        mostrar();
      }
    );
  }

  function modificar() {
    $('#result').load('caso_casos_modificar.php?id=' + $('#h2_id').val(), {
        'm_caso_id': $('#m_caso_id').val(),
        'm_caso_descripcion': $('#m_caso_descripcion').val(),
        //  'm_usua_id_abierto':  $('#m_usua_id_abierto').val(),
        'm_caes_id': $('#m_caes_id').val(),
        'm_depa_id': $('#m_depa_id').val(),
        'm_cati_id': $('#m_cati_id').val(),
        'm_inso_id': $('#m_inso_id').val(),
        'm_inpr_id': $('#m_inpr_id').val(),
        'm_imec_id': $('#m_imec_id').val(),
        'm_impe_id': $('#m_impe_id').val(),
        'm_imma_id': $('#m_imma_id').val(),
        'm_equi_id': $('#m_equi_id').val(),
        'm_caso_fecha': $('#m_caso_fecha').val(),
        'm_caso_nota': $('#m_caso_nota').val(),
        'm_usua_id_asignado': $('#m_usua_id_asignado').val(),
        'm_depa_id_asignado': $('#m_depa_id_asignado').val(),
        "m_ubicacion": $("#m_ubicacion").val(),
        "m_cacl_id": $("#m_cacl_id").val(),
        "m_cacd_id": $("#m_cacd_id").val(),
        "m_referencia": $("#m_referencia").val(),
        "m_proc_id": $("#m_proc_id").val(),
        "m_depa_id_quien_reporta": $("#m_depa_id_quien_reporta").val(),
        "m_caso_externo": $("#m_caso_externo").val(),
        "m_usuario_revisado" : $("#m_usuario_revisado").val(),
        "m_usuario_revisado2" : $("#m_usuario_revisado2").val(),
        "m_usuario_revisado3" : $("#m_usuario_revisado3").val(),
        "m_usuario_aprobado" : $("#m_usuario_aprobado").val(),
        "m_usuario_aprobado2" : $("#m_usuario_aprobado2").val(),
        "m_usuario_aprobado3" : $("#m_usuario_aprobado3").val()

      },
      function() {
        $('#modal2').hide('slow');
        $('#overlay2').hide();
        mostrar();
      }
    );
  }

  function borrar(id) {
    var agree = confirm('¿Está seguro?');
    if (agree) {
      $('#result').load('caso_casos_borrar.php?id=' + id,
        function() {
          mostrar();
        }
      );
    }
  }

  function editar(id) {
    $('#modal2').show();
    $('#overlay2').show();
    $('#modal2').center();
    $('#h2_id').val(id);
    $.get('caso_casos_datos.php?id=' + id, function(data) {
      var resp = data;
      r_array = resp.split('||');
      console.log(r_array);
      //alert(r_array[0]);
      $('#m_caso_descripcion').val(r_array[1]);
      $('#m_usua_id_abierto').val(r_array[2]);
      $('#m_caso_estado').val(r_array[3]);
      $('#m_depa_id').val(r_array[4]);
      $("#m_ubicacion").val(r_array[16])
      $('#m_cati_id').val(r_array[5]);
      $('#m_inso_id').val(r_array[6]);
      $('#m_inpr_id').val(r_array[7]);
      $('#m_imec_id').val(r_array[8]);
      $('#m_impe_id').val(r_array[9]);
      $('#m_imma_id').val(r_array[10]);
      $('#m_equi_id').val(r_array[11]);
      $('#m_caso_fecha').val(r_array[12]);
      $('#m_caso_nota').val(r_array[13]);
      $('#m_usua_id_aprobado').val(r_array[14]);
      $('#m_usua_id_asignado').val(r_array[15]);
      $('#m_cacl_id').val(r_array[17]);
      $('#m_cacd_id').val(r_array[18]);
      $('#m_referencia').val(r_array[19]);
      $("#m_proc_id").val(r_array[20]);
      $("#m_depa_id_quien_reporta").val(r_array[21]);
      $("#m_caso_externo").val(r_array[22]);
      $("#m_usuario_revisado").val(r_array[23]);
      $("#m_usuario_revisado2").val(r_array[24]);
      $("#m_usuario_revisado3").val(r_array[25]);
      $("#m_usuario_aprobado").val(r_array[26]);
      $("#m_usuario_aprobado2").val(r_array[27]);
      $("#m_usuario_aprobado3").val(r_array[28]);
    });
  }

  function mostrar() {
    $('#datos_mostrar').load('caso_casos_mostrar.php?nochk=6878' +
      '&usua_id_revisado=' + $('#f_usua_id_revisado').val() +
      '&cati_id=' + $("#f_cati_id").val() +
      "&equi_id=" + $("#f_equi_id").val() +
      "&usua_id_aprobado=" + $("#f_usua_id_aprobado").val() +
      "&usua_id_asignado=" + $("#f_usua_id_asignado").val() +
      "&depa_id=" + $("#f_depa_id").val() +
      "&caes_id=" + $("#f_caes_id").val() +
      "&caso_id=" + $("#f_caso_id").val() +
      "&referencia=" + $("#f_referencia").val()
    );
  }

  function aprobarCaso() { //Cuando ya pasa la revision del caso
    const caso_id = $("#caso_id").val()
    const observaciones = $("#observaciones").val()
    $.ajax({
      type: "PUT",
      contentType: "application/json",
      url: "ajax/caso.php",
      data: JSON.stringify({
        caso_id: caso_id,
        observaciones: observaciones
      }),
      success: function(res) {
        // console.log("Éxito:", res);
        mostrar()
        alert(res)
      },
    });
  }
</script>
<div id='separador'>
  <div class="d-flex p-3 rounded-lg shadow-sm mb-3" style="background-color: #ffff;">
    <div class="d-flex col-10 justify-content-between m-auto flex-wrap">
      <span>
        <?php echo catalogo('usuarios', 'Revisado por', 'usua_nombre', 'f_usua_id_revisado', 'usua_id', 'usua_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('casos_tipos', 'Tipo', 'cati_nombre', 'f_cati_id', 'cati_id', 'cati_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('casos_estado', 'Estado', 'caes_nombre', 'f_caes_id', 'caes_id', 'caes_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('equipos', 'Equipos', 'equi_nombre', 'f_equi_id', 'equi_id', 'equi_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('usuarios', 'Aprobado por', 'usua_nombre', 'f_usua_id_aprobado', 'usua_id', 'usua_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('usuarios', 'Responsable de acciones', 'usua_nombre', 'f_usua_id_asignado', 'usua_id', 'usua_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo catalogo('departamentos', 'Departamento afectado', 'depa_nombre', 'f_depa_id', 'depa_id', 'depa_nombre', '0', '1', '180', "", "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo entrada('input', 'Reporte # ', 'f_caso_id', '150', "", "", "", "1"); ?>
      </span>
      <span>
        <?php echo entrada('input', ' Referencia ', 'f_referencia', '150', "", "", "", "1"); ?>
      </span>
    </div>
    <div class="d-flex my-3 justify-content-end">
      <div id='b_mostrar'><a href='javascript:mostrar()' class="mx-1 px-2" style="font-size: 22px;"><i class="fa-solid fa-eye" style="color: #0049c7;"></i></a></div>
      <!-- <div id='dmodal'><a href='#' class="mx-1 px-2" style="font-size: 22px;"><i class="fa-solid fa-square-plus" style="color: #39a300;"></i></a></div> -->
      <a href='casos.php' target="_blank" class="mx-1 px-2" style="font-size: 22px;"><i class="fa-solid fa-square-plus" style="color: #39a300;"></i></a>
    </div>
  </div>
</div>
<div id='columna6'>
  <div id='datos_mostrar'></div>
</div>
<!--MODAL-->
<div id='overlay'></div>
<div id='modal'>
  <div id='content'>
    <table>
      <tr>
        <td class='etiquetas'>Descripción:</td>
        <td><input type='text' id=i_caso_descripcion size=64 class='entradas'></td>
      </tr>
      <tr>
        <?php //echo catalogo('usuarios', 'Abierto por', 'usua_nombre', 'i_usua_id_abierto', 'usua_id', 'usua_nombre', '0', '0', '');
        ?>
      </tr>
      <tr>
        <!-- <td class='etiquetas'>Estado:</td> -->
        <!-- <td><input type='text' id=i_caso_estado size=40 class='entradas'></td> -->
        <?php echo catalogo('casos_estado', 'Estado', 'caes_nombre', 'i_caes_id', 'caes_id', 'caes_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('departamentos', 'Abierto por', 'depa_nombre', 'i_depa_id', 'depa_id', 'depa_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('casos_tipos', 'Tipo', 'cati_nombre', 'i_cati_id', 'cati_id', 'cati_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('incidencia_seg_op', 'Inc. Seguridad Operacional', 'inso_nombre', 'i_inso_id', 'inso_id', 'inso_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('incidencia_procesos', 'Inc. de procesos', 'inpr_nombre', 'i_inpr_id', 'inpr_id', 'inpr_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_economico', 'Impacto economico', 'imec_nombre', 'i_imec_id', 'imec_id', 'imec_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_personas', 'Impacto en personas', 'impe_nombre', 'i_impe_id', 'impe_id', 'impe_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_medio_ambiente', 'Impacto medio ambiente', 'imma_nombre', 'i_imma_id', 'imma_id', 'imma_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('equipos', 'Equipos', 'equi_nombre', 'i_equi_id', 'equi_id', 'equi_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <td class='etiquetas'>Fecha de apertura:</td>
        <td><input type='text' id=i_caso_fecha size=64 class='entradas'></td>
      </tr>
      <tr>
        <td class='etiquetas'>Detalles:</td>
        <td><input type='text' id=i_caso_nota size=64 class='entradas'></td>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Aprobado por', 'usua_nombre', 'i_usua_id_aprobado', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Responsable de acciones', 'usua_nombre', 'i_usua_id_asignado', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('departamentos', 'Departamento asignado', 'depa_nombre', 'i_depa_id_asignado', 'depa_id', 'depa_nombre', '0', '0', '500'); ?>
      </tr>

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
    <table>
      <tr>
        <td class='etiquetas'>Descripción:</td>
        <td><input type='text' id=m_caso_descripcion size=64 class='entradas'></td>
      </tr>

      <tr>
        <!-- <td class='etiquetas'>Estado:</td> -->
        <!-- <td><input type='text' id=m_caso_estado size=40 class='entradas'></td> -->
        <?php echo catalogo('casos_estado', 'Estado', 'caes_nombre', 'm_caes_id', 'caes_id', 'caes_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('casos_clasificacion', 'Clasificación', 'cacl_nombre', 'm_cacl_id', 'cacl_id', 'cacl_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('casos_subclasificacion', 'Subclasificion', 'casu_descripcion', 'm_cacd_id', 'casu_id', 'casu_descripcion', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('departamentos', 'Departamento quien reporta', 'depa_nombre', 'm_depa_id_quien_reporta', 'depa_id', 'depa_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('departamentos', 'Departamento afectado', 'depa_nombre', 'm_depa_id', 'depa_id', 'depa_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('casos_tipos', 'Tipo', 'cati_nombre', 'm_cati_id', 'cati_id', 'cati_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <td class='etiquetas'>Ubicacion:</td>
        <td><input type='text' id=m_ubicacion size=64 class='entradas'></td>
      </tr>
      <tr>
        <?php echo catalogo('incidencia_seg_op', 'Inc. Seguridad Operacional', 'inso_nombre', 'm_inso_id', 'inso_id', 'inso_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('incidencia_procesos', 'Inc. de procesos', 'inpr_nombre', 'm_inpr_id', 'inpr_id', 'inpr_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_economico', 'Impacto economico', 'imec_nombre', 'm_imec_id', 'imec_id', 'imec_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_personas', 'Impacto en personas', 'impe_nombre', 'm_impe_id', 'impe_id', 'impe_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('impacto_medio_ambiente', 'Impacto medio ambiente', 'imma_nombre', 'm_imma_id', 'imma_id', 'imma_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('equipos', 'Equipos', 'equi_nombre', 'm_equi_id', 'equi_id', 'equi_nombre', '0', '0', '500'); ?>
      </tr>

      <tr>
        <td class='etiquetas'>Fecha de apertura:</td>
        <td><input type='text' id=m_caso_fecha size=64 class='entradas'></td>
      </tr>
      <tr>
        <td class='etiquetas'>Detalles:</td>
        <td><input type='text' id=m_caso_nota size=64 class='entradas'></td>
      </tr>
      <tr>
        <td class='etiquetas'>Referencia:</td>
        <td><input type='text' id=m_referencia size=64 class='entradas'></td>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario asignado', 'usua_nombre', 'm_usua_id_asignado', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('departamentos', 'Departamento asignado', 'depa_nombre', 'm_depa_id_asignado', 'depa_id', 'depa_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('procesos', 'Proceso', 'proc_nombre', 'm_proc_id', 'proc_id', 'proc_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('sino', 'Caso Externo', 'sino_nombre', 'm_caso_externo', 'sino_id', 'sino_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Revisar 1', 'usua_nombre', 'm_usuario_revisado', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Revisar 2', 'usua_nombre', 'm_usuario_revisado2', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Revisar 3', 'usua_nombre', 'm_usuario_revisado3', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Aprobar 1', 'usua_nombre', 'm_usuario_aprobado', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Aprobar 2', 'usua_nombre', 'm_usuario_aprobado2', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
        <?php echo catalogo('usuarios', 'Usuario encargado de Aprobar 3', 'usua_nombre', 'm_usuario_aprobado3', 'usua_id', 'usua_nombre', '0', '0', '500'); ?>
      </tr>
      <tr>
      <tr>
        <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
      </tr>
    </table>
  </div>
  <a href='javascript:void(0);' id='close2'>close</a>
</div>
<div id=result></div>
<!-- Modal para aceptar el caso y agregar observaciones-->
<div class="modal fade" id="revisado-observaciones" tabindex="-1" aria-labelledby="revisado-observaciones" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-observaciones">
          <input type="hidden" id="caso_id" name="caso_id">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">Observaciones</span>
            </div>
            <textarea class="form-control" aria-label="Observaciones" name="observaciones" id="observaciones"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="aprobarCaso()">Aprobar caso</button>
      </div>
    </div>
  </div>
</div>
<script>
  $('#revisado-observaciones').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var caso_id = button.data('casoid') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this)
    modal.find('.modal-body #caso_id').val(caso_id)
  })
</script>