<script>
function crear() {
    let formData = new FormData();
    formData.append("i_liae_nombre", $("#i_liae_nombre").val());
    formData.append("i_pais_id", $("#i_pais_id").val());
    formData.append("i_liae_prefijo", $("#i_liae_prefijo").val());
    formData.append("i_liae_icao", $("#i_liae_icao").val());
    formData.append("i_liae_tres_digitos", $("#i_liae_tres_digitos").val());
    formData.append("i_liae_dk", $("#i_liae_dk").val());

    let fileInput = $("#i_liae_ref")[0].files[0];
    if (fileInput) {
        formData.append("i_liae_ref", fileInput);
    }

    $.ajax({
        url: "liae_lineas_aereas_crear.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log(response); // Depuración
            $("#modal").hide("slow");
            $("#overlay").hide();

             // Limpiar formulario
            $("#i_liae_nombre").val('');
            $("#i_pais_id").val('');
            $("#i_liae_prefijo").val('');
            $("#i_liae_icao").val('');
            $("#i_liae_tres_digitos").val('');
            $("#i_liae_ref").val('');
            $("#i_liae_dk").val('');
            $("#preview").attr("src", "").hide(); 
            mostrar();
        },
        error: function (xhr, status, error) {
            console.error("Error al enviar los datos:", error);
        },
    });
}
  // Previsualización de imagen
  function previsualizarImagen(input) {
    if (input.files && input.files[0]) {
      let reader = new FileReader();
      reader.onload = function (e) {
        $("#preview").attr("src", e.target.result);
        $("#preview").show();
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
  
function modificar() {
    // Obtener el ID desde el campo oculto
    let id = $('#m_liae_id').val();
    
    // Verificar que el ID existe antes de continuar
    if (!id || id === '') {
        alert('Error: No se ha seleccionado un registro para modificar');
        return;
    }
    
    let formData = new FormData();
    formData.append('m_liae_id', id);
    formData.append('m_liae_nombre', $('#m_liae_nombre').val());
    formData.append('m_pais_id', $('#m_pais_id').val());
    formData.append('m_liae_prefijo', $('#m_liae_prefijo').val());
    formData.append('m_liae_icao', $('#m_liae_icao').val());
    formData.append('m_liae_tres_digitos', $('#m_liae_tres_digitos').val());
    formData.append('m_liae_dk', $('#m_liae_dk').val());

    let fileInput = $('#m_liae_ref')[0].files[0];
    if (fileInput) {
        formData.append('m_liae_ref', fileInput);
    }

    $.ajax({
        url: 'liae_lineas_aereas_modificar.php',  // Eliminar el parámetro GET
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log(response);
            $('#modal2').hide('slow');
            $('#overlay2').hide();
            mostrar();
        },
        error: function(xhr, status, error) {
            console.error('Error al modificar los datos:', error);
            alert('Error al modificar el registro');
        }
    });
}

function editar(id) {
    $('#modal2').show();
    $('#overlay2').show();
    $('#modal2').center();
    
    // Asignar el ID a ambos campos
    $('#h2_id').val(id);
    $('#m_liae_id').val(id);
    
    // Agregar console.log para debug
    console.log('ID enviado a editar:', id);

    $.get('liae_lineas_aereas_datos.php?id=' + id, function(data) {
        var r_array = data.split('||');
        
        // Verificar que se recibieron los datos correctamente
        // console.log('Datos recibidos:', r_array);

        $('#m_liae_nombre').val(r_array[1]);
        $('#m_pais_id').val(r_array[2]);
        $('#m_liae_prefijo').val(r_array[4]);
        $('#m_liae_icao').val(r_array[5]);
        $('#m_liae_tres_digitos').val(r_array[6]);
        $('#m_liae_dk').val(r_array[7]);

        if (r_array[3]) {
            $('#preview2').attr('src', 'https://giraglogicdesa.girag.aero/img/liae_ref/' + r_array[3]).show();
        } else {
            $('#preview2').hide();
        }
    }).fail(function(xhr, status, error) {
        console.error('Error al cargar datos:', error);
        alert('Error al cargar los datos del registro');
    });
}
  function borrar(id) {
    var agree = confirm('¿Está seguro?');
    if (agree) {
      $('#result').load('liae_lineas_aereas_borrar.php?id=' + id,
        function() {
          mostrar();
        }
      );
    }
  }


  function mostrar() {
    $.ajax({
        url: 'liae_lineas_aereas_mostrar.php',
        data: {
            nochk: 'jjjlae222',
            f_liae_nombre: $('#f_liae_nombre').val(),
            f_pais_id: $('#f_pais_id').val(),
            f_liae_ref: $('#f_liae_ref').val()
        },
        success: function(response) {
            console.log('Respuesta:', response);
            $('#datos_mostrar').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

$(function () {
  // Helpers
  const cerrarModal1 = () => { $('#modal').hide('slow');  $('#overlay').hide();  };
  const cerrarModal2 = () => { $('#modal2').hide('slow'); $('#overlay2').hide(); };

  // Abrir "Nuevo" (modal 1)
  $('#dmodal a').on('click', function (e) {
    e.preventDefault();
    $('#modal').show();
    $('#overlay').show();
    // Si tienes el plugin .center(), opcional:
    if (typeof $('#modal').center === 'function') $('#modal').center();
  });

  // Cerrar modal 1 (si agregas un botón con id="close")
  $('#close').on('click', function (e) { e.preventDefault(); cerrarModal1(); });
  $('#overlay').on('click', cerrarModal1);        // cerrar al hacer click fuera

  // Cerrar modal 2 (tu botón actual)
  $('#close2').on('click', function (e) { e.preventDefault(); cerrarModal2(); });
  $('#overlay2').on('click', cerrarModal2);       // cerrar al hacer click fuera

  // Previsualización de imagen (conéctalo al input)
  $('#i_liae_ref').on('change', function () { previsualizarImagen(this); });
});

</script>
<div id='separador'>
  <table width='' class=filtros>
    <tr>
      <?php echo entrada('input', 'Nombre', 'f_liae_nombre', '150') ?>
      <?php echo catalogo('paises', 'Pais', 'pais_nombre', 'f_pais_id', 'pais_id', 'pais_nombre', '0', '1', '150'); ?>
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
<div id="modal">
  <div id="content">
    <a href="javascript:void(0);" id="close" class="cerrar-modal">×</a> <!-- Botón de cerrar -->
    <table>
      <tr><?php echo entrada('input', 'Nombre', 'i_liae_nombre', '150'); ?></tr>
      <tr><?php echo catalogo('paises', 'Pais', 'pais_nombre', 'i_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150'); ?></tr>
      <tr><?php echo entrada('input', 'Prefijo', 'i_liae_prefijo', '150'); ?></tr>
      <tr><?php echo entrada('input', 'Código ICAO', 'i_liae_icao', '150'); ?></tr>
      <tr><?php echo entrada('input', 'Código Tres Dígitos', 'i_liae_tres_digitos', '150'); ?></tr>
      <tr><?php echo entrada('input', 'DK', 'i_liae_dk', '150'); ?></tr>
      <tr>
        <?php echo entrada('file', 'Imagen', 'i_liae_ref', '150'); ?>
        <td>
          <img id="preview" src="#" alt="Previsualización" style="display:none; max-width:150px; max-height:150px;" />
        </td>
      </tr>
      <tr>
        <td colspan=2><a href="javascript:crear()" class="botones">Crear</a></td>
      </tr>
    </table>
  </div>
</div>


<div id='overlay2'></div>
<div id='modal2'>
  <div id='content2'>
   <input type="hidden" id="m_liae_id" name="m_liae_id">

    <table>
      <tr>
        <?php echo entrada('input', 'Nombre', 'm_liae_nombre', '150'); ?>
      </tr>
      <tr>
        <?php echo catalogo('paises', 'Pais', 'pais_nombre', 'm_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150'); ?>
      </tr>
      <tr>
        <?php echo entrada('input', 'Prefijo', 'm_liae_prefijo', '150'); ?>
      </tr>
      <tr>
        <?php echo entrada('file', 'Imagen', 'm_liae_ref', '150'); ?>
      </tr>
      <tr>
        <?php echo entrada('input', 'DK', 'm_liae_dk', '150'); ?>
      </tr>
      <tr>
        <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
      </tr>
    </table>
  </div>
  <a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>


<style>
  #overlay, #overlay2 {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  z-index: 999;
}
#modal, #modal2 {
  display: none;
  position: fixed; /* o absolute si usas .center() */
  z-index: 1000;   /* mayor que el overlay */
}

</style>