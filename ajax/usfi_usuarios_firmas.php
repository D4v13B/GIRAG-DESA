<?php

$sql = "SELECT * FROM usuarios";

$usuario_firma = mysql_query($sql);

$sql = "SELECT * ,(SELECT usua_nombre FROM usuarios WHERE usuarios.usua_id = usuarios_firmas.usua_id) usua_nombre FROM usuarios_firmas ORDER BY usfi_id";
$usuarios = mysql_query($sql);

?>

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
        resetForm()
        mostrar()
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


  function crear() {
    $('#result').load('usfi_usuarios_firmas_crear.php', {
        'i_usua_id': $('#i_usua_id').val()
      },
      function() {
        $('#modal').hide('slow');
        $('#overlay').hide();
        mostrar();
      }
    );
  }

  function modificar() {
    $('#result').load('usfi_usuarios_firmas_modificar.php?id=' + $('#h2_id').val(), {
        'm_usua_id': $('#m_usua_id').val()
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
      $('#result').load('usfi_usuarios_firmas_borrar.php?id=' + id,
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
    $.get('usfi_usuarios_firmas_datos.php?id=' + id, function(data) {
      var resp = data;
      r_array = resp.split('||');
      //alert(r_array[0]);
      $('#m_usua_id').val(r_array[0]);
    });
  }

  function mostrar() {
    $('#datos_mostrar').load('usfi_usuarios_firmas_mostrar.php?nochk=jjjlae222' +
      "&f_usua_id=" + $('#f_usua_id').val()
    );
  }
</script>
<div id='separador'>
  <table width='' class=filtros>
    <tr>
    <tr>
      <?php echo catalogo('usuarios', 'Nombre', 'usua_nombre', 'f_usua_id', 'usua_id', 'usua_nombre', '0', '1', '150'); ?>
      <td class='tabla_datos'>
        <div id='b_mostrar'><a href='javascript:mostrar()' class="btn btn-primary">Mostrar</a></div>
      </td>
      <td>
        <!-- <div id='dmodal' style='text-align:right'><a href='#' class=botones>Nuevo</a></div> -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-registrar-firma">
          Registra firma
        </button>


        <div class="modal fade" id="modal-registrar-firma" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar nueva firma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
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
                    <input class="form-control" type="file" id="usfi_ref" name="usfi_ref" />
                  </div>
                  <div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-success btn-lg">Guardar</button>
                    </div>
                    <!-- <button type="reset" class="btn btn-success">ANULAR</button>-->
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
              </div>
            </div>
          </div>
        </div>

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
    <table>
      <tr>
        <?php echo catalogo('usuarios', 'Nombre', 'usua_nombre', 'i_usua_id', 'usua_id', 'usua_nombre', '0', '0', ''); ?>
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
    <table>
      <tr>
        <?php echo catalogo('usuarios', 'Nombre', 'usua_nombre', 'm_usua_id', 'usua_id', 'usua_nombre', '0', '0', ''); ?>
      </tr>
      <tr>
        <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
      </tr>
    </table>
  </div>
  <a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>