<script>
function crear() {
$('#result').load('ship_shipper_crear.php'
,
{
    'i_ship_nombre':  $('#i_ship_nombre').val(),
    'i_ship_ciudad':  $('#i_ship_ciudad').val(),
    'i_pais_id':  $('#i_pais_id').val(),
    'i_ship_direccion':  $('#i_ship_direccion').val()
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
$('#result').load('ship_shipper_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_ship_id':  $('#m_ship_id').val(),
     'm_ship_nombre':  $('#m_ship_nombre').val(),
     'm_ship_ciudad':  $('#m_ship_ciudad').val(),
     'm_pais_id':  $('#m_pais_id').val(),
     'm_ship_direccion':  $('#m_ship_direccion').val()
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
   $('#result').load('ship_shipper_borrar.php?id=' + id
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
$.get('ship_shipper_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_ship_nombre').val(r_array[1]);
     $('#m_ship_ciudad').val(r_array[2]);
     $('#m_pais_id').val(r_array[3]);
     $('#m_ship_direccion').val(r_array[4]);
     });
}
function mostrar() {
$('#datos_mostrar').load('ship_shipper_mostrar.php?nochk=jjjlae222'
		+"&f_ship_nombre=" +  $('#f_ship_nombre').val()
		+"&f_ship_ciudad=" +  $('#f_ship_ciudad').val()
		+"&f_pais_id=" +  $('#f_pais_id').val()
		+"&f_ship_direccion=" +  $('#f_ship_direccion').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Nombre','f_ship_nombre','150')?>
<?php echo entrada('input', 'Ciudad','f_ship_ciudad','150')?>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'f_pais_id', 'pais_id', 'pais_nombre', '0', '1', '150');?></tr><tr>
<?php echo entrada('input', 'Dirección','f_ship_direccion','150')?>
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
<?php echo entrada('input', 'Nombre', 'i_ship_nombre', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Ciudad', 'i_ship_ciudad', '150');?>
</tr>
<tr>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'i_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Dirección', 'i_ship_direccion', '150');?>
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
<?php echo entrada('input', 'Nombre', 'm_ship_nombre', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Ciudad', 'm_ship_ciudad', '150');?>
</tr>
<tr>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'm_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Dirección', 'm_ship_direccion', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

