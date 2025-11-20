<?php 
session_start();
include('funciones_ui.php');

$user_check = $_SESSION['login_user'];
?>
<script>
function crear() {
$('#result').load('cade_carga_detalles_crear.php'
,
{
    'i_cade_peso':  $('#i_cade_peso').val(),
    'i_cade_piezas':  $('#i_cade_piezas').val(),
    'i_cade_desc':  $('#i_cade_desc').val(),
    'i_cade_guia':  $('#i_cade_guia').val(),
    'i_cade_tipo_id':  $('#i_cade_tipo_id').val()
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
$('#result').load('cade_carga_detalles_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_cade_id':  $('#m_cade_id').val(),
     'm_cade_peso':  $('#m_cade_peso').val(),
     'm_cade_piezas':  $('#m_cade_piezas').val(),
     'm_cade_desc':  $('#m_cade_desc').val(),
     'm_cade_guia':  $('#m_cade_guia').val(),
     'm_cade_tipo_id':  $('#m_cade_tipo_id').val()
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
   $('#result').load('cade_carga_detalles_borrar.php?id=' + id
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
$.get('cade_carga_detalles_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_cade_peso').val(r_array[1]);
     $('#m_cade_piezas').val(r_array[2]);
     $('#m_cade_desc').val(r_array[3]);
     $('#m_cade_guia').val(r_array[4]);
     $('#m_cade_tipo_id').val(r_array[5]);
     });
}
function mostrar() {
$('#datos_mostrar').load('cade_carga_detalles_mostrar.php?nochk=jjjlae222'
		+"&f_cade_peso=" +  $('#f_cade_peso').val()
    +"&f_carg_fecha_registro=" +  $('#f_carg_fecha_registro').val()
		+"&f_cade_piezas=" +  $('#f_cade_piezas').val()
		+"&f_cade_desc=" +  $('#f_cade_desc').val()
		+"&f_cade_guia=" +  $('#f_cade_guia').val()
		+"&f_cade_tipo_id=" +  $('#f_cade_tipo_id').val()
);}
</script>

<!-- AUTOCOMPLETAR PARA EL FILTRO -->
<?php echo autocompletar_filtro('f_cade_guia', 'obtener_guias.php', 'codigo_guia', '1', 'f_cade_guia_id')?>

<!-- AUTOCOMPLETAR PARA EL MODAL DE CREAR -->
<?php echo autocompletar_filtro('i_cade_guia', 'obtener_guias.php', 'codigo_guia', '1', 'i_cade_guia_id')?>

<!-- AUTOCOMPLETAR PARA EL MODAL DE MODIFICAR -->
<?php echo autocompletar_filtro('m_cade_guia', 'obtener_guias.php', 'codigo_guia', '1', 'm_cade_guia_id')?>

<div id='separador'>
<table width='' class=filtros>
<tr><tr>
  <!-- CAMPOS HIDDEN PARA LOS IDs -->
  <input type="hidden" id="f_cade_guia_id">
  <input type="hidden" id="i_cade_guia_id">
  <input type="hidden" id="m_cade_guia_id">
  
  <?php echo entrada('input', 'Fecha','f_carg_fecha_registro','150')?>
<?php echo entrada('input', 'Peso','f_cade_peso','150')?>
<?php echo entrada('input', 'Piezas','f_cade_piezas','150')?>
<?php echo entrada('input', 'Descripción','f_cade_desc','150')?></tr><tr>
<?php echo entrada('input', 'Guia','f_cade_guia','150')?>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'f_cade_tipo_id', 'cade_tipo_id', 'cade_descripcion', '0', '1', '150');?>
<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
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
<?php echo entrada('input', 'Peso', 'i_cade_peso', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Piezas', 'i_cade_piezas', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Descripción', 'i_cade_desc', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Guia', 'i_cade_guia', '150');?>
</tr>
<tr>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'i_cade_tipo_id', 'cade_tipo_id', 'cade_descripcion', '0', '0', '150');?>
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
<?php echo entrada('input', 'Peso', 'm_cade_peso', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Piezas', 'm_cade_piezas', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Descripción', 'm_cade_desc', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Guia', 'm_cade_guia', '150');?>
</tr>
<tr>
<?php echo catalogo('carga_detalle_tipo', 'Tipo', 'cade_descripcion', 'm_cade_tipo_id', 'cade_tipo_id', 'cade_descripcion', '0', '0', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

<script>
   $(function() {

      //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL
      $(".btn_borrar_factura").hide();
      <?php echo pantalla_roles("index.php?p=cade_carga_detalles_mostrar", $_SESSION["login_user"]) ?>

   })
</script>