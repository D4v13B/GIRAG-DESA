<script>
  function limpiarFormularioCrear() {
  $('#calb_nombre').val('');
  $('#calb_seccion').val(''); // O el valor por defecto
  $('#calb_x').val('');
  $('#calb_y').val('');
  $('#calb_estado').val(''); // O el valor por defecto
}
function crear() {
  $('#result').load('calb_carga_localizaciones_bodega_crear.php', {
    'calb_nombre': $('#calb_nombre').val(),
    'calb_seccion': $('#calb_seccion').val(),
    'calb_x': $('#calb_x').val(),
    'calb_y': $('#calb_y').val(),
    'calb_estado': $('#calb_estado').val()
  }, function () {
    $('#modal').hide('slow');
    $('#overlay').hide();
    mostrar();
     limpiarFormularioCrear();
  });
}

function modificar() {
  $('#result').load('calb_carga_localizaciones_bodega_modificar.php?id=' + $('#h2_id').val(), {
    'calb_nombre': $('#m_calb_nombre').val(),
    'calb_seccion': $('#m_calb_seccion').val(),
    'calb_x': $('#m_calb_x').val(),
    'calb_y': $('#m_calb_y').val(),
    'calb_estado': $('#m_calb_estado').val()
  }, function () {
    $('#modal2').hide('slow');
    $('#overlay2').hide();
    mostrar();
  });
}

function borrar(id)
{
var agree=confirm('¿Está seguro?');
if(agree) {
   $('#result').load('calb_carga_localizaciones_bodega_borrar.php?id=' + id
   ,
   function()
     {
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

  $.get('calb_carga_localizaciones_bodega_datos.php?id=' + id, function (data) {
    let r_array = data.split('||');

    // Asumiendo que data viene así: nombre||seccion||x||y||estado
    $('#m_calb_nombre').val(r_array[0]);
    $('#m_calb_seccion').val(r_array[1]);
    $('#m_calb_x').val(r_array[2]);
    $('#m_calb_y').val(r_array[3]);
    $('#m_calb_estado').val(r_array[4]);
  });
}

function mostrar() {
$('#datos_mostrar').load('calb_carga_localizaciones_bodega_mostrar.php?nochk=jjjlae222'
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
<td><div id='dmodal' style='text-align:right'><a href='#' class=botones>Nuevo</a></div></td>
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
        <td>Nombre:</td>
        <td><input type="text" id="calb_nombre"></td>
      </tr>
      <tr>
        <td>Sección:</td>
        <td>
          <select id="calb_seccion">
            <option value="IMPORT">IMPORT</option>
            <option value="EXPORT">EXPORT</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Coordenada X:</td>
        <td><input type="text" id="calb_x"></td>
      </tr>
      <tr>
        <td>Coordenada Y:</td>
        <td><input type="text" id="calb_y"></td>
      </tr>
      <tr>
        <td>Estado:</td>
        <td>
          <select id="calb_estado">
            <option value="1">OCUPADA</option>
            <option value="0">DESOCUPADA</option>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2"><a href="javascript:crear()" class="botones">Crear</a></td>
      </tr>
    </table>
  </div>
  <a href="#" id="close">Cerrar</a>
</div>

<div id='overlay2'></div>
<div id='modal2'>
  <div id='content2'>
    <input type="hidden" id="h2_id">
    <table>
      <tr>
        <td>Nombre:</td>
        <td><input type="text" id="m_calb_nombre"></td>
      </tr>
      <tr>
        <td>Sección:</td>
        <td>
          <select id="m_calb_seccion">
            <option value="IMPORT">IMPORT</option>
            <option value="EXPORT">EXPORT</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Coordenada X:</td>
        <td><input type="text" id="m_calb_x"></td>
      </tr>
      <tr>
        <td>Coordenada Y:</td>
        <td><input type="text" id="m_calb_y"></td>
      </tr>
      <tr>
        <td>Estado:</td>
        <td>
          <select id="m_calb_estado">
            <option value="1">OCUPADA</option>
            <option value="0">DESOCUPADA</option>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2"><a href="javascript:modificar()" class="botones">Modificar</a></td>
      </tr>
    </table>
  </div>
  <a href="javascript:void(0);" id="close2">Cerrar</a>
</div>


<div id=result></div>

