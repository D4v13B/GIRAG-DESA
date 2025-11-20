<script>
function crear() {
$('#result').load('cons_consignee_crear.php'
,
{
    'i_cons_nombre':  $('#i_cons_nombre').val(),
    'i_pais_id':  $('#i_pais_id').val(),
    'i_cons_ciudad':  $('#i_cons_ciudad').val(),
    'i_cons_direccion':  $('#i_cons_direccion').val()
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
$('#result').load('cons_consignee_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_cons_id':  $('#m_cons_id').val(),
     'm_cons_nombre':  $('#m_cons_nombre').val(),
     'm_pais_id':  $('#m_pais_id').val(),
     'm_cons_ciudad':  $('#m_cons_ciudad').val(),
     'm_cons_direccion':  $('#m_cons_direccion').val()
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
   $('#result').load('cons_consignee_borrar.php?id=' + id
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
$.get('cons_consignee_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_cons_nombre').val(r_array[1]);
     $('#m_pais_id').val(r_array[2]);
     $('#m_cons_ciudad').val(r_array[3]);
     $('#m_cons_direccion').val(r_array[4]);
     });
}
function mostrar() {
$('#datos_mostrar').load('cons_consignee_mostrar.php?nochk=jjjlae222'
		+"&f_cons_nombre=" +  $('#f_cons_nombre').val()
		+"&f_pais_id=" +  $('#f_pais_id').val()
		+"&f_cons_ciudad=" +  $('#f_cons_ciudad').val()
		+"&f_cons_direccion=" +  $('#f_cons_direccion').val()
);}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr><tr>
<?php echo entrada('input', 'Consignee','f_cons_nombre','150')?>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'f_pais_id', 'pais_id', 'pais_nombre', '0', '1', '150');?>
<?php echo entrada('input', 'Ciudad','f_cons_ciudad','150')?></tr><tr>
<?php echo entrada('input', 'Dirección','f_cons_direccion','150')?>
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
<?php echo entrada('input', 'Consignee', 'i_cons_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'i_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Ciudad', 'i_cons_ciudad', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Dirección', 'i_cons_direccion', '150');?>
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
<?php echo entrada('input', 'Consignee', 'm_cons_nombre', '150');?>
</tr>
<tr>
<?php echo catalogo('paises', 'Pais', 'pais_nombre', 'm_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Ciudad', 'm_cons_ciudad', '150');?>
</tr>
<tr>
<?php echo entrada('input', 'Dirección', 'm_cons_direccion', '150');?>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

