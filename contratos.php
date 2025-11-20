<?php
$sql ="select * from contratos_clases order by cocl_nombre";
$rs_c = mysql_query($sql);
$num_c = mysql_num_rows($rs_c);
$i=0;

?> 
<script src="ckeditor4/ckeditor.jss"></script>
<script>
$(function () { 
        $("#i_entrega").datepicker({ dateFormat: 'yymmdd' });

		CKEDITOR.editorConfig = function(config) {
		config.allowedContent = true;
		// o si quieres granularidad:
		// config.extraAllowedContent = 'svg[*]; text[*]; tspan[*];';
		};

		//$('#i_descripcion').htmlarea();
		//$('#m_descripcion').htmlarea();
	/*	
		CKEDITOR.config.extraPlugins = 'justify';
		
		CKEDITOR.editorConfig = function( config ) {
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];
};
*/
		/*
		CKEDITOR.config.toolbar = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
		'/',
		{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'others', items: [ '-' ] },
		{ name: 'about', items: [ 'About' ] }
		];
		*/
		
		$( '#i_descripcion' ).ckeditor();
		$( '#m_descripcion' ).ckeditor();
		
		//CKEDITOR.replace( 'i_descripcion' );
		//CKEDITOR.replace( 'm_descripcion' );
		
		//CKEDITOR.replace( 'i_descripcion' );
		
    });

$(function () {
        mostrar();
    });

function validar_campos()
{
if($("#i_nombre").val()=='') return false;
return true;
}	

$(function () {
$('#btn_crear').click(
		function(){
		if(validar_campos()==true)
		{
		///////////////
		
		var formURL = "contratos_crear.php";
			
			var formData = $('#fcrear').serialize();
			//alert(formData);
			$.ajax({
			type: "POST",
			url: formURL,
			data: formData, // serializes the form's elements.
			//contentType: false,
			//processData: false,
			success: function(data)
			{
				$('#modal').hide('slow');
				$('#overlay').hide();
				mostrar();
				
				$('#i_nombre').val('');
				$('#i_descripcion').val('');
			}
			});

			return false; 
		}
		else
		{
		alert('Todos los campos son obligatorios!');
		}
	});
});

function htmlEncode(value){
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
}

function borrar(id)
{
var agree=confirm("�Est� seguro?");
if (agree)
	{
		$('#result').load("contratos_borrar.php?id=" + id
		,
		function(){
		mostrar();
		}
		);
	}
}

			
$(function () {
$('#btn_modificar').click(
		function(){
			
		var formURL = "contratos_modificar.php";
			
			var formData = $('#fmodificar').serialize();
			
			$.ajax({
			type: "POST",
			url: formURL,
			data: formData, // serializes the form's elements.
			//contentType: false,
			//processData: false,
			success: function(data)
			{
			$('#modal2').hide('slow');
			$('#overlay2').hide();
			mostrar();
			//alert(data); // show response from the php script.
			}
			});

			return false; // avoid to execute the actual submit of the form. 	
			
			

	});
});

function editar(id)
{
	$('#modal2').show();
	$('#overlay2').show();

	$('#h_aid').val(id);
	
	$.get("datos_contratos.php?pid=" + id, function(data){
	var resp=data;
	r_array = resp.split('||');
	//alert(r_array[0]);
	$('#m_nombre').val(r_array[0]);
	$("#m_descripcion").val(r_array[1]);
	$("#m_tipo").val(r_array[2]);
	$("#m_proyecto").val(r_array[3]);
	$("#m_clase").val(r_array[4]);
	$("#m_version").val(r_array[5]);
		
	$('#sobretodo').hide();
	$('#procesando').hide();

	$('#modal2').center();
	
	});
}

$(function () {
	$('#b_mostrar').click(function() {
		mostrar();
	});
});

function mostrar()
{
$("#programas").load("contratos_mostrar.php?letra=" + QueryString("letra") 
		+ "&nombre=" + $('#f_nombre').val()
		+ "&cocl_id=" + $('#cocl_id').val()
		);
}

function clonar(id)
{

$('#modal3').show();
$('#overlay3').show();

$('#h3_id').val(id);

}

$(function () {
	$('#btn_clonar').click(function() {
	
		$("#result").load("contratos_clonar.php?id=" + $('#h3_id').val() 
		+ "&nombre=" + encodeURI($('#i3_nombre').val())
		+ "&nombre_completo=" + encodeURI($('#i3_nombre_completo').val())
		,
		function()
			{
			$('#modal3').hide('slow');
			$('#overlay3').hide('slow');
			
			mostrar();
			}
		);
	});
});
</script>
<div id="separador">
<table class=filtros>
	<tr>
	<td class="etiquetas">Nombre:</td>
	<td class="etiquetas"><input type=text id=f_nombre></td>
	<?php echo catalogo('contratos_clases', 'Tipo', 'cocl_nombre', 'cocl_id', 'cocl_id', 'cocl_nombre', '0', '1', '100');?>
	<td class="etiquetas"><div id="b_mostrar"><a href="#" class=botones>Mostrar</a></div></td>
	<td><div id="dmodal" style="text-align:right"><a href="#" class=botones>Nueva Plantilla</a></div></td>
	</tr>
</table>
</div>
<div id="columna6">
	<div id="programas"></div>
</div>
<!--MODAL-->
<div id='overlay'></div>
<div id='modal'>
    <div id='content'>
	<form name=f_crear id=fcrear method=post action="contratos_crear.php">
	<table>
	<tr>
	<td class="etiquetas">Clase:</td>
	<td><select id=i_clase name=i_clase>
	<?php
	$i=0;
	while ($i<$num_c)
	{
	?>
	<option value="<?php echo mysql_result($rs_c,$i,'cocl_id');?>"><?php echo mysql_result($rs_c,$i,'cocl_nombre');?></option>
	<?php
	$i++;
	}
	?>
	</select>
	
	</td>
	</tr>
	<tr>
	<td class="etiquetas">Nombre:</td>
	<td><input type="text" id=i_nombre  name="i_nombre" size=40 class="entradas"></td>
	</tr>
	<tr>
	<td class="etiquetas">Versi&oacute;n:</td>
	<td><input type="text" id=i_version  name="i_version" size=40 class="entradas"></td>
	</tr>
	<tr>
	<tr>
	<td class="etiquetas">Machote:</td>
	<td><textarea id=i_descripcion name="i_descripcion" style='width:400px;height:300px'></textarea></td>
	</tr>
	<td colspan=2><input type=button id="btn_crear" value="Crear" class="botones" style="float:right !important"></td>
	</tr>
	</table>
	</form>
	</div>
    <a href='#' id='close'>close</a>
</div>

<div id='overlay2'></div>
<div id='modal2'>
    <div id='content2'>
	<form name="f_modificar" id="fmodificar" method=post action="contratos_modificar.php">
	<input type="hidden" id="h_aid" name="h_aid">
	<table>
	<tr>
	<td class="etiquetas">Clase:</td>
	<td><select id=m_clase name=m_clase>
	<?php
	$i=0;
	while ($i<$num_c)
	{
	?>
	<option value="<?php echo mysql_result($rs_c,$i,'cocl_id');?>"><?php echo mysql_result($rs_c,$i,'cocl_nombre');?></option>
	<?php
	$i++;
	}
	?>
	</select>
	</td>
	</tr>
	<tr>
	<td class="etiquetas">Nombre:</td>
	<td><input type="text" id=m_nombre name="m_nombre"  size=40 class="entradas" readonly></td>
	</tr>
	<tr>
	<td class="etiquetas">Versi&oacute;n:</td>
	<td><input type="text" id=m_version  name="m_version" size=40 class="entradas"></td>
	</tr>
	<tr>
	<td class="etiquetas">Descripci&oacute;n:</td>
	<td><textarea id=m_descripcion name="m_descripcion" style='width:400px;height:300px'></textarea></td>
	</tr>
	<td colspan=2><input type=button id="btn_modificar" value="Modificar" class="botones" style="float:right !important"></td>
	</tr>
	</table>
	</form>
	</div>
    <a href='#' id='close2'>close</a>
</div>

<div id=result></div>