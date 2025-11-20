<?php include('funciones_ui.php');?>
<script>

 function limpiarFormularioCreacion() {
    // Limpiar campos de texto
    $('#i_carg_guia').val('');
    $('#i_vuel_id').val('');
    $('#vuelo').val('');
    $('#i_carg_recepcion_real').val('');
    
    // Resetear selects a su opción por defecto
    $('#i_aeco_id_destino_final').val('0');
    $('#i_liae_id').val('0');
    $('#i_caes_id').val('0');
    
    // Mantener i_cati_id en 'Export' según tu lógica actual
    $('#i_cati_id').find('option').each(function() {
        if ($(this).text().trim() === 'Transferencia exportación') {
            $(this).prop('selected', true);
        }
    });
    
    // Limpiar autocompletado de vuelo si existe
    if ($('#i_vuel_id').hasClass('ui-autocomplete-input')) {
        $('#i_vuel_id').autocomplete('close');
    }
}

function crear() {
$('#result').load('carg_carga_crear.php'
,
{
    'i_cati_id':  $('#i_cati_id').val(),
    'i_carg_guia':  $('#i_carg_guia').val(),
    'i_vuel_id':  $('#vuelo').val(),
    'i_aeco_id_destino_final':  $('#i_aeco_id_destino_final').val(),
    'i_usua_id_creador':  $('#i_usua_id_creador').val(),
    //'i_carg_fecha_registro':  $('#i_carg_fecha_registro').val(),
    'i_carg_recepcion_real':  $('#i_carg_recepcion_real').val(),
    'i_liae_id':  $('#i_liae_id').val(),
    'i_caes_id':  $('#i_caes_id').val()
    }
    ,
    function(){
        $('#modal').hide('slow');
        $('#overlay').hide();
        mostrar();
        limpiarFormularioCreacion();
    }
  );
}
function modificar() {
$('#result').load('carg_carga_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_carg_id':  $('#m_carg_id').val(),
     'm_cati_id':  $('#m_cati_id').val(),
     //'m_carg_guia':  $('#m_carg_guia').val(),
     'm_vuel_id':  $('#m_vuel_id').val(),
     'm_aeco_id_destino_final':  $('#m_aeco_id_destino_final').val(),

     'm_carg_recepcion_real':  $('#m_carg_recepcion_real').val(),
     'm_liae_id':  $('#m_liae_id').val(),
     'm_caes_id':  $('#m_caes_id').val()
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
   $('#result').load('carg_carga_borrar.php?id=' + id
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
$.get('carg_carga_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     $("#m_carg_id").val(r_array[0]);
     $('#m_cati_id').val(r_array[1]);
     $('#m_carg_guia').val(r_array[2]);
     $('#m_vuel_id').val(r_array[3]);
     $('#m_aeco_id_destino_final').val(r_array[4]);
     $('#m_usua_id_creador').val(r_array[5]);
     $('#m_carg_fecha_registro').val(r_array[6]);
     $('#m_carg_recepcion_real').val(r_array[7]);
     $('#m_liae_id').val(r_array[8]);
     $('#m_caes_id').val(r_array[9]);
     
     });
}
function mostrar() {
$('#datos_mostrar').load('carg_carga_mostrar.php?nochk=jjjlae222'
		+"&f_cati_id=" +  $('#f_cati_id').val()
		+"&f_carg_guia=" +  $('#f_carg_guia').val()
        +"&f_vuel_codigo=" +  $('#f_vuel_codigo').val()
		+"&f_aeco_id_destino_final=" +  $('#f_aeco_id_destino_final').val()
		+"&f_usua_id_creador=" +  $('#f_usua_id_creador').val()
		+"&f_carg_fecha_registro=" +  $('#f_carg_fecha_registro').val()
		+"&f_carg_recepcion_real=" +  $('#f_carg_recepcion_real').val()
		+"&f_liae_id=" +  $('#f_liae_id').val()
		+"&f_caes_id=" +  $('#f_caes_id').val()
);}

$(document).ready(function() {
    // Set initial value for both filter and modal selectors
    $('#f_cati_id, #i_cati_id, #m_cati_id').each(function() {
        // Find and select the Export option
        $(this).find('option').each(function() {
            if ($(this).text().trim() === 'Transferencia exportación') {
                $(this).prop('selected', true);
            } else {
                $(this).prop('disabled', true);
            }
        });
    });
    
    // Prevent changing the selection
    $('#f_cati_id, #i_cati_id, #m_cati_id').on('change', function(e) {
        var $exportOption = $(this).find('option').filter(function() {
            return $(this).text().trim() === 'Transferencia exportación';
        });
        $exportOption.prop('selected', true);
        return false;
    });
});

$(document).ready(function() {
    // Seleccionar automáticamente los estados específicos al cargar la página
    $('#f_caes_id').find('option').each(function() {
        var estadoTexto = $(this).text().trim();
        
        // Seleccionar Borrador, Recibida y Retenida
        if (estadoTexto === 'Borrador' || estadoTexto === 'Recibida' || estadoTexto === 'Retenida') {
            $(this).prop('selected', true);
        } 
        // Deseleccionar los demás estados, incluyendo la opción "Todos" si existe
        else {
            $(this).prop('selected', false);
        }
    });
    
    // Disparar evento change para actualizar cualquier dependencia
    $('#f_caes_id').trigger('change');
});
</script>
<?php echo autocompletar_filtro('i_vuel_id', 'obtener_vuelos.php', 'codigo_vuelo', '1', 'vuelo')?>
<input type=hidden id=vuelo>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo catalogo('carga_tipos', 'Tipo', 'cati_nombre', 'f_cati_id', 'cati_id', 'cati_nombre', '0', '1', '150');?>
<?php echo entrada('input', 'Guia','f_carg_guia','150')?>
<?php echo entrada('input', 'Vuelo', 'f_vuel_codigo', '150') ?></tr><tr>
<?php echo catalogo('aereopuertos_codigos', 'Destino final', 'aeco_nombre', 'f_aeco_id_destino_final', 'aeco_id', 'aeco_nombre', '0', '1', '150');?>
<?php echo catalogo('usuarios', 'Registrado por', 'usua_nombre', 'f_usua_id_creador', 'usua_id', 'usua_nombre', '0', '1', '150');?>

<?php echo entrada('fecha', 'Fecha de registro','f_carg_fecha_registro','150')?></tr><tr>
<?php echo entrada('fecha', 'Fecha de recepcion','f_carg_recepcion_real','150')?>
<?php echo catalogo('lineas_aereas', 'Linea aerea', 'liae_nombre', 'f_liae_id', 'liae_id', 'liae_nombre', '0', '1', '150');?>
<?php echo catalogo('carga_estado', 'Estado', 'caes_nombre', 'f_caes_id', 'caes_id', 'caes_nombre', '0', '1', '150');?></tr><tr>
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
<?php echo catalogo('carga_tipos', 'Tipo', 'cati_nombre', 'i_cati_id', 'cati_id', 'cati_nombre', '0', '0', '350');?>
</tr>
<tr>
<?php echo entrada('input','Vuelo','i_vuel_id','350') ?>
</tr>
<tr>
<?php echo catalogo('aereopuertos_codigos', 'Destino final', 'aeco_nombre', 'i_aeco_id_destino_final', 'aeco_id', 'aeco_nombre', '0', '0', '350');?>
</tr>


<tr>
<?php echo entrada('fecha','Fecha de recepcion','i_carg_recepcion_real','350') ?>
</tr>
<tr>
<?php echo catalogo('lineas_aereas', 'Linea aerea', 'liae_nombre', 'i_liae_id', 'liae_id', 'liae_nombre', '0', '0', '350');?>
</tr>
<tr>
<?php echo catalogo('carga_estado', 'Estado', 'caes_nombre', 'i_caes_id', 'caes_id', 'caes_nombre', '0', '0', '350');?>
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
  <input type="hidden" id="m_carg_id">
<?php echo catalogo('carga_tipos', 'Tipo', 'cati_nombre', 'm_cati_id', 'cati_id', 'cati_nombre', '0', '0', '150');?>
</tr>
<tr>
<td class='etiquetas'>Vuelo:</td>
<td><input type='text' id=m_vuel_id size=40 class='entradas'></td>
</tr>
<tr>
<?php echo catalogo('aereopuertos_codigos', 'Destino final', 'aeco_nombre', 'm_aeco_id_destino_final', 'aeco_id', 'aeco_nombre', '0', '0', '150');?>
</tr>


<tr>
<td class='etiquetas'>Fecha de recepcion:</td>
<td><input type='text' id=m_carg_recepcion_real size=40 class='entradas'></td>
</tr>
<tr>
<?php echo catalogo('lineas_aereas', 'Linea aerea', 'liae_nombre', 'm_liae_id', 'liae_id', 'liae_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo catalogo('carga_estado', 'Estado', 'caes_nombre', 'm_caes_id', 'caes_id', 'caes_nombre', '0', '0', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

