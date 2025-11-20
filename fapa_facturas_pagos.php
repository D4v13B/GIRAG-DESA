<script>
function crear() {
$('#result').load('fapa_facturas_pagos_crear.php'
,
{
    'i_fact_id':  $('#i_fact_id').val(),
    'i_fapa_monto':  $('#i_fapa_monto').val(),
    'i_fapa_fecha':  $('#i_fapa_fecha').val(),
    'i_fopa_id':  $('#i_fopa_id').val(),
    'i_usua_id':  $('#i_usua_id').val(),
    'i_fapa_fecha_creacion':  $('#i_fapa_fecha_creacion').val()
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
$('#result').load('fapa_facturas_pagos_modificar.php?id=' + $('#h2_id').val()
,
{
     'm_fapa_id':  $('#m_fapa_id').val(),
     'm_fact_id':  $('#m_fact_id').val(),
     'm_fapa_monto':  $('#m_fapa_monto').val(),
     'm_fapa_fecha':  $('#m_fapa_fecha').val(),
     'm_fopa_id':  $('#m_fopa_id').val(),
     'm_usua_id':  $('#m_usua_id').val(),
     'm_fapa_fecha_creacion':  $('#m_fapa_fecha_creacion').val()
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
   $('#result').load('fapa_facturas_pagos_borrar.php?id=' + id
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
$.get('fapa_facturas_pagos_datos.php?id=' + id, function(data){
     var resp=data;
     r_array = resp.split('||');
     //alert(r_array[0]);
     $('#m_fact_id').val(r_array[1]);
     $('#m_fapa_monto').val(r_array[2]);
     $('#m_fapa_fecha').val(r_array[3]);
     $('#m_fopa_id').val(r_array[4]);
     $('#m_usua_id').val(r_array[5]);
     $('#m_fapa_fecha_creacion').val(r_array[6]);
     });
}
function mostrar() {
$('#datos_mostrar').load('fapa_facturas_pagos_mostrar.php?nochk=1222'
	+ "&desde=" + $('#desde').val()
	+ "&hasta=" + $('#hasta').val()
	+ "&vendedor=" + $('#f_vendedor').val()
	+ "&cliente=" + $('#f_cliente').val()
	);
}

function imprimir_factura(id)
{
	$("#result").load("exportar_pdf_v6.php?contenido=documento_factura.php&id=" + id
					,
					function(data)
					{
						//alert(data);
						//abro el documento
						window.open('facturas/facturas_' + id + '.pdf');
					});
}

function exportar()
{
$("#datos_mostrar").table2excel({
					exclude: ".noExl",
					name: "Excel Document Name",
					filename: "facturacion",
					exclude_img: true,
					exclude_links: true,
					exclude_inputs: true
				});
}
</script>
<div id='separador'>
<table width='' class=filtros>
<tr>
<?php echo entrada('fecha_mysql', 'Desde', 'desde') ?>
<?php echo entrada('fecha_mysql', 'Hasta', 'hasta') ?>
<?php echo catalogo('usuarios', 'Vendedor', 'usua_nombre', 'f_vendedor', 'usua_id', 'usua_nombre', 0,1,150)?>
<?php echo catalogo('clientes', 'Cliente', 'clie_nombre', 'f_cliente', 'clie_id', 'clie_nombre', 0,1,150)?>
</tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
<td><a href="javascript:exportar()"><img src="imagenes/excel.png" style="width:20px;height:20px"></a></td>

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
<td class='etiquetas'>fact_id:</td>
<td><input type='text' id=i_fact_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Monto:</td>
<td><input type='text' id=i_fapa_monto size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Fecha:</td>
<td><input type='text' id=i_fapa_fecha size=40 class='entradas'></td>
</tr>
<tr>
<?php echo catalogo('forma_pago', 'Forma de pago', 'fopa_nombre', 'i_fopa_id', 'fopa_id', 'fopa_nombre', '0', '0', '');?>
</tr>
<tr>
<td class='etiquetas'>usua_id:</td>
<td><input type='text' id=i_usua_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Fecha de registro:</td>
<td><input type='text' id=i_fapa_fecha_creacion size=40 class='entradas'></td>
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
<td class='etiquetas'>fact_id:</td>
<td><input type='text' id=m_fact_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Monto:</td>
<td><input type='text' id=m_fapa_monto size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Fecha:</td>
<td><input type='text' id=m_fapa_fecha size=40 class='entradas'></td>
</tr>
<tr>
<?php echo catalogo('forma_pago', 'Forma de pago', 'fopa_nombre', 'm_fopa_id', 'fopa_id', 'fopa_nombre', '0', '0', '');?>
</tr>
<tr>
<td class='etiquetas'>usua_id:</td>
<td><input type='text' id=m_usua_id size=40 class='entradas'></td>
</tr>
<tr>
<td class='etiquetas'>Fecha de registro:</td>
<td><input type='text' id=m_fapa_fecha_creacion size=40 class='entradas'></td>
</tr>
<tr>
<td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
</tr>
</table>
</div>
<a href='javascript:void(0);' id='close2'>close</a>
</div>

<div id=result></div>

