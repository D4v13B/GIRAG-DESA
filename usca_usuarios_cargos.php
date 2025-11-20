<script>
function crear() {
$('#result').load('usca_usuarios_cargos_crear.php'
,
{
    'i_usca_nombre':  $('#i_usca_nombre').val(),
    'i_ucsa_gerente':  $('#i_ucsa_gerente').val()
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
$('#result').load('usca_usuarios_cargos_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_usca_id':  $('#m_usca_id').val(),
     'm_usca_nombre':  $('#m_usca_nombre').val(),
     'm_ucsa_gerente':  $('#m_ucsa_gerente').val()
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
   $('#result').load('usca_usuarios_cargos_borrar.php?id=' + id
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
$.get('usca_usuarios_cargos_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_usca_nombre').val(r_array[1]);
     $('#m_ucsa_gerente').val(r_array[2]);
     });
}
function mostrar() {
$('#datos_mostrar').load('usca_usuarios_cargos_mostrar.php?nochk=jjjlae222'
		+"&f_usca_nombre=" +  $('#f_usca_nombre').val()
		+"&f_ucsa_gerente=" +  $('#f_ucsa_gerente').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Cargo','f_usca_nombre','150')?>
<?php echo catalogo('sino', 'GERENTE', 'sino_nombre', 'f_ucsa_gerente', 'sino_id', 'sino_nombre', '0', '1', '150');?>
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
<?php echo entrada('input', 'Cargo', 'i_usca_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('sino', 'GERENTE', 'sino_nombre', 'i_ucsa_gerente', 'sino_id', 'sino_nombre', '0', '0', '150');?>
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
<?php echo entrada('input', 'Cargo', 'm_usca_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('sino', 'GERENTE', 'sino_nombre', 'm_ucsa_gerente', 'sino_id', 'sino_nombre', '0', '0', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

