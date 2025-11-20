<script>
function crear() {
  $('#result').load('case_carga_servicios_crear.php',
  {
    'i_case_nombre':  $('#i_case_nombre').val(),
    'i_cadt_id':  $('#i_cadt_id').val(),
    'i_case_monto':  $('#i_case_monto').val(),
    'i_case_itbms':  $('#i_case_itbms').val(),
    'i_case_reembolsable':  $('#i_case_reembolsable').val(),
    'i_case_peso_minimo':  $('#i_case_peso_minimo').val(),
    'i_case_cuenta':  $('#i_case_cuenta').val(),
    'i_case_ait':  $('#i_case_ait').val(),
    'i_case_monto_max':  $('#i_case_monto_max').val(),
    'i_liae_id':  $('#i_liae_id').val()
  },
  function(){
    $('#modal').hide('slow');
    $('#overlay').hide();
    mostrar();

    // 🔹 limpiar inputs y selects del modal de crear
    $('#i_case_nombre').val('');
    $('#i_cadt_id').val('');
    $('#i_case_monto').val('');
    $('#i_case_monto_max').val('');
    $('#i_case_itbms').val('0');
    $('#i_case_ait').val('0');
    $('#i_case_reembolsable').val('0');
    $('#i_case_peso_minimo').val('');
    $('#i_case_cuenta').val('');
    $('#i_liae_id').val('');
  });
}

function modificar() {
$('#result').load('case_carga_servicios_modificar.php?id=' + $('#h2_id').val()
,
{
    'm_case_id':  $('#m_case_id').val(),
    'm_case_nombre':  $('#m_case_nombre').val(),
    'm_cadt_id':  $('#m_cadt_id').val(),
    'm_case_monto':  $('#m_case_monto').val(),
    'm_case_itbms':  $('#m_case_itbms').val(),
    'm_case_reembolsable':  $('#m_case_reembolsable').val(),
    'm_case_peso_minimo':  $('#m_case_peso_minimo').val(),
    'm_case_cuenta':  $('#m_case_cuenta').val(),
    'm_case_ait':  $('#m_case_ait').val(),
    'm_case_monto_max':  $('#m_case_monto_max').val(),
    'm_liae_id':  $('#m_liae_id').val()
    }
    ,
    function(){
       $('#modal2').hide('slow');
       $('#overlay2').hide();
       mostrar();
    }
  );
}
function borrar(id)
{
var agree=confirm('¿Está seguro?');
if(agree) {
   $('#result').load('case_carga_servicios_borrar.php?id=' + id
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
$.get('case_carga_servicios_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
    $('#m_case_nombre').val(r_array[1]);
    $('#m_cadt_id').val(r_array[2]);
    $('#m_case_monto').val(r_array[3]);
    $('#m_case_itbms').val(r_array[4]);
    $('#m_case_reembolsable').val(r_array[5]);
    $('#m_case_peso_minimo').val(r_array[6]);
    $('#m_case_cuenta').val(r_array[7]);
    $('#m_case_ait').val(r_array[8]);
    $('#m_case_monto_max').val(r_array[9]);
    $('#m_liae_id').val(r_array[10]);
     });
}
function mostrar() {
$('#datos_mostrar').load('case_carga_servicios_mostrar.php?nochk=jjjlae222'
		+"&f_case_nombre=" +  $('#f_case_nombre').val()
		+"&f_cadt_id=" +  $('#f_cadt_id').val()
		+"&f_case_monto=" +  $('#f_case_monto').val()
		+"&f_case_itbms=" +  $('#f_case_itbms').val()
		+"&f_case_reembolsable=" +  $('#f_case_reembolsable').val()
		+"&f_case_peso_minimo=" +  $('#f_case_peso_minimo').val()
    
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Servicio','f_case_nombre','150')?>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'f_cadt_id', 'cade_tipo_id', 'cade_descripcion', '0', '1', '150');?>
<?php echo entrada('input', 'Predeterminado','f_case_monto','150')?></tr><tr>
<?php echo entrada('input', 'Lleva Itbms','f_case_itbms','150')?>
<?php echo entrada('input', 'Es Reembolsable','f_case_reembolsable','150')?>
<?php echo entrada('input', 'Peso Mínimo','f_case_peso_minimo','150')?></tr><tr>
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
<div id='modal'><div id='content'>
<table>
<tr>
<?php echo entrada('input', 'Servicio', 'i_case_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'i_cadt_id', 'cade_tipo_id', 'cade_descripcion', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Monto Base', 'i_case_monto', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Monto Kg adicional', 'i_case_monto_max', '150');?>
</tr>
<tr>
<td>Lleva ITBMS</td>
<td>
  <select id="i_case_itbms">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<td>Incluye AIT</td>
<td>
  <select id="i_case_ait">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<td>Es Reembolsable</td>
<td>
  <select id="i_case_reembolsable">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<?php echo catalogo('lineas_aereas', 'Aerolinea', 'liae_nombre', 'i_liae_id', 'liae_id', 'liae_nombre', '2', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Peso Mínimo', 'i_case_peso_minimo', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Cuenta', 'i_case_cuenta', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:crear()' class='botones'>Crear</a></td>
</tr>
</table>
</div>
<a href='#' id='close'>close</a>
</div>

<div id='overlay2'></div>
<div id='modal2'><div id='content2'>
<input type=hidden id=h2_id><table>
<tr>
<?php echo entrada('input', 'Servicio', 'm_case_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'm_cadt_id', 'cade_tipo_id', 'cade_descripcion', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Monto Base', 'm_case_monto', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Monto Kg adicional', 'm_case_monto_max', '150');?>
</tr>
<tr>
<td>Lleva ITBMS</td>
<td>
  <select id="m_case_itbms">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<td>Incluye AIT</td>
<td>
  <select id="m_case_ait">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<td>Es Reembolsable</td>
<td>
  <select id="m_case_reembolsable">
    <option value="0">No</option>
    <option value="1">Sí</option>
  </select>
</td>
</tr>
<tr>
<?php echo catalogo('lineas_aereas', 'Aerolinea', 'liae_nombre', 'm_liae_id', 'liae_id', 'liae_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Peso Mínimo', 'm_case_peso_minimo', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Cuenta', 'm_case_cuenta', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

