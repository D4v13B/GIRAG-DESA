<script>
  function resetearUsuario(usua_id) {
    Swal.fire({
      title: "¿Estás seguro?",
      text: "Esto reinicia la password del usuario",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, reiniciar!"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "ajax/usuarios_verificar.php",
          method: "POST",
          data: {
            usua_id: usua_id
          },
          success: res => {
            res = JSON.parse(res)

            if (res.success) {
              Swal.fire({
                icon: "success",
                title: "Usuario reiniciado"
              })
            }
          }
        })
      }
    })
  }


  function crear() {

    $('#result').load('usuarios_crear.php'

      ,

      {

        'i_usua_nombre': $('#i_usua_nombre').val(),

        'i_usti_id': $('#i_usti_id').val(),

        'i_usua_password': $('#i_usua_password').val(),

        'i_usua_nombre_completo': $('#i_usua_nombre_completo').val(),

        'i_usua_mail': $('#i_usua_mail').val(),

        'i_usua_sms_aprueba': $('#i_usua_sms_aprueba').val(),

        'i_usua_administrador_caso': $("#i_usua_administrador_caso").val(),

        'i_usca_id': $("#i_usca_id").val(),
        'i_usua_cedula': $("#i_usua_cedula").val()

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

    $('#result').load('usuarios_modificar.php?id=' + $('#h2_id').val()

      ,

      {

        'm_usua_id': $('#m_usua_id').val(),

        'm_usua_nombre': $('#m_usua_nombre').val(),

        'm_usti_id': $('#m_usti_id').val(),

        'm_usua_password': $('#m_usua_password').val(),

        'm_usua_nombre_completo': $('#m_usua_nombre_completo').val(),

        'm_usua_mail': $('#m_usua_mail').val(),

        'm_usua_sms_aprueba': $('#m_usua_sms_aprueba').val(),

        'm_usua_administrador_caso': $("#m_usua_administrador_caso").val(),
        'm_usua_cedula': $("#m_usua_cedula").val(),

        'm_usca_id':$("#m_usca_id").val()

      }

      ,

      function() {

        $('#modal2').hide('slow');

        $('#overlay2').hide();

        mostrar();

      }

    );

  }

  function borrar(id)

  {

    var agree = confirm('¿Está seguro?');

    if (agree) {

      $('#result').load('usuarios_borrar.php?id=' + id

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

    $.get('usuarios_datos.php?id=' + id, function(data) {

      var resp = data;

      r_array = resp.split('||');

      //alert(r_array[0]);

      $('#m_usua_nombre').val(r_array[1]);

      $('#m_usti_id').val(r_array[2]);

      $('#m_usua_password').val(r_array[3]);

      $('#m_usua_nombre_completo').val(r_array[4]);

      $('#m_usua_mail').val(r_array[5]);

      $('#m_usua_sms_aprueba').val(r_array[6]);

      $("#m_usua_administrador_caso").val(r_array[7]);

      $("#m_usca_id").val(r_array[8]);
      $("#m_usua_cedula").val(r_array[9]);

    });

  }


  function mostrar() {

    $('#datos_mostrar').load('usuarios_mostrar.php?nochk=jjjlae222'

      +
      "&f_usua_nombre=" + $('#f_usua_nombre').val()

      +
      "&f_usti_id=" + $('#f_usti_id').val()

      +
      "&f_usua_password=" + $('#f_usua_password').val()

      +
      "&f_usua_nombre_completo=" + $('#f_usua_nombre_completo').val()

      +
      "&f_usua_mail=" + $('#f_usua_mail').val()
       +
      "&f_usua_cedula=" + $('#f_usua_cedula').val()

      +
      "&f_usua_sms_aprueba=" + $('#f_usua_sms_aprueba').val()

    );
  }





  function roles(id)

  {

    $('#h3_id').val(id);

    $('#dv_usuarios_roles').load('usro_usuarios_roles_mostrar.php?user_id=' + id

      ,

      function()

      {

        $('#modal3').show();

        $('#modal3').center();

        $('#overlay3').show();

      }

    );



  }



  function crear_rol() {

    $('#result').load('usro_usuarios_roles_crear.php?idmp=1'

      +
      '&i_usua_id=' + encodeURI($('#h3_id').val())

      +
      '&i_paro_id=' + encodeURI($('#paro_id').val())

      ,

      function(data)

      {

        //alert(data);

        roles($('#h3_id').val());

      }

    );

  }



  function borrar_rol(id)

  {

    var agree = confirm('¿Está seguro?');

    if (agree) {

      $('#result').load('usro_usuarios_roles_borrar.php?id=' + id

        ,

        function()

        {

          mostrar();

        }

      );

    }

  }
</script>

<div id='separador'>

  <table width='' class=filtros>

    <tr>
    <tr>

      <?php echo entrada('input', 'Usuario', 'f_usua_nombre', '150') ?>

      <?php echo catalogo('usuarios_tipos', 'Tipo', 'usti_nombre', 'f_usti_id', 'usti_id', 'usti_nombre', '0', '1', '150'); ?>

      <?php echo entrada('input', 'Password', 'f_usua_password', '150') ?></tr>
    <tr>

      <?php echo entrada('input', 'Nombre Completo', 'f_usua_nombre_completo', '150') ?>

      <?php echo entrada('input', 'E-mail', 'f_usua_mail', '150') ?>
       <?php echo entrada('input', 'Cédula', 'f_usua_usua_cedula', '150') ?>

      <?php echo catalogo('sino', 'Aprueba Casos', 'sino_nombre', 'f_usua_sms_aprueba', 'sino_id', 'sino_nombre', '0', '1', '150'); ?>

    </tr>
    <tr>

      <td class='tabla_datos'>
        <div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div>
      </td>

      <td>
        <div id='dmodal' style='text-align:right'><a href='#' class=botones>Nuevo</a></div>
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

        <?php echo entrada('input', 'Usuario', 'i_usua_nombre', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('usuarios_tipos', 'Tipo', 'usti_nombre', 'i_usti_id', 'usti_id', 'usti_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('usuarios_cargos', 'Cargo', 'usca_nombre', 'i_usca_id', 'usca_id', 'usca_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'Password', 'i_usua_password', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'Nombre Completo', 'i_usua_nombre_completo', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'E-mail', 'i_usua_mail', '150'); ?>

      </tr>

        <tr>

        <?php echo entrada('input', 'Cédula', 'i_usua_cedula', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('sino', 'Aprueba Casos', 'sino_nombre', 'i_usua_sms_aprueba', 'sino_id', 'sino_nombre', '0', '0', '150'); ?></tr>

      <tr>
        <?php echo catalogo('sino', 'Adminitra Casos', 'sino_nombre', 'i_usua_administrador_caso', 'sino_id', 'sino_nombre', '0', '0', '150'); ?>

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

        <?php echo entrada('input', 'Usuario', 'm_usua_nombre', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('usuarios_tipos', 'Tipo/Rol', 'usti_nombre', 'm_usti_id', 'usti_id', 'usti_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('usuarios_cargos', 'Cargo', 'usca_nombre', 'm_usca_id', 'usca_id', 'usca_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'Password', 'm_usua_password', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'Nombre Completo', 'm_usua_nombre_completo', '150'); ?>

      </tr>

      <tr>

        <?php echo entrada('input', 'E-mail', 'm_usua_mail', '150'); ?>

      </tr>
         <tr>

        <?php echo entrada('input', 'Cédula', 'm_usua_cedula', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('sino', 'Aprueba Casos', 'sino_nombre', 'm_usua_sms_aprueba', 'sino_id', 'sino_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <?php echo catalogo('sino', 'Administra casos', 'sino_nombre', 'm_usua_administrador_caso', 'sino_id', 'sino_nombre', '0', '0', '150'); ?>

      </tr>

      <tr>

        <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>

      </tr>

    </table>

  </div>

  <a href='javascript:void(0);' id='close2'>close</a>

</div>



<div id='overlay3'></div>

<div id='modal3'>

  <div id='content3'>

    <input type=hidden id=h3_id>

    <table>

      <tr>

        <?php echo catalogo('pantalla_roles', 'Rol de Pantalla', 'paro_nombre', 'paro_id', 'paro_id', 'paro_nombre', 0, 1, 400) ?>

        <td><a href="javascript:crear_rol()" class=botones>Agregar</a></td>

      </tr>

      <tr>

        <td colspan=3>
          <div id=dv_usuarios_roles></div>
        </td>

      </tr>

    </table>

  </div>

  <a href='javascript:void(0)' id='close3'>close</a>

</div>



<div id=result></div>