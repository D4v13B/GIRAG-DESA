<script>
function crear() {
$('#result').load('usec_usuarios_email_config_crear.php'
,
{
    'i_usec_email':  $('#i_usec_email').val(),
    'i_usec_password':  $('#i_usec_password').val(),
    'i_usmd_id':  $('#i_usmd_id').val(),
    'i_usec_estado':  $('#i_usec_estado').val(),
    'i_usec_smtp':  $('#i_usec_smtp').val()
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
$('#result').load('usec_usuarios_email_config_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_usec_id':  $('#m_usec_id').val(),
     'm_usec_email':  $('#m_usec_email').val(),
     'm_usec_password':  $('#m_usec_password').val(),
     'm_usmd_id':  $('#m_usmd_id').val(),
     'm_usec_estado':  $('#m_usec_estado').val(),
     'm_usec_smtp':  $('#m_usec_smtp').val()
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
   $('#result').load('usec_usuarios_email_config_borrar.php?id=' + id
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
$.get('usec_usuarios_email_config_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_usec_email').val(r_array[1]);
     $('#m_usec_password').val(r_array[2]);
     $('#m_usmd_id').val(r_array[3]);
     $('#m_usec_estado').val(r_array[4]);
     $('#m_usec_smtp').val(r_array[5]);
     });
}
function mostrar() {
$('#datos_mostrar').load('usec_usuarios_email_config_mostrar.php?nochk=jjjlae222'
		+"&f_usec_email=" +  $('#f_usec_email').val()
		+"&f_usec_password=" +  $('#f_usec_password').val()
		+"&f_usmd_id=" +  $('#f_usmd_id').val()
		+"&f_usec_estado=" +  $('#f_usec_estado').val()
		+"&f_usec_smtp=" +  $('#f_usec_smtp').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Email del usuario','f_usec_email','150')?>
<?php echo entrada('input', 'Contraseña','f_usec_password','150')?>
<?php echo catalogo('usuarios_email_dedicado_a', 'Dedicado a', 'usmd_nombre', 'f_usmd_id', 'usmd_id', 'usmd_nombre', '0', '1', '150');?>
</tr><tr>
<?php echo entrada('input', 'Servidor de SMTP','f_usec_smtp','150')?>
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
<?php echo entrada('input', 'Email del usuario', 'i_usec_email', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Contraseña', 'i_usec_password', '150');?>
</tr>
<tr>
<?php echo catalogo('usuarios_email_dedicado_a', 'Dedicado a', 'usmd_nombre', 'i_usmd_id', 'usmd_id', 'usmd_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo catalogo('usuarios_email_config_estado', 'Estado', 'usec_nombre', 'i_usec_estado', 'usec_id', 'usec_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Servidor de SMTP', 'i_usec_smtp', '150');?>
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
<?php echo entrada('input', 'Email del usuario', 'm_usec_email', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Contraseña', 'm_usec_password', '150');?>
</tr>
<tr>
<?php echo catalogo('usuarios_email_dedicado_a', 'Dedicado a', 'usmd_nombre', 'm_usmd_id', 'usmd_id', 'usmd_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo catalogo('usuarios_email_config_estado', 'Estado', 'usec_nombre', 'm_usec_estado', 'usec_id', 'usec_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Servidor de SMTP', 'm_usec_smtp', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

