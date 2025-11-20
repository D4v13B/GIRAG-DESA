<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.js"></script>
<script>

function mostrar() {
	var desde = $('#desde').val();
	var hasta = $('#hasta').val();
	var aerolinea = $.trim($('#f_aerolinea').val()); 
	var errores = [];

	// 1. Validar Fechas (obligatorias y no '0000-00-00')
	if (!desde || desde === '0000-00-00') {
		errores.push("Debe seleccionar una fecha de inicio (Desde).");
	}
	if (!hasta || hasta === '0000-00-00') {
		errores.push("Debe seleccionar una fecha de fin (Hasta).");
	}

	// 2. Validar Aerolínea (obligatoria)
	// Usamos !aerolinea (que captura la cadena vacía '') y la comprobación explícita para '0'
	if (!aerolinea || aerolinea === '0') {
		errores.push("Debe seleccionar una Aerolínea.");
	}

	// Si hay errores, detiene la ejecución
	if (errores.length > 0) {
		// Muestra los errores en un alert
		alert("Atención, debe corregir lo siguiente:\n\n" + errores.join('\n'));

		// Muestra errores en el div
		$('#result').html('<p style="color:red; font-weight:bold;">' + errores.join('<br>') + '</p>');
		return; // Detiene la ejecución de la función
	}

	// Limpia el mensaje de error anterior
	$('#result').empty(); 

	// Si todo es válido, carga los datos
	$('#datos_mostrar').load('reporte_ait_clientes_mostrar.php?nochk=1222'
		+ "&desde=" + desde
		+ "&hasta=" + hasta
		+ "&aerolinea=" + aerolinea
	);

    // Mostrar el botón de PDF
    $('#b_pdf').show();
}

function imprimir_pdf() {
    var dataContainer = $('#datos_mostrar');
    
    // 1. Verificar si hay datos
    var dataRows = dataContainer.find('.nicetable tr').length; 
    
    if (dataRows <= 2) { 
        alert('Alerta: No hay datos disponibles en el reporte para generar el PDF. Por favor, realice la búsqueda primero.');
        return;
    }

    // console.log("Generando PDF a partir de la data cargada en datos_mostrar...");

    // 2. Obtener la tabla original
    var originalTable = dataContainer.find('.nicetable');
    
    if (originalTable.length === 0) {
        alert('No se encontró la tabla para generar el PDF.');
        return;
    }

    // 3. Obtener información para el nombre del archivo
    var aerolineaNombre = $('#f_aerolinea option:selected').text().trim();
    var desde = $('#desde').val();
    var hasta = $('#hasta').val();
    
    // Limpiar nombre de aerolínea para usar en nombre de archivo
    aerolineaNombre = aerolineaNombre.replace(/[^a-zA-Z0-9]/g, '_');
    
    // 4. Ocultar temporalmente la columna PDF
    var pdfColumnIndex = 8;
    var hiddenCells = [];
    
    originalTable.find('.tabla_datos_titulo').eq(pdfColumnIndex - 1).each(function() {
        $(this).hide();
        hiddenCells.push($(this));
    });
    
    originalTable.find('tr').each(function() {
        $(this).find('td').eq(pdfColumnIndex - 1).each(function() {
            $(this).hide();
            hiddenCells.push($(this));
        });
    });

    // 5. Crear un contenedor temporal con estilos mejorados
    var tempContainer = $('<div></div>').css({
        'position': 'absolute',
        'left': '-9999px',
        'background': 'white',
        'padding': '20px',
        'width': '1200px'
    });
    
    // Agregar título
    var titulo = $('<div></div>').css({
        'text-align': 'center',
        'font-size': '24px',
        'font-weight': 'bold',
        'margin-bottom': '20px',
        'color': '#333'
    }).html('Reporte AIT de ' + aerolineaNombre + '<br><span style="font-size:16px;">Aerolínea: ' + 
            $('#f_aerolinea option:selected').text() + 
            ' | Período: ' + desde + ' al ' + hasta + '</span>');
    
    tempContainer.append(titulo);
    
    // Clonar y agregar la tabla
    var tableClone = originalTable.clone();
    tempContainer.append(tableClone);
    
    $('body').append(tempContainer);

    // 6. Capturar usando html2canvas
    html2canvas(tempContainer[0], { 
        scale: 2,
        useCORS: true,
        allowTaint: true,
        logging: false,
        backgroundColor: '#ffffff',
        width: 1200,
        windowWidth: 1200
    }).then(function(canvas) {
        
        // Restaurar visibilidad
        hiddenCells.forEach(function(cell) {
            cell.show();
        });
        
        // Remover contenedor temporal
        tempContainer.remove();
        
        // 7. Crear el nombre del archivo
        var nombreArchivo = 'AIT_' + aerolineaNombre + '_' + desde + '_' + hasta + '.pdf';
        
        // 8. Usar jsPDF (con window.jspdf)
        var { jsPDF } = window.jspdf;
        var pdf = new jsPDF('l', 'mm', 'a4');
        
        var pdfWidth = pdf.internal.pageSize.getWidth();
        var pdfHeight = pdf.internal.pageSize.getHeight();
        
        var imgWidth = pdfWidth - 20;
        var imgHeight = canvas.height * imgWidth / canvas.width;
        
        var position = 10;
        var heightLeft = imgHeight;
        
        // Agregar imagen
        var imgData = canvas.toDataURL('image/png');
        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
        heightLeft -= (pdfHeight - position);
        
        // Páginas adicionales si es necesario
        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pdfHeight;
        }
        
        // 9. Descargar
        pdf.save(nombreArchivo);

    }).catch(function(error) {
        // Restaurar en caso de error
        hiddenCells.forEach(function(cell) {
            cell.show();
        });
        tempContainer.remove();
        
        console.error("Error al generar el PDF:", error);
        alert("Ocurrió un error al intentar generar el PDF: " + error.message);
    });
}

</script>
<div id='separador'>
<table width='' class=filtros>
<tr>
<?php echo entrada('fecha_mysql', 'Desde', 'desde') ?>
<?php echo entrada('fecha_mysql', 'Hasta', 'hasta') ?>
<?php echo catalogo('lineas_aereas', 'Aerolinea', 'liae_nombre', 'f_aerolinea', 'liae_id', 'liae_nombre', 0,1,150)?>
</tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td class='tabla_datos'><div id='b_mostrar'><a href='javascript:mostrar()' class=botones>Mostrar</a></div></td>
<td id='b_pdf' class=tabla_datos style="text-align:center; display:none;">
    <div>
        <a href="javascript:imprimir_pdf()" class=botones style="text-decoration: none;">Imprimir PDF</a>
    </div>
</td>
</tr>
</table>
</div>
<div id='columna6'>
<div id='datos_mostrar'></div>
</div>

<div id=result></div>

