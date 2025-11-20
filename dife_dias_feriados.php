<script>
function crear() {
$('#result').load('dife_dias_feriados_crear.php'
,
{
    'i_dife_descripcion':  $('#i_dife_descripcion').val(),
    'i_dife_fecha':  $('#i_dife_fecha').val()
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
$('#result').load('dife_dias_feriados_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_dife_id':  $('#m_dife_id').val(),
     'm_dife_descripcion':  $('#m_dife_descripcion').val(),
     'm_dife_fecha':  $('#m_dife_fecha').val()
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
   $('#result').load('dife_dias_feriados_borrar.php?id=' + id
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
$.get('dife_dias_feriados_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_dife_descripcion').val(r_array[1]);
     $('#m_dife_fecha').val(r_array[2]);
     });
}
function mostrar() {
$('#datos_mostrar').load('dife_dias_feriados_mostrar.php?nochk=jjjlae222'
		+"&f_dife_descripcion=" +  $('#f_dife_descripcion').val()
		+"&f_dife_fecha=" +  $('#f_dife_fecha').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Descripción','f_dife_descripcion','150')?>
<?php echo entrada('fecha', 'Fecha','f_dife_fecha','150')?>
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
<?php echo entrada('input', 'Descripción', 'i_dife_descripcion', '150');?>
</tr>
<tr>
<?php echo entrada('fecha', 'Fecha', 'i_dife_fecha', '150');?>
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
<?php echo entrada('input', 'Descripción', 'm_dife_descripcion', '150');?>
</tr>
<tr>
<?php echo entrada('fecha', 'Fecha', 'm_dife_fecha', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

