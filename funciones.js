function QueryString(key) {
  //Get the full querystring
  fullQs = window.location.search.substring(1);
  //Break it down into an array of name-value pairs
  qsParamsArray = fullQs.split("&");
  //Loop through each name-value pair and
  //return value in there is a match for the given key
  for (i = 0; i < qsParamsArray.length; i++) {
    strKey = qsParamsArray[i].split("=");
    if (strKey[0] == key) {
      return strKey[1];
    }
  }
}

$(document).ajaxStart(function () {
//   $.get("sesion_activa.php", function (data) {
//     if (data == "") {
//       alert("Su sesi\u00F3n ha expirado!!!");
//       window.location.replace("login.php");
//     } else {
//       //alert(data);
//     }
//   });

  $("#sobretodo").show();
  $("#procesando").show();
  $("#procesando").center();
});
$(document).ajaxStop(function () {
  $("#sobretodo").hide();
  $("#procesando").hide();
});

/**
 * Genera un PDF a partir de una cadena de texto HTML y lo descarga.
 *
 * @param {string} htmlContent - La cadena de texto que contiene el HTML (div, tabla, etc.) a convertir.
 * @param {string} fileName - El nombre deseado para el archivo PDF (ej: 'factura_123.pdf').
 * @param {number} [customWidth=800] - Ancho de referencia para la captura (en px). Esto afecta la escala.
 */
function generarPDFOnline(htmlContent, fileName, customWidth = 800) {
    const nombreArchivo = fileName || 'documento.pdf';

    // 1. Crear un contenedor temporal oculto para que html2canvas pueda capturarlo
    const tempContainer = document.createElement('div');
    tempContainer.id = 'temp-pdf-container-' + new Date().getTime(); // ID único
    
    // Estilos para ocultar y asegurar el ancho de captura
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.style.background = 'white';
    tempContainer.style.width = customWidth + 'px';
    tempContainer.innerHTML = htmlContent;

    document.body.appendChild(tempContainer);

    // 2. Configuración y captura con html2canvas
    html2canvas(tempContainer, {
        scale: 2, // Aumenta la resolución de captura
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff',
        width: customWidth,
        windowWidth: customWidth
    }).then(function(canvas) {
        
        // 3. Inicializar jsPDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4'); 
        
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        
        // Calcular dimensiones para encajar la imagen en el PDF
        const imgWidth = pdfWidth - 20; // 10mm de margen a cada lado
        const imgHeight = canvas.height * imgWidth / canvas.width;
        
        let position = 10; // Margen superior
        let heightLeft = imgHeight;
        
        const imgData = canvas.toDataURL('image/png');
        
        // 4. Añadir imagen al PDF y manejar el paginado
        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
        heightLeft -= (pdfHeight - position);
        
        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pdfHeight;
        }

        // 5. Descargar
        pdf.save(nombreArchivo);

    }).finally(function() {
        // 6. Eliminar el contenedor temporal del DOM
        if (tempContainer && tempContainer.parentNode) {
            tempContainer.parentNode.removeChild(tempContainer);
        }
    }).catch(function(error) {
        console.error("Error al generar el PDF:", error);
        alert("Ocurrió un error al intentar generar el PDF.");
        
        // Asegurar la limpieza en caso de error
        if (tempContainer && tempContainer.parentNode) {
            tempContainer.parentNode.removeChild(tempContainer);
        }
    });
}