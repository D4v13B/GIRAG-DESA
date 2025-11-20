<script>
function crear() {
$('#result').load('casu_casos_subclasificacion_crear.php'
,
{
    'i_casu_descripcion':  $('#i_casu_descripcion').val(),
    'i_cacl_id':  $('#i_cacl_id').val()
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
$('#result').load('casu_casos_subclasificacion_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_casu_id':  $('#m_casu_id').val(),
     'm_casu_descripcion':  $('#m_casu_descripcion').val(),
     'm_cacl_id':  $('#m_cacl_id').val()
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
   $('#result').load('casu_casos_subclasificacion_borrar.php?id=' + id
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
$.get('casu_casos_subclasificacion_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_casu_descripcion').val(r_array[1]);
     $('#m_cacl_id').val(r_array[2]);
     });
}
function mostrar() {
$('#datos_mostrar').load('casu_casos_subclasificacion_mostrar.php?nochk=jjjlae222'
		+"&f_casu_descripcion=" +  $('#f_casu_descripcion').val()
		+"&f_cacl_id=" +  $('#f_cacl_id').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Descripción','f_casu_descripcion','150')?>
<?php echo catalogo('casos_clasificacion', 'Pertenece a', 'cacl_nombre', 'f_cacl_id', 'cacl_id', 'cacl_nombre', '0', '1', '150');?>
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
<?php echo entrada('input', 'Descripción', 'i_casu_descripcion', '150');?>
</tr>
<tr>
<?php echo catalogo('casos_clasificacion', 'Pertenece a', 'cacl_nombre', 'i_cacl_id', 'cacl_id', 'cacl_nombre', '0', '0', '150');?>
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
<?php echo entrada('input', 'Descripción', 'm_casu_descripcion', '150');?>
</tr>
<tr>
<?php echo catalogo('casos_clasificacion', 'Pertenece a', 'cacl_nombre', 'm_cacl_id', 'cacl_id', 'cacl_nombre', '0', '0', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

