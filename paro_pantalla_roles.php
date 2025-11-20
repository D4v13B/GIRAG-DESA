<script> 
function crear() {
$('#result').load('paro_pantalla_roles_crear.php'
,
{
    'i_paro_pantalla':  $('#i_paro_pantalla').val(),
    'i_paro_nombre':  $('#i_paro_nombre').val(),
    'i_paro_descripcion':  $('#i_paro_descripcion').val(),
    'i_paro_item_id':  $('#i_paro_item_id').val(),
    'i_paro_item_tipo':  $('#i_paro_item_tipo').val()
    }
    ,
    function(){
        $('#modal').hide('slow');
        $('#overlay').hide();
        mostrar();
    }
  );
}
function modificar() {
$('#result').load('paro_pantalla_roles_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_paro_id':  $('#m_paro_id').val(),
     'm_paro_pantalla':  $('#m_paro_pantalla').val(),
     'm_paro_nombre':  $('#m_paro_nombre').val(),
     'm_paro_descripcion':  $('#m_paro_descripcion').val(),
     'm_paro_item_id':  $('#m_paro_item_id').val(),
     'm_paro_item_tipo':  $('#m_paro_item_tipo').val()
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
   $('#result').load('paro_pantalla_roles_borrar.php?id=' + id
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
$.get('paro_pantalla_roles_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_paro_pantalla').val(r_array[1]);
     $('#m_paro_nombre').val(r_array[2]);
     $('#m_paro_descripcion').val(r_array[3]);
     $('#m_paro_item_id').val(r_array[4]);
     $('#m_paro_item_tipo').val(r_array[5]);
     });
}
function mostrar() {
$('#datos_mostrar').load('paro_pantalla_roles_mostrar.php');
}
</script>
<div id='separador'>
<table  class=filtros>
<tr>
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
<td class='etiquetas'>Pantalla:</td>
<td><input type='text' id=i_paro_pantalla size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Nombre:</td>
<td><input type='text' id=i_paro_nombre size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Descripción:</td>
<td><input type='text' id=i_paro_descripcion size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Item ID:</td>
<td><input type='text' id=i_paro_item_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Item Tipo:</td>
<td><input type='text' id=i_paro_item_tipo size=40 class='entradas'></td>
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
<td class='etiquetas'>Pantalla:</td>
<td><input type='text' id=m_paro_pantalla size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Nombre:</td>
<td><input type='text' id=m_paro_nombre size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Descripción:</td>
<td><input type='text' id=m_paro_descripcion size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Item ID:</td>
<td><input type='text' id=m_paro_item_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Item Tipo:</td>
<td><input type='text' id=m_paro_item_tipo size=40 class='entradas'></td>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

