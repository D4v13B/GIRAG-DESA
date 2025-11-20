<script>
$(function () {
   mostrar();
});
function crear() {
$('#result').load('ctlg_menu_roles_crear.php?idmp=1'
     + '&i_usti_id=' + encodeURI($('#i_usti_id').val())
     + '&i_menu_id=' + encodeURI($('#i_menu_id').val())
    ,
    function(){
        $('#modal').hide('slow');
        $('#overlay').hide();
        mostrar();
    }
  );
}
function modificar() {
$('#result').load('ctlg_menu_roles_modificar.php?id=' + $('#h2_id').val()
     + '&m_usti_id=' + encodeURI($('#m_usti_id').val())
     + '&m_menu_id=' + encodeURI($('#m_menu_id').val())
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
   $('#result').load('ctlg_menu_roles_borrar.php?id=' + id
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
$.get('ctlg_menu_roles_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_usti_id').val(r_array[1]);
     $('#m_menu_id').val(r_array[2]);
     });
}
function mostrar() {
$('#datos_mostrar').load('ctlg_menu_roles_mostrar.php?temp=0'
		+"&usti_id=" + $('#usti_id').val());
}
</script>
<div id='separador'>
<table class=filtros>
<tr>
<?php echo catalogo('usuarios_tipos', 'Tipo de Usuario', 'usti_nombre', 'usti_id', 'usti_id', 'usti_nombre',0,1,200)?>
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
<?php echo catalogo('usuarios_tipos', 'Tipo de Usuario', 'usti_nombre', 'i_usti_id', 'usti_id', 'usti_nombre',0,0,200)?>
</tr>
<tr>
<?php echo catalogo('menu a, menu_madre b', 'Opci&oacute;n', 'mema_nombre, menu_nombre', 'i_menu_id', 'menu_id', "concat(mema_nombre,'-',menu_nombre) ",0,1,200, ' WHERE a.mema_id=b.mema_id', '', 'nmenu', 'nmenu')?>
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
<?php echo catalogo('usuarios_tipos', 'Tipo de Usuario', 'usti_nombre', 'm_usti_id', 'usti_id', 'usti_nombre',0,0,200)?>
</tr>
<tr>
<?php echo catalogo('menu', 'Opci&oacute;n', 'menu_nombre', 'm_menu_id', 'menu_id', 'menu_nombre',0,0,200)?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='#' id='close2'>close</a>
</div>
<div id=result></div>