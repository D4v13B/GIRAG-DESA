<?php include('funciones_ui.php');?>
<script>

function modificar() {
$('#result').load('carg_carga_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_carg_id':  $('#m_carg_id').val(),
     'm_cati_id':  $('#m_cati_id').val(),
     'm_carg_guia':  $('#m_carg_guia').val(),
     'm_vuel_id':  $('#m_vuel_id').val(),
     'm_aeco_id_destino_final':  $('#m_aeco_id_destino_final').val(),
     'm_usua_id_creador':  $('#m_usua_id_creador').val(),
     'm_carg_fecha_registro':  $('#m_carg_fecha_registro').val(),
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
     $('#m_carg_id').val(r_array[0]);
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
$('#datos_mostrar').load('carg_carga_mostrar2.php?nochk=jjjlae222'
		+"&f_carg_guia=" +  $('#f_carg_guia').val()
		+"&f_liae_id=" +  $('#f_liae_id').val()
		+"&f_caes_id=" +  $('#f_caes_id').val()
);}
</script>
<input type=hidden id=vuelo>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Guia','f_carg_guia','150')?>
<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Buscar</a></div></td>
<!-- <td><div id='dmodal' style='text-align:right'><a href='#' class=botones>Nuevo</a></div></td> -->
</tr>
</table>
</div>
<div id='columna6'>
<div id='datos_mostrar'></div>
</div>
<!--MODAL-->
<div id='overlay'></div>

<div id='overlay2'></div>
<div id='modal2'><div id='content2'>
<input type=hidden id=h2_id><table>
<tr>
<?php echo catalogo('carga_tipos', 'Tipo', 'cati_nombre', 'm_cati_id', 'cati_id', 'cati_nombre', '0', '0', '150');?>
</tr>
<input type="text" id="m_carg_id">
<tr>
<td class='etiquetas'>Vuelo:</td>
<td><input type='text' id=m_vuel_id size=40 class='entradas'></td>
</tr>
<tr>
<?php echo catalogo('aereopuertos_codigos', 'Destino final', 'aeco_nombre', 'm_aeco_id_destino_final', 'aeco_id', 'aeco_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo catalogo('usuarios', 'Registrado por', 'usua_nombre', 'm_usua_id_creador', 'usua_id', 'usua_nombre', '0', '0', '150');?>
</tr>
<tr>
<td class='etiquetas'>Fecha de registro:</td>
<td><input type='text' id=m_carg_fecha_registro size=40 class='entradas'></td>
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

