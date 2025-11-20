<?php
//Tomar el id del caso
session_start();
$usua_id = $_SESSION['login_user'];
$caso_id = $_GET["caso"];
/*
* Este mide el avance total de las tareas del caso
**/
$usuariosArray = [];
$stmt = "SELECT 
AVG(catb_avance) AS promedio_avance
FROM casos_tareas a
INNER JOIN (
SELECT 
    cate_id,
    MAX(catb_id) AS ultimo_catb_id
FROM casos_tareas_bitacora
GROUP BY cate_id
) ultimos_avances ON a.cate_id = ultimos_avances.cate_id
INNER  JOIN casos_tareas_bitacora b ON ultimos_avances.cate_id = b.cate_id AND ultimos_avances.ultimo_catb_id = b.catb_id
WHERE a.caso_id = $caso_id";

$promedio_total = (int) mysql_fetch_assoc(mysql_query($stmt))["promedio_avance"];

//Pedir toda la info
$stmt = "SELECT caso_nota_cierre, caso_id, caso_descripcion, cati_nombre, inso_nombre, inpr_nombre, depa_nombre, caso_fecha_analisis, imec_nombre, imma_nombre, equi_nombre, caso_fecha, caso_nota, impe_nombre, caso_externo, caso_observaciones, caes_id, caso_beneficio, 
usua_id_aprobado, usua_id_aprobado2, usua_id_aprobado3,
usua_id_revisado, usua_id_revisado2, usua_id_revisado3,
usua_id_encargado_revision, usua_id_encargado_revision2, usua_id_encargado_revision3,
usua_id_encargado_aprobacion, usua_id_encargado_aprobacion2, usua_id_encargado_aprobacion3,
(SELECT usua_nombre FROM usuarios WHERE  usua_id=usua_id_revisado) revisado,
(SELECT usua_nombre FROM usuarios WHERE  usua_id=usua_id_aprobado) aprobado,
(SELECT usua_nombre FROM usuarios WHERE usua_id=usua_id_asignado) usua_asignado,
(SELECT depa_nombre FROM departamentos WHERE depa_id=depa_id_asignado) depa_asignado,
(SELECT caes_nombre FROM casos_estado WHERE caes_id=a.caes_id) caso_estado
FROM casos a, casos_tipos b, departamentos c, equipos d, impacto_economico e, impacto_medio_ambiente f, impacto_personas g, incidencia_procesos h, incidencia_seg_op i 
WHERE a.cati_id=b.cati_id
AND a.depa_id=c.depa_id 
AND a.equi_id=d.equi_id 
AND a.imec_id=e.imec_id 
AND a.imma_id=f.imma_id 
AND a.impe_id=g.impe_id 
AND a.inpr_id=h.inpr_id
AND a.inso_id=i.inso_id
AND a.caso_id = $caso_id";


$caso = mysql_query($stmt, $dbh);
$caso = mysql_fetch_assoc($caso);
$caso_id = $caso["caso_id"];


$stmt = "SELECT ct.cate_id, ct.cate_nombre, ct.cate_descripcion, ct.cate_fecha_cierre, cate_estado, dep.depa_nombre, us.usua_nombre FROM casos_tareas ct
INNER JOIN usuarios us ON ct.usua_id = us.usua_id
INNER JOIN departamentos dep ON ct.depa_id = dep.depa_id
WHERE ct.caso_id = '$caso_id'";
$casos_tareas = mysql_query($stmt);

$stmt = "SELECT * FROM casos_documentos WHERE caso_id = $caso_id";
$casos_documentos = mysql_query($stmt);

$stmt = "SELECT * FROM departamentos";
$depas = mysql_query($stmt, $dbh);

$stmt = "SELECT * FROM usuarios";
$users = mysql_query($stmt, $dbh);

// echo $usuario_tipo = $_SESSION['usti_id'];
?>

<main class="container container-fluid">
   <section class="content">

      <!-- Default box -->
      <div class="card">
         <div class="card-header">
            <a href="index.php?p=caso_casos" class="btn" id="back">
               <i class="fa-solid fa-chevron-left" style="color: #000000;"></i>
            </a>
            <div class="d-flex flex-column col-12 col-md-4">
               <h4>Programa de Gestión / FT-GAC-04</h4>
               <!-- EXPEDIENTE -->

               <?php
               $caso_id = $_GET["caso"];
               // Verificar si existe un expediente para el caso
               $res = mysql_query("SELECT cado_ref FROM casos_documentos WHERE caso_id = $caso_id AND cado_ref LIKE '%expediente%'");
               $expediente = $res ? mysql_fetch_assoc($res)["cado_ref"] ?? null : null;
               ?>
               <div class="d-flex justify-content-between">
                  <?php if (!$expediente): ?>
                     <span onclick="abrirModal()" class="btn btn-success mb-3 btn_armar_expediente">
                        <i class="fa-solid fa-folder-plus"></i> Armar expediente
                     </span>
                  <?php else: ?>
                     <span class="btn btn-success text-white mb-3">
                        <a href="img/casos_docs/<?php echo $expediente; ?>" target="_blank" class="text-white">
                           <i class="fa-solid fa-folder-open "></i> Ver expediente
                        </a>
                     </span>
                  <?php endif; ?>
               </div>
               <!-- Modal -->
               <div class="modal fade" id="modalArmarExpediente" tabindex="-1" role="dialog" aria-labelledby="modalArmarExpedienteLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg" role="document"> <!-- Usamos modal-lg para hacer el modal más ancho -->
                     <div class="modal-content">
                        <!-- Encabezado del modal -->
                        <div class="modal-header bg-light">
                           <h5 class="modal-title" id="modalArmarExpedienteLabel">
                              <i class="fa-solid fa-folder-open mr-2"></i>Armar Expediente
                           </h5>
                           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>

                        <!-- Cuerpo del modal -->
                        <div class="modal-body">
                           <!-- Contenedor para alertas -->
                           <div id="alertContainer"></div>

                           <!-- Contenedor de dos columnas -->
                           <div class="row">
                              <!-- Columna 1: Formulario de Expediente -->
                              <div class="col-md-6">
                                 <form id="formExpediente" class="needs-validation" novalidate>
                                    <!-- Campo Nombre de Portada -->
                                    <div class="form-group">
                                       <label for="titulo_portada">Nombre de la Portada</label>
                                       <textarea class="form-control" id="titulo_portada" name="caso_titulo_portada" rows="4" required placeholder="Ingrese el nombre de la portada"></textarea>
                                       <div class="invalid-feedback">Por favor ingrese un nombre para la portada</div>
                                    </div>



                                    <button type="button" class="btn btn-success btn_guadar_expediente" onclick="guardarDatosExpediente()" title="Portada del reporte">
                                       <i class="fa-solid fa-save mr-2"></i>Guardar Portada
                                    </button>
                                 </form>
                              </div>

                              <!-- Columna 2: Formulario de Actividades -->
                              <div class="col-md-6">
                                 <form id="formExpedienteActividades" class="needs-validation" novalidate>
                                    <!-- Campo Título de la Actividad -->
                                    <div class="form-group">
                                       <label for="titulo_actividad">Título de la Actividad</label>
                                       <input type="text" class="form-control" id="titulo_actividad" name="caso_titulo_actividad" required placeholder="Ingrese el título de la actividad">
                                       <div class="invalid-feedback">Por favor ingrese el título de la actividad</div>
                                    </div>

                                    <!-- Campo Descripción de la Actividad -->
                                    <div class="form-group">
                                       <label for="descripcion_actividad">Descripción de la Actividad</label>
                                       <textarea class="form-control" id="descripcion_actividad" name="caso_descripcion_actividad" rows="4" required placeholder="Ingrese la descripción de la actividad"></textarea>
                                       <div class="invalid-feedback">Por favor ingrese la descripción de la actividad</div>
                                    </div>

                                    <button type="button" class="btn btn-success btn_guadar_actividades" onclick="guardarDatosActividades()" title="Portada de Actividad">
                                       <i class="fa-solid fa-save mr-2"></i>Guardar Separador de Actividad
                                    </button>
                                 </form>
                              </div>
                           </div> <!-- Fin del row -->

                          <!-- SELECCION DE DOCUMENTOS PARA ARMAR PDF -->
<div class="form-group" style="margin-top: 5%;">
   <label><i class="fa-solid fa-file-alt mr-2"></i>Seleccione los Documentos a Incluir:</label>
   <div id="document-list" class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
      <p class="text-muted">Cargando documentos...</p>
   </div>
   <input type="hidden" id="selectionOrder" name="selectionOrder" value="">
</div>

<!-- NUEVA SECCIÓN PARA CONVERTIR DOCUMENTOS A PDF -->
<div class="form-group" style="margin-top: 3%;">
   <label><i class="fa-solid fa-file-pdf mr-2"></i>Documentos para Convertir a PDF:</label>
   <div id="convert-list" class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
      <p class="text-muted">Cargando documentos no PDF...</p>
   </div>
   <div class="text-right mt-2">
      <button id="convert-btn" type="button" class="btn btn-sm btn-primary">Convertir Documento</button>
   </div>
</div>

<!-- En el head de tu documento HTML -->
<script>
   // Definir la función en el ámbito global
   function cargarDocumentosExpediente() {
    const caso_id = <?php echo $caso_id; ?>;
    // Mostrar spinner mientras se cargan los documentos
    const docListContainer = $('#document-list');
    const convertListContainer = $('#convert-list');
    
    docListContainer.html('<p class="text-muted"><span class="spinner-border spinner-border-sm mr-2"></span> Cargando documentos...</p>');
    convertListContainer.html('<p class="text-muted"><span class="spinner-border spinner-border-sm mr-2"></span> Cargando documentos...</p>');
    
    // Enviar la solicitud AJAX
    $.ajax({
        url: 'ajax/guardar_datos_expediente.php',
        type: 'GET',
        data: {
            caso: caso_id
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                docListContainer.html(''); // Limpiar el contenedor
                convertListContainer.html(''); // Limpiar el contenedor de conversión
                
                let hayDocumentosNoPdf = false;

                if (data.documentos.length === 0 && data.separadores.length === 0 && (!data.tareas || data.tareas.length === 0)) {
                    docListContainer.html('<p class="text-muted">No hay documentos disponibles para este expediente.</p>');
                    convertListContainer.html('<p class="text-muted">No hay documentos disponibles para convertir.</p>');
                    return;
                }

                // Crear estructura de dos columnas para documentos PDF
                docListContainer.html(`
                    <div class="row">
                        <div class="col-md-7">
                            <h6>Documentos</h6>
                            <div id="documentos-container"></div>
                        </div>
                        <div class="col-md-5">
                            <h6>Separadores</h6>
                            <div id="separadores-container" class="small"></div>
                        </div>
                    </div>
                `);
                
                // Crear estructura para documentos no PDF (solo una columna)
                convertListContainer.html(`
                    <div id="convert-documentos-container"></div>
                `);

                const documentosContainer = $('#documentos-container');
                const separadoresContainer = $('#separadores-container');
                const convertDocumentosContainer = $('#convert-documentos-container');

                // Agregar documentos regulares (solo PDFs)
                data.documentos.forEach(doc => {
                    const isPdf = doc.cado_ref.toLowerCase().endsWith('.pdf');
                    
                    if (isPdf) {
                        // Agregar a la sección de documentos PDF
                        documentosContainer.append(`
                            <div class="form-check">
                                <input class="form-check-input doc-checkbox" type="checkbox" name="documentos_seleccionados[]"
                                    value="${doc.cado_id}" id="doc${doc.cado_id}" data-seq="0">
                                <label class="form-check-label" for="doc${doc.cado_id}">
                                    <span class="seq-number"></span>
                                    <a href="../img/casos_docs/${doc.cado_ref}" target="_blank">${doc.cado_ref}</a>
                                </label>
                            </div>
                        `);
                    } else {
                        // MODIFICADO: Ahora usamos radio button en lugar de checkbox
                        convertDocumentosContainer.append(`
                            <div class="form-check">
                                <input class="form-check-input convert-radio" type="radio" name="documento_a_convertir"
                                    value="doc_${doc.cado_id}" id="convert_doc${doc.cado_id}">
                                <label class="form-check-label" for="convert_doc${doc.cado_id}">
                                    <a href="../img/casos_docs/${doc.cado_ref}" target="_blank">${doc.cado_ref}</a>
                                </label>
                            </div>
                        `);
                        hayDocumentosNoPdf = true;
                    }
                });

                // Agregar documentos de tareas (solo PDFs)
                if (data.tareas && data.tareas.length > 0) {
                    data.tareas.forEach(tarea => {
                        if (tarea.tado_ref) {
                            const isPdf = tarea.tado_ref.toLowerCase().endsWith('.pdf');
                            
                            if (isPdf) {
                                // Agregar a la sección de documentos PDF
                                documentosContainer.append(`
                                    <div class="form-check">
                                        <input class="form-check-input doc-checkbox" type="checkbox" name="documentos_seleccionados[]"
                                            value="tado_${tarea.tado_id}" id="tado_${tarea.tado_id}" data-seq="0">
                                        <label class="form-check-label" for="tado_${tarea.tado_id}">
                                            <span class="seq-number"></span>
                                            <a href="../img/casos_docs/${tarea.tado_ref}" target="_blank">${tarea.tado_ref}</a>
                                        </label>
                                    </div>
                                `);
                            } else {
                                // MODIFICADO: Ahora usamos radio button en lugar de checkbox
                                convertDocumentosContainer.append(`
                                    <div class="form-check">
                                        <input class="form-check-input convert-radio" type="radio" name="documento_a_convertir"
                                            value="tado_${tarea.tado_id}" id="convert_tado_${tarea.tado_id}">
                                        <label class="form-check-label" for="convert_tado_${tarea.tado_id}">
                                            <a href="../img/casos_docs/${tarea.tado_ref}" target="_blank">${tarea.tado_ref}</a>
                                        </label>
                                    </div>
                                `);
                                hayDocumentosNoPdf = true;
                            }
                        }
                    });
                }

                // Agregar separadores (solo PDFs)
                if (data.separadores.length > 0) {
                    let haySeparadoresPdf = false;
                    
                    data.separadores.forEach(sep => {
                        const isPdf = sep.cads_ref.toLowerCase().endsWith('.pdf');
                        
                        if (isPdf) {
                            // Agregar a la sección de separadores PDF
                            separadoresContainer.append(`
                                <div class="form-check">
                                    <input class="form-check-input doc-checkbox" type="checkbox" name="separadores_seleccionados[]"
                                        value="${sep.cads_id}" id="sep${sep.cads_id}" data-seq="0">
                                    <label class="form-check-label" for="sep${sep.cads_id}">
                                        <span class="seq-number"></span>
                                        <a href="../img/casos_docs/${sep.cads_ref}" target="_blank" class="medium">${sep.cads_ref}</a>
                                    </label>
                                </div>
                            `);
                            haySeparadoresPdf = true;
                        }
                        // Los separadores no PDF simplemente no se muestran
                    });
                    
                    if (!haySeparadoresPdf) {
                        separadoresContainer.html('<p class="text-muted small">No hay separadores PDF disponibles</p>');
                    }
                } else {
                    separadoresContainer.html('<p class="text-muted small">No hay separadores disponibles</p>');
                }
                
                // Si no hay documentos no PDF para convertir
                if (!hayDocumentosNoPdf) {
                    convertListContainer.html('<p class="text-muted">No hay documentos para convertir a PDF.</p>');
                }

                attachCheckboxEvents(); // Vincular eventos a los checkboxes
                attachConvertEvents(); // Vincular eventos para conversión (ahora con radio buttons)
                
            } catch (e) {
                console.error('Error:', e);
                docListContainer.html('<p class="text-danger">Error al procesar la respuesta del servidor.</p>');
                convertListContainer.html('<p class="text-danger">Error al procesar la respuesta del servidor.</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            docListContainer.html('<p class="text-danger">Error de conexión con el servidor.</p>');
            convertListContainer.html('<p class="text-danger">Error de conexión con el servidor.</p>');
        }
    });
}

   // Función para vincular eventos a los checkboxes
   function attachCheckboxEvents() {
      const checkboxes = document.querySelectorAll('.doc-checkbox');
      let selectionCounter = 1;

      checkboxes.forEach(function(checkbox) {
         checkbox.addEventListener('change', function() {
            if (this.checked) {
               this.setAttribute('data-seq', selectionCounter);
               selectionCounter++;
            } else {
               this.setAttribute('data-seq', '0');
            }

            updateDisplayNumbers();
            updateSelectionOrder();
         });
      });
   }
   function attachConvertEvents() {
    // Eliminar eventos previos para evitar duplicados
    $('#convert-btn').off('click').on('click', function(e) {
        e.preventDefault(); // Prevenir comportamiento por defecto
        
        // Selector más específico
        const selectedRadio = $('#convert-list .convert-radio:checked');
        
        // Debug: Mostrar en consola qué está seleccionado
        console.log('Radio seleccionado:', selectedRadio.val(), selectedRadio.length);
        
        if (selectedRadio.length === 0) {
            alert('Por favor, seleccione un documento para convertir.');
            return;
        }

        const selectedValue = selectedRadio.val();
        let requestData = { 
            caso_id: <?php echo $caso_id; ?>,
            documento_id: selectedValue
        };

        // Mostrar loading
        $('#convert-list').html(`
            <div class="text-center py-2">
                <i class="fas fa-spinner fa-spin"></i> Convirtiendo documento...
            </div>
        `);
        $('#convert-btn').prop('disabled', true);

        $.ajax({
            url: 'ajax/convertir_a_pdf.php',
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(response) {
                let resultHtml = '';
                
                if (response.success) {
                    resultHtml = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <div class="mt-2">
                                <strong>Documento convertido:</strong> 
                                ${response.nombre_pdf || 'documento.pdf'}
                            </div>
                        </div>
                    `;
                    setTimeout(cargarDocumentosExpediente, 2000);
                } else {
                    resultHtml = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${response.message}
                        </div>
                    `;
                }
                
                $('#convert-list').html(resultHtml);
                $('#convert-btn').prop('disabled', false);
            },
            error: function(xhr) {
                $('#convert-list').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error en la conversión
                        <div class="small">${xhr.responseText || 'Error de servidor'}</div>
                    </div>
                `);
                $('#convert-btn').prop('disabled', false);
            }
        });
    });
}
   // Función para actualizar los números de secuencia
   function updateDisplayNumbers() {
      document.querySelectorAll('.seq-number').forEach(span => {
         span.textContent = '';
      });

      const selectedCheckboxes = Array.from(document.querySelectorAll('.doc-checkbox')).filter(cb => cb.checked);
      selectedCheckboxes.sort((a, b) => parseInt(a.getAttribute('data-seq')) - parseInt(b.getAttribute('data-seq')));

      selectedCheckboxes.forEach((checkbox, index) => {
         const numberSpan = checkbox.closest('.form-check').querySelector('.seq-number');
         numberSpan.textContent = (index + 1) + '. ';
      });
   }

   // Función para actualizar el orden de selección
   function updateSelectionOrder() {
      const selectedCheckboxes = Array.from(document.querySelectorAll('.doc-checkbox'))
         .filter(cb => cb.checked)
         .sort((a, b) => parseInt(a.getAttribute('data-seq')) - parseInt(b.getAttribute('data-seq')));

      const orderArray = selectedCheckboxes.map(cb => {
         if (cb.name.includes('separadores_seleccionados')) {
            return 'sep_' + cb.value; // Formato: sep_ + ID
         } else if (cb.value.startsWith('tado_')) {
            return cb.value; // Mantener el formato tado_ + ID
         } else {
            return 'doc_' + cb.value; // Formato: doc_ + ID
         }
      });

      document.getElementById('selectionOrder').value = orderArray.join(',');
   }

   // Llamar a la función al cargar la página
   $(document).ready(function() {
      cargarDocumentosExpediente();
   });
</script>
<!--  FIN DEL SELECTOR DE DOCUMENTOS -->
</div>




                        <!-- Pie del modal -->
                        <div class="modal-footer bg-light">
                           <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                           <button type="button" class="btn btn-success btn_generar_expediente" onclick="generarExpediente()" title="Expediente">
                              <i class="fa-solid fa-save mr-2"></i>Generar Expediente
                           </button>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Toast container -->
               <div id="toastContainer" class="position-fixed" style="z-index: 1070; bottom: 1rem; right: 1rem;"></div>

               <!-- Estilos CSS -->
               <style>
                  .modal-header .close {
                     padding: 1rem;
                     margin: -1rem -1rem -1rem auto;
                     font-size: 1.5rem;
                     opacity: .5;
                     cursor: pointer;
                  }

                  .modal-header .close:hover {
                     opacity: .75;
                  }

                  .toast {
                     background-color: rgba(255, 255, 255, .95);
                     border: none;
                     box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, .1);
                     max-width: 350px;
                  }

                  .toast-success {
                     background-color: #28a745 !important;
                     color: white;
                  }

                  .toast-error {
                     background-color: #dc3545 !important;
                     color: white;
                  }

                  .alert {
                     margin-bottom: 1rem;
                  }

                  /* Ajustes para Bootstrap 4.1 */
                  .mr-2 {
                     margin-right: .5rem !important;
                  }

                  .mb-3 {
                     margin-bottom: 1rem !important;
                  }

                  .bg-light {
                     background-color: #f8f9fa !important;
                  }
               </style>

               <!-- Scripts -->
               <script>
                  // Variable global para el modal
                  let modalInstance = null;

                  // Inicialización cuando el DOM está listo
                  $(document).ready(function() {
                     modalInstance = $('#modalArmarExpediente');

                     // Evento para limpiar el formulario cuando se cierre el modal
                     modalInstance.on('hidden.bs.modal', function() {
                        const form = document.getElementById('formExpediente');
                        form.reset();
                        form.classList.remove('was-validated');
                        $('#alertContainer').empty();
                     });
                  });

                  // Función para abrir el modal
                  function abrirModal() {
                     modalInstance.modal('show');
                  }

                  // Función para cerrar el modal
                  function cerrarModal() {
                     modalInstance.modal('hide');
                  }

                  // Función para mostrar toast
                  function mostrarToast(mensaje, tipo = 'success') {
                     const toastId = 'toast-' + Date.now();
                     const bgClass = tipo === 'success' ? 'toast-success' : 'toast-error';
                     const iconClass = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                     const toastHTML = `
        <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
            <div class="toast-body">
                <i class="fa-solid ${iconClass} mr-2"></i>
                ${mensaje}
                <button type="button" class="close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    `;

                     $('#toastContainer').append(toastHTML);
                     $(`#${toastId}`).toast('show');

                     // Remover el toast después de que se oculte
                     $(`#${toastId}`).on('hidden.bs.toast', function() {
                        $(this).remove();
                     });
                  }

                  // Función para mostrar error en el modal
                  function mostrarError(mensaje) {
                     $('#alertContainer').empty().append(`
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-circle mr-2"></i>
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);
                  }

                  // Función para guardar datos del expediente
                  function guardarDatosExpediente() {
                     const form = document.getElementById('formExpediente');

                     // Validación del formulario
                     if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                     }

                     // Obtener los valores
                     const titulo_portada = $('#titulo_portada').val();
                     const tipo_expediente = $('#tipo_expediente').val();
                     const caso_id = <?php echo $caso_id; ?>;

                     // Crear objeto FormData
                     const formData = new FormData();
                     formData.append('caso_titulo_portada', titulo_portada);
                     formData.append('cadt_id', tipo_expediente);
                     formData.append('caso_id', caso_id);

                     // Deshabilitar el botón de guardar y mostrar spinner
                     const btnGuardar = $('.btn_guadar_expediente');
                     btnGuardar.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm mr-2"></span>Guardando');

                     // Enviar datos usando AJAX
                     $.ajax({
                        url: 'ajax/guardar_datos_expediente.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                           try {
                              const data = JSON.parse(response);
                              if (data.success) {
                                 mostrarToast('Datos guardados correctamente', 'success');

                                 // Llamar a la función para cargar documentos después de guardar
                                 cargarDocumentosExpediente();

                                 setTimeout(() => {
                                    // Cerrar el modal o realizar otras acciones
                                 }, 1500);

                              } else {
                                 mostrarError(data.message || 'Error al guardar el expediente');
                              }
                           } catch (e) {
                              console.error('Error:', e);
                              mostrarError('Error al procesar la respuesta del servidor');
                           }
                        },
                        error: function(xhr, status, error) {
                           console.error('Error:', error);
                           mostrarError('Error de conexión con el servidor');
                        },
                        complete: function() {
                           const btnGuardar = $('.btn_guadar_expediente');
                           btnGuardar.prop('disabled', false).html('Guardar');

                           const form = document.getElementById('formExpediente'); // Reemplaza con el ID de tu formulario
                           if (form) {
                              form.reset();
                              form.classList.remove('was-validated');
                           }
                        }

                     });
                  }

                  function generarExpediente() {
                     const caso_id = <?php echo $caso_id; ?>;
                     const btnGenerar = $('.btn_generar_expediente');

                     // Obtener los documentos seleccionados en el orden correcto
                     const seleccionOrder = $('#selectionOrder').val();
                     if (!seleccionOrder) {
                        mostrarError('No ha seleccionado ningún documento');
                        return;
                     }

                     btnGenerar.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm mr-2"></span>Generando...');

                     $.ajax({
                        url: 'ajax/generar_expediente.php',
                        type: 'POST',
                        data: {
                           caso_id: caso_id,
                           documentos_seleccionados: seleccionOrder // Enviar el orden de los documentos seleccionados
                        },
                        success: function(response) {
                           try {
                              const responseData = JSON.parse(response);
                              if (responseData.success) {
                                 $('.btn_armar_expediente').replaceWith(`
                <span class="btn btn-success text-white mb-3">
                   <a href="${responseData.pdf_file}" target="_blank" class ="text-white">
                      <i class="fa-solid fa-folder-open text-white"></i> Ver expediente
                   </a>
                </span>
            `);
                                 // Restore the generate button and close modal
                                 btnGenerar.prop('disabled', false)
                                    .html('<i class="fa-solid fa-save mr-2"></i>Generar Expediente');
                                 $('#modalArmarExpediente').modal('hide');
                                 mostrarToast('Expediente generado correctamente', 'success');
                              } else {
                                 throw new Error(responseData.message || 'Error al generar el expediente');
                              }
                           } catch (error) {
                              mostrarError(error.message);
                              btnGenerar.prop('disabled', false)
                                 .html('<i class="fa-solid fa-save mr-2"></i>Generar Expediente');
                           }
                        },
                        error: function(xhr, status, error) {
                           console.error('Error:', error);
                           mostrarError('Error de conexión con el servidor');
                           btnGenerar.prop('disabled', false)
                              .html('<i class="fa-solid fa-save mr-2"></i>Generar Expediente');
                        }
                     });
                  }
                  // GUARDAR ACTIVIDADES
                  // Función para guardar datos de la actividad
                  function guardarDatosActividades() {
                     const form = document.getElementById('formExpedienteActividades');
                     // Validación del formulario
                     if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                     }
                     // Obtener los valores
                     const titulo_actividad = $('#titulo_actividad').val();
                     const descripcion_actividad = $('#descripcion_actividad').val();
                     const caso_id = <?php echo $caso_id; ?>;
                     // Crear objeto FormData
                     const formData = new FormData();
                     formData.append('caso_titulo_actividad', titulo_actividad);
                     formData.append('caso_descripcion_actividad', descripcion_actividad);
                     formData.append('caso_id', caso_id);
                     // Deshabilitar el botón de guardar y mostrar spinner
                     const btnGuardar = $('.btn_guadar_actividades');
                     btnGuardar.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm mr-2"></span>Guardando');
                     // Enviar datos usando AJAX
                     $.ajax({
                        url: 'ajax/guardar_datos_actividades.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                           try {
                              const data = JSON.parse(response);
                              if (data.success) {
                                 mostrarToast('Actividad guardada correctamente', 'success');

                                 // Llamar a la función para cargar documentos después de guardar
                                 cargarDocumentosExpediente();

                                 setTimeout(() => {
                                    // Cerrar el modal o realizar otras acciones
                                 }, 1500);

                              } else {
                                 mostrarError(data.message || 'Error al guardar la actividad');
                              }
                           } catch (e) {
                              console.error('Error:', e);
                              mostrarError('Error al procesar la respuesta del servidor');
                           }
                        },
                        error: function(xhr, status, error) {
                           console.error('Error:', error);
                           mostrarError('Error de conexión con el servidor');
                        },
                        complete: function() {
                           const btnGuardar = $('.btn_guadar_actividades');
                           btnGuardar.prop('disabled', false)
                              .html('<i class="fa-solid fa-save mr-2"></i>Guardar Actividad');
                           form.reset();
                           form.classList.remove('was-validated');

                        }
                     });
                  }
                  // Llamar la función al cargar la página
                  $(document).ready(function() {
                     cargarDocumentosExpediente();
                  });
               </script>
               <!-- FIN EXPEDIENTE -->

               <?php if ($promedio_total > 0): ?>
                  <h6 class="text-primary">Avance de solución general de tareas <b><?php echo $promedio_total ?>%</b></h6>
               <?php endif ?>

               <?php if ($caso["caes_id"] != 2) : ?>
                  <?php if ($promedio_total > 0 && $promedio_total < 100): ?>
                     <div class="alert alert-warning">El caso tiene tareas pendientes. Debe completar el 100% para cerrar.</div>
                  <?php else: ?>
                     <span onclick="cerrarCaso('cerrar')" class="btn btn-danger mb-3 btn_cerrar_caso">
                        <i class="fa-solid fa-xmark"></i> Cerrar el caso
                     </span>
                     <script>
                        $(function() {
                           $(".btn_cerrar_caso").hide();
                           <?php echo pantalla_roles("index.php?p=detalle-caso", $_SESSION["login_user"]) ?>
                        });
                     </script>
                  <?php endif ?>
               <?php endif ?>

               <?php
               $usua_id = $_SESSION['login_user'];
               $caso_id = $_GET["caso"];
               // Consulta para obtener los encargados de revisión y aprobación
               $revision_assignees = array_filter([
                  $caso["usua_id_encargado_revision"],
                  $caso["usua_id_encargado_revision2"],
                  $caso["usua_id_encargado_revision3"]
               ]);

               $approval_assignees = array_filter([
                  $caso["usua_id_encargado_aprobacion"],
                  $caso["usua_id_encargado_aprobacion2"],
                  $caso["usua_id_encargado_aprobacion3"]
               ]);

               // Verificar si el usuario actual ya aprobó este caso
               $usuario_ya_aprobo = ($usua_id == $caso['usua_id_aprobado'] || 
                                    $usua_id == $caso['usua_id_aprobado2'] || 
                                    $usua_id == $caso['usua_id_aprobado3']);

               // Verificar si el usuario actual ya revisó este caso                     
               $usuario_ya_reviso = ($usua_id == $caso['usua_id_revisado'] || 
                                    $usua_id == $caso['usua_id_revisado2'] || 
                                    $usua_id == $caso['usua_id_revisado3']);
               ?>


               <?php if (empty($caso["caso_fecha_analisis"])) : ?>
                  <button class="btn btn-secondary mb-3" data-toggle="modal" data-target="#modal-fecha-revision">
                     <i class="fa-solid fa-newspaper"></i> Fecha de Análisis
                  </button>
               <?php else : ?>
                  <span class="d-flex justify-content-between">
                     <a class="btn btn-warning mb-3 col-9" href="programa-gestion-pdf.php?caso=<?php echo $_GET["caso"] ?>" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>Programa de gestión
                     </a>
                     <button onclick="guardarProgramaGestion()" class="btn btn-warning mb-3 col-2 ocultable">
                        <i class="fa-solid fa-floppy-disk"></i>
                     </button>
                  </span>

                  <?php if (!empty($revision_assignees) || !empty($approval_assignees)) : ?>
                     <span class="d-flex justify-content-between">
                        <?php
                        // Botones de revisión para todos los revisores asignados
                        if (in_array($usua_id, $revision_assignees) && !$usuario_ya_reviso): ?>
                           <button class="btn btn-success col-5 btn-sm mb-3" style="margin: 0 10px 0 0" onclick="cerrarCaso('revisado')">
                              <i class="fa-solid fa-file-signature"></i> Revisar programa de gestión
                           </button>
                        <?php elseif (in_array($usua_id, $revision_assignees) && $usuario_ya_reviso): ?>
                           <button class="btn btn-secondary col-5 btn-sm mb-3" style="margin: 0 10px 0 0" disabled>
                              <i class="fa-solid fa-check-circle"></i> Ya Revisado
                           </button>
                        <?php endif; ?>

                        <?php
                        // Botones de aprobación para todos los aprobadores asignados
                        if (in_array($usua_id, $approval_assignees) && !$usuario_ya_aprobo): ?>
                           <button class="btn btn-primary col-5 btn-sm mb-3" style="margin: 0 0 0 10px;" onclick="cerrarCaso('aprobado')">
                              <i class="fa-solid fa-file-signature"></i> Aprobar programa de gestión
                           </button>
                        <?php elseif (in_array($usua_id, $approval_assignees) && $usuario_ya_aprobo): ?>
                           <button class="btn btn-secondary col-5 btn-sm mb-3" style="margin: 0 0 0 10px;" disabled>
                              <i class="fa-solid fa-check-circle"></i> Ya Aprobado
                           </button>
                        <?php endif; ?>
                     </span>
                  <?php endif; ?>


                  <script>
                     function guardarProgramaGestion() {

                        $.ajax({
                           url: "programa-gestion-pdf.php",
                           method: "GET",
                           data: {
                              "caso": <?php echo $_GET["caso"] ?>,
                              "tipo": "guardado"
                           },
                           success: res => {
                              alert("Programa de gestión creado correctamente")
                              getDocCaso()
                           }

                        })
                     }
                  </script>
               <?php endif ?>



               <?php
               $caso_id = $_GET["caso"];
               // Verificar si ya existe un archivo asociado al caso que coincida con el patrón
               $res = mysql_query("SELECT cado_ref FROM casos_documentos WHERE caso_id = $caso_id AND cado_ref LIKE '%-reporte-incidente-caso-%'");
               $archivo = $res ? mysql_fetch_assoc($res)["cado_ref"] ?? null : null;
               ?>

               <div class="d-flex justify-content-between reporte-incidente-container">
                  <?php if (!$archivo): ?>
                     <!-- Botón para generar el reporte de incidente -->
                     <a class="btn btn-primary text-white mb-3" href="reporte-incidente-pdf.php?caso=<?php echo $caso_id ?>" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i> Reporte de Incidente
                     </a>
                     <!-- Botón para guardar el reporte -->
                     <button onclick="guardarReporteIncidente(this)" class="btn btn-warning mb-3 col-2" title="Guardar Reporte">
                        <i class="fa-solid fa-floppy-disk"></i>
                     </button>
                  <?php else: ?>
                     <!-- Botón para ver el archivo guardado -->
                     <a class="boton_reporte btn btn-success text-white mb-3" href="img/casos_docs/<?php echo $archivo; ?>" target="_blank">
                        <i class="fa-solid fa-eye"></i> Ver Reporte de Incidentes
                     </a>
                  <?php endif; ?>
               </div>
            </div>
            <script>
               function guardarReporteIncidente(btnElement) {
                  $.ajax({
                     url: "reporte-incidente-pdf.php",
                     method: "GET",
                     data: {
                        "caso": <?php echo $caso_id; ?>,
                        "tipo": "guardado"
                     },
                     dataType: 'json',
                     success: function(response) {
                        alert("Reporte de Incidente creado correctamente");

                        // Obtener el contenedor específico usando el botón que se clickeó
                        const $container = $(btnElement).closest('.reporte-incidente-container');

                        // Limpiar solo los botones dentro de este contenedor específico
                        $container.empty();

                        // Añadir el botón "Ver Reporte de Incidentes" usando el nombre del archivo de la respuesta
                        const nuevoBoton_reporte = `
            <a class="boton_reporte btn btn-success text-white mb-3" href="img/casos_docs/${response.nombre}" target="_blank">
               <i class="fa-solid fa-eye"></i> Ver Reporte de Incidentes
            </a>`;
                        $container.append(nuevoBoton_reporte);
                     },
                     error: function(xhr, status, error) {
                        alert("Error al guardar el reporte: " + error);
                        console.log("Error detallado:", xhr.responseText);
                     }
                  });
               }
            </script>



            <div class="card-body">
               <div class="row">
                  <div class="col-12 col-md-12 col-lg-8 order-2 order-md-1">
                     <div class="row">
                        <div class="col-12 col-sm-4">
                           <div class="info-box bg-light">
                              <div class="info-box-content">
                                 <span class="info-box-text text-center text-muted">Estado</span>
                                 <span class="info-box-number text-center text-muted mb-0"><?php echo strtoupper($caso["caso_estado"]) ?></span>
                              </div>
                           </div>
                        </div>
                        <div class="col-12 col-sm-4">
                           <div class="info-box bg-light">
                              <div class="info-box-content">
                                 <span class="info-box-text text-center text-muted">Revisado por</span>
                                 <span class="info-box-number text-center text-muted mb-0"><?php echo strtoupper($caso["revisado"]) ?></span>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-12">
                           <span class="d-flex justify-content-between">
                              <span class="mb-3">
                                 <h6 style="font-size: 19px" class="card-title">Asociado a: Reporte # <?php echo strtoupper($caso["caso_id"]) ?>, <?php echo strtoupper($caso["caso_descripcion"]) ?></h6>
                                 <span class="text-success d-block">Fecha de incidencia-><?php echo DateTime::createFromFormat("Y-m-d H:i:s", $caso["caso_fecha"])->format("d/m/Y H:i") ?></span>
                                 <span class="text-primary d-block">Fecha de análisis->
                                    <span id="fecha_revision_span"><?php echo !empty($caso["caso_fecha_analisis"]) ? DateTime::createFromFormat("Y-m-d", $caso["caso_fecha_analisis"])->format("d/m/Y") : ""  ?></span>
                                 </span>
                              </span>
                              <!-- Button trigger modal -->
                              <button type="button" class="btn btn-primary ocultable administrador" data-toggle="modal" data-target="#form_nueva_tarea" onclick="editarTarea('')">
                                 <i class="fa-solid fa-plus"></i> Nueva tarea
                              </button>

                              <div class="modal fade" id="form_nueva_tarea">
                                 <div class="modal-dialog">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <h4 class="modal-title">Descripción de tarea</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                             <span aria-hidden="true">&times;</span>
                                          </button>
                                       </div>
                                       <div class="modal-body">
                                          <form id="form_task" enctype="multipart/form-data">
                                             <input type="hidden" value="<?php echo $_GET["caso"] ?>" name="caso_id" id="caso_id">
                                             <input type="hidden" name="cate_id" id="cate_id">
                                             <div class="form-group">
                                                <label for="nombre" class="col-form-label">Tarea</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
                                             </div>
                                             <!-- Usuario asignado 1 -->
                                             <div class="form-group">
                                                <label for="usuario1">Asignar usuario</label>
                                                <select class="form-control" id="usuario1" name="usuario" required>
                                                   <option value="">Seleccionar usuario</option>
                                                   <?php while ($fila = mysql_fetch_assoc($users)) : ?>
                                                      <option value="<?php echo htmlspecialchars($fila["usua_id"]) ?>">
                                                         <?php echo htmlspecialchars($fila["usua_nombre"]) ?>
                                                      </option>
                                                   <?php
                                                      array_push($usuariosArray, [
                                                         "usua_id" => $fila["usua_id"],
                                                         "usua_nombre" => $fila["usua_nombre"]
                                                      ]);
                                                   endwhile ?>
                                                </select>
                                             </div>

                                             <!-- Usuario asignado 2 -->
                                             <div class="form-group">
                                                <label for="usuario2">Asignar usuario #2</label>
                                                <select class="form-control" id="usuario2" name="usua_asignado_2">
                                                   <option value="">Seleccionar usuario</option>
                                                   <?php foreach ($usuariosArray as $fila) : ?>
                                                      <option value="<?php echo htmlspecialchars($fila["usua_id"]) ?>">
                                                         <?php echo htmlspecialchars($fila["usua_nombre"]) ?>
                                                      </option>
                                                   <?php endforeach ?>
                                                </select>
                                             </div>

                                             <!-- Usuario asignado 3 -->
                                             <div class="form-group">
                                                <label for="usuario3">Asignar usuario #3</label>
                                                <select class="form-control" id="usuario3" name="usua_asignado_3">
                                                   <option value="">Seleccionar usuario</option>
                                                   <?php foreach ($usuariosArray as $fila) : ?>
                                                      <option value="<?php echo htmlspecialchars($fila["usua_id"]) ?>">
                                                         <?php echo htmlspecialchars($fila["usua_nombre"]) ?>
                                                      </option>
                                                   <?php endforeach ?>
                                                </select>
                                             </div>
                                             <div class="form-group">
                                                <label for="fecha_inicio" class="col-form-label">Fecha de inicio</label>
                                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" placeholder="Fecha de inicio">
                                             </div>

                                             <div class="form-group">
                                                <label for="fecha_fin" class="col-form-label">Fecha de cierre</label>
                                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" placeholder="Fecha de fin">
                                             </div>
                                             <div class="form-group">
                                                <label for="descripcion" class="col-form-label">Descripción</label>
                                                <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion de la actividad"></textarea>
                                             </div>
                                             <div class="form-group">
                                                <label for="equipos" class="col-form-label">Recursos</label>
                                                <textarea class="form-control" id="recursos" name="recursos" placeholder="Recursos"></textarea>
                                             </div>
                                             <div class="form-group">
                                                <label for="observaciones" class="col-form-label">Observaciones</label>
                                                <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                                             </div>
                                             <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="archivos" placeholder="Buscar documentos" name="archivos[]" multiple>
                                                <label class="custom-file-label" for="archivos">Buscar documentos</label>
                                             </div>
                                          </form>
                                       </div>
                                       <div class="modal-footer justify-content-between">
                                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                          <button type="button" class="btn btn-primary" onclick="agregarTarea()">Guardar</button>
                                       </div>
                                    </div>
                                    <!-- /.modal-content -->
                                 </div>
                                 <!-- /.modal-dialog -->
                              </div>
                              <!-- /.modal -->

                              <!-- Modal fecha de revision del programa de gestion-->
                              <div class="modal fade" id="modal-fecha-revision" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                 <div class="modal-dialog">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <h5 class="modal-title" id="exampleModalLabel">Fecha de análisis del caso</h5>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                             <span aria-hidden="true">&times;</span>
                                          </button>
                                       </div>
                                       <div class="modal-body">
                                          <form id="form-fecha-revision">
                                             <input type="hidden" name="caso_id" value="<?php echo $_GET["caso"] ?>">
                                             <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                   <span class="input-group-text" id="basic-addon1">Fecha</span>
                                                </div>
                                                <input type="date" class="form-control" placeholder="Fecha de análisis" aria-label="Fecha de revisión" aria-describedby="basic-addon1" name="fecha_revision" id="fecha-revision">
                                             </div>
                                          </form>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="button" class="btn btn-primary" onclick="actualizarFechaAnalisis()">Guardar cambios</button>

                                       </div>
                                    </div>
                                 </div>
                              </div>


                              <!-- Modal detalles de cada tarea como los documentos -->
                              <div class="modal fade" id="modal_detail_task">
                                 <div class="modal-dialog">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <h4 class="modal-title">Detalles de tarea</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                             <span aria-hidden="true">&times;</span>
                                          </button>
                                       </div>
                                       <div class="modal-body" id="modal-body">
                                          <form id="form-nuevo-avance" enctype="multipart/form-data">
                                             <b>Avance o retraso de tarea</b>
                                             <hr>

                                             <div class="input-group mb-3">
                                                <input type="number" class="form-control" placeholder="Avance representativo" name="avance_tarea">
                                             </div>

                                             <div class="input-group mb-3">
                                                <textarea class="form-control" placeholder="Agregar comentario" name="observaciones"></textarea>
                                             </div>

                                             <!-- <div class="input-group mb-3">
                                             <div class="input-group-prepend">
                                                <span class="input-group-text">Upload</span>
                                             </div>
                                             <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="fileEvidencias" name="evidencias[]" lang="es" multiple>
                                                <label class="custom-file-label" for="fileEvidencias">Choose file</label>
                                             </div>
                                          </div> -->
                                             <div class="d-flex justify-content-center">
                                                <span class="btn btn-success ocultable text-center col-12" onclick="subirAvance()">Registrar avance</span>
                                             </div>
                                          </form>
                                          <hr>
                                          <table class="table table-sm">
                                             <thead>
                                                <tr>
                                                   <th colspan="2">Bitácora de tarea</th>
                                                </tr>
                                             </thead>
                                             <tbody id='table_body_docs'>
                                             </tbody>
                                          </table>

                                          <table class="table table-sm">
                                             <thead>
                                                <tr>
                                                   <th scope="col">Documento</th>
                                                   <th scope="col">Acciones</th>
                                                </tr>
                                             </thead>
                                             <tbody id='table_body_task_docs'>
                                             </tbody>
                                          </table>

                                          <form id="form_new_doc" class="my-3">
                                             <input type="hidden" id="tarea_id" name="tarea_id">
                                             <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="archivos" placeholder="Buscar documentos" name="new_docs[]" multiple>
                                                <label class="custom-file-label" for="archivos">Agregar archivos</label>
                                             </div>
                                             <div class="d-flex justify-content-center mt-3">
                                                <span type="button" class="btn btn-primary ocultable col-12" onclick="agregarDoc()">Guardar ayuda a tarea</span>
                                             </div>
                                          </form>
                                       </div>
                                       <div class="modal-footer justify-content-between">
                                          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                       </div>
                                    </div>
                                    <!-- /.modal-content -->
                                 </div>
                                 <!-- /.modal-dialog -->
                              </div>
                              <!-- /.modal -->

                              <!-- Modal agregar archivos al caso -->
                              <div class="modal fade" id="modal_caso_docs">
                                 <div class="modal-dialog">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <h4 class="modal-title">Agregar archivos</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                             <span aria-hidden="true">&times;</span>
                                          </button>
                                       </div>
                                       <div class="modal-body" id="modal-body">

                                          <form id="form_caso_doc" class="my-3" enctype="mul">
                                             <input type="hidden" id="caso_id" name="caso_id" value="<?php echo $_GET["caso"] ?>">
                                             <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="archivos" placeholder="Buscar documentos" name="new_docs[]" multiple>
                                                <label class="custom-file-label" for="archivos">Agregar archivos</label>
                                             </div>
                                          </form>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="button" class="btn btn-primary" onclick="casoNewDoc()">Guardar documentos</button>
                                       </div>
                                    </div>
                                    <!-- /.modal-content -->
                                 </div>
                                 <!-- /.modal-dialog -->
                              </div>
                              <!-- /.modal -->
                           </span>
                           <hr>

                           <!-- Task section -->
                           <div id="task_section">

                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-12 col-md-12 col-lg-4 order-1 order-md-2 mt-4">
                     <hr class="d-block d-md-none">
                     <h6 class="text-danger">OBJETIVO/META/PROBLEMA/NO CONFORMIDAD</h6>
                     <p class="text-muted"><?php echo $caso["caso_nota"] ?></p>
                     <br>

                     <h6 class="text-danger">OBSERVACIONES DEL REVISADO</h6>
                     <!-- <p class="text-muted"><?php echo $caso["caso_nota_cierre"] ?></p> -->
                     <textarea class="form-control mb-3" id="input-caso-nota-cierre" name="caso_nota_cierre" rows="3"><?php echo $caso["caso_nota_cierre"] ?></textarea>

                     <h6 class="text-danger">BENEFICIO</h6>
                     <form id="formulario-beneficio">
                        <input type="hidden" value="<?php echo $_GET["caso"] ?>" name="id">
                        <div class="form-group">
                           <textarea class="form-control" id="input-beneficio" name="caso_beneficio" rows="3"><?php echo $caso["caso_beneficio"] ?></textarea>
                        </div>
                        <span class="btn btn-primary w-100 ocultable" id="btn-enviar-beneficio" onclick="guardarBeneficio()">Guardar beneficio</span>
                     </form>

                     <script>
                        function guardarBeneficio() {
                           const formData = new FormData($("#formulario-beneficio")[0])
                           const jsonData = {};

                           // Convertir FormData a JSON
                           formData.forEach((value, key) => {
                              jsonData[key] = value;
                           });

                           $.ajax({
                              url: "ajax/caso.php",
                              method: "PATCH",
                              contentType: "application/json",
                              data: JSON.stringify(jsonData),
                              success: res => {
                                 console.log(res);
                              }
                           })
                        }
                     </script>

                     <h5 class="mt-5 text-muted">Evidencias/Documentos</h5>
                     <ul class="list-unstyled">

                     </ul>

                     <table class="table table-sm table-borderless">
                        <tbody id="case_doc_task">

                        </tbody>
                     </table>
                     <div class="text-center mt-5 mb-3">
                        <!-- AQUI LE QUITE EL btn_agregar_documento-->
                        <button type="button" class="btn btn-primary btn-sm ocultable " data-toggle="modal" data-target="#modal_caso_docs">
                           <i class="fa-solid fa-plus"></i> Agregar documento
                        </button>
                     </div>
                  </div>
               </div>
            </div>
            <!-- /.card-body -->
         </div>
         <!-- /.card -->

   </section>
   <!-- /.content -->

</main>

<script>
   function dnoneBackdrop() {
      let modalBackDrop = document.querySelectorAll(".modal-backdrop")

      modalBackDrop.forEach(e => {
         e.style.display = "none"
      })
   }

   /**
    * Funcion que me va a modificar el avance de la tarea y la observacion
    */
   function subirAvance() {
      const datos = new FormData($("#form-nuevo-avance")[0])
      datos.append("cate_id", taskIdInput.val())

      $.ajax({
         url: "ajax/tareas-avance.php",
         method: "POST",
         contentType: false,
         processData: false,
         data: datos,
         success: res => {
            console.log(res);
            $("#form-nuevo-avance").trigger("reset");
         },
         complete: () => {
            getDocsTask(taskIdInput.val())
            obtenerTareas()
         }
      })
   }

   // Funcion que actualiza la fecha de revision del caso ------------------------------
   function actualizarFechaAnalisis() {
      let datos = new FormData($("#form-fecha-revision")[0])

      $.ajax({
         method: "POST",
         url: "ajax/caso.php",
         processData: false,
         contentType: false,
         data: datos,
         success: res => {
            let datos = JSON.parse(res)
            $("#fecha_revision_span").text(datos.fecha)
            alert(datos.msg)
            location.reload()
         }
      })

   }

   //Funcion que me agrega una tarea---------------------------------------
   function agregarTarea() {
      let formTask = document.getElementById("form_nueva_tarea")
      const taskData = new FormData($("#form_task")[0])

      $.ajax({
         type: "POST",
         url: "ajax/tareas.php",
         data: taskData,
         contentType: false,
         processData: false,
         success: data => {
            obtenerTareas()
            formTask.style.display = "none"
            // modalBackDrop.style.display = "none"
            dnoneBackdrop()
            // alert(data)
         },
         error: (xhr, status, thrown) => {
            console.log(xhr.statusText);
         }
      })
   }

   //Funcion que me trae todas las tareas
   function obtenerTareas() {
      const taskSection = document.getElementById("task_section")
      let caso_id = $("#caso_id").val()
      let html = ""

      $.ajax({
         type: "GET",
         url: "ajax/tareas.php",
         data: {
            caso_id: caso_id
         },
         success: data => {
            const tasks = JSON.parse(data)

            if (tasks[0]) {
               tasks.forEach(task => {
                  let asignado = task.usua_nombre ? `Usuario -> ${task.usua_nombre}` : `Departamento -> ${task.depa_nombre}`;

                  if (task.ultimo_avance == null) {
                     task.ultimo_avance = 0
                  }

                  html += `
               <div class="post">
                  <div class="user-block">
                     <div class="username d-flex justify-content-between">
                        <div>
                           <span>${task.cate_nombre}</span>
                        </div>
                        <div class="d-flex justify-content-end">
                        <button class='edit-btn btn ocultable administrador' data-toggle="modal" data-target="#form_nueva_tarea" onclick="editarTarea(${task.cate_id})">
                        <i class="fa-solid fa-pen-to-square" style="color: #FFD43B;"></i>
                        </button>
                        <button class='dlt-btn btn administrador' data-id=${task.cate_id}>
                        <i class="fa-solid fa-trash" style="color: #ff0000;"></i>
                        </button>
                        </div>
                     </div>
                     <div class="description">
                        <span class="text-success d-block">Fecha de aper: ${task.cate_fecha_inicio}</span>
                        <span class="text-danger d-block">Fecha de cierre: ${task.cate_fecha_cierre}</span>
                        <span class="d-block">Asignado a: ${asignado}</span>
                     </div>
                  </div>
                  <p class="mb-1">
                     <b>Descripcion:</b> ${task.cate_descripcion}
                  </p>
                  <p class="mb-1">
                     <b>Observaciones: </b>${task.cate_observaciones}
                  </p>
                  <p class="mb-1">
                     <b>Recursos</b>: ${task.cate_recursos}
                  </p>
                  <div class="d-flex justify-content-between w-100">
                  <button class='btn btn-primary' data-toggle="modal" data-target="#modal_detail_task" onclick="ejecutarOpenTaskDetails(${task.cate_id})">Evidencias/Documentos</button> 
                  <span>Avance total: ${task.ultimo_avance}%</span>
                  </div>
                  </div>
               `
               });

               taskSection.innerHTML = html
               html = ""

            } else {
               taskSection.innerHTML = `<h2 class='text-center'>No hay tareas para este caso</h2>`
            }
         },
         complete: () => {
            let btnDlts = document.querySelectorAll(".dlt-btn")

            //Funcion que elimina la tarea por completo----------------------------------
            btnDlts.forEach(btn => {
               btn.addEventListener("click", () => {
                  $.ajax({
                     type: "DELETE",
                     url: "ajax/tareas.php",
                     contentType: "application/json",
                     data: JSON.stringify({
                        tarea_id: btn.dataset.id
                     }),
                     success: (response) => {
                        console.log(response)
                     },
                     complete: () => {
                        obtenerTareas()
                     }
                  })
               })
            })
         }
      })
   }
   obtenerTareas()

   function ejecutarOpenTaskDetails(id) {
      getDocTaskGenerals(id)
      getDocsTask(id)
   }

   function editarTarea(cate_id) {
      $("#cate_id").val("");
      $("#form_task").trigger("reset");

      if (cate_id !== "") {
         $("#cate_id").val(cate_id);

         $.ajax({
            url: "ajax/tareas.php",
            method: "GET",
            data: {
               cate_id: cate_id
            },
            success: function(response) {
               try {
                  const res = JSON.parse(response);
                  console.log("Datos recibidos:", res); // Para debug

                  // Campos básicos
                  $("#nombre").val(res.cate_nombre);
                  $("#fecha_inicio").val(res.cate_fecha_inicio);
                  $("#fecha_fin").val(res.cate_fecha_cierre);
                  $("#descripcion").val(res.cate_descripcion);
                  $("#recursos").val(res.cate_recursos);
                  $("#observaciones").val(res.cate_observaciones);

                  // Usuarios
                  $("select[name='usuario']").val(res.usua_id);
                  $("select[name='usua_asignado_2']").val(res.usua_id_2 || '');
                  $("select[name='usua_asignado_3']").val(res.usua_id_3 || '');

               } catch (error) {
                  console.error("Error al procesar la respuesta:", error);
                  alert("Error al cargar los datos de la tarea");
               }
            },
            error: function(xhr, status, error) {
               console.error("Error en la petición AJAX:", error);
               alert("Error al obtener los datos de la tarea");
            }
         });
      }
   }

   // Funcion para agregar nuevo documento al caso -------------------------------
   function casoNewDoc() {
      const modalCasoDocs = document.getElementById("modal_caso_docs")
      let modalBackDrop = document.querySelectorAll(".modal-backdrop")
      const casoNewDocForm = $("#form_caso_doc")
      const datos = new FormData(casoNewDocForm[0])

      $.ajax({
         type: "POST",
         url: "ajax/caso.php",
         contentType: false,
         processData: false,
         data: datos,
         success: (res) => {
            console.log(res);
            getDocCaso()
            modalCasoDocs.style.display = "none"
            dnoneBackdrop()
         }
      })
   }

   //Funcion que me trae los documentos del caso ----------------------------------
   function getDocCaso() {
      let caso_id = $("#caso_id").val()
      const caseDocSection = $("#case_doc_task")
      html = ''


      $.ajax({
         type: "GET",
         url: "ajax/caso.php",
         data: {
            caso_id: caso_id
         },
         success: (res) => {
            datos = JSON.parse(res)
            if (datos[0]) {
               datos.forEach(e => {
                  html += `
               <tr class="d-flex justify-content-between">
                  <td>
                     <a target="_blank" href="img/casos_docs/${e.cado_ref}" class="btn-link text-secondary">${e.cado_nombre}</a>
                  </td>
                  <td>
                  <div class="text-white btn-group btn-group-sm">
                        <a target='_blank' class="btn btn-info" href="img/casos_docs/${e.cado_ref}"">
                        <i class="fa-solid fa-eye"></i>
                        </a>
                        <button class="text-white btn btn-danger btn-doc-delete ocultable administrador" onclick='deleteDocCaso(${e.cado_id})'>
                              <i class="fa-solid fa-trash"></i>
                              </button>
                        </div>
                  </td>
               </tr>`
               })
               caseDocSection.html(html)
            } else {
               html += `
               <tr class="d-flex justify-content-between">
                  <td>No hay documentos</td>
               </tr>`
               caseDocSection.html(html)
            }

         }
      })
   }

   getDocCaso()

   //Funcion que eliminar cada documento del caso ---------------------------------
   function deleteDocCaso(doc_id) {

      $.ajax({
         type: "DELETE",
         contentType: "application/json",
         url: "ajax/caso.php",
         data: JSON.stringify({
            cado_id: doc_id
         }),
         success: (e) => {
            console.log(e);
            getDocCaso()
         }
      })
   }

   //Funcion que me agrega un documento a la tarea ---------------------------------
   const taskIdInput = $("#tarea_id") //Controla el id de la tarea

   function agregarDoc() {
      const formDocTask = $("#form_new_doc")
      const docData = new FormData(formDocTask[0])

      $.ajax({
         type: "POST",
         url: "ajax/tareas_docs.php",
         data: docData,
         contentType: false,
         processData: false,
         success: (res) => {
            console.log(res)
         },
         complete: () => {
            getDocsTask(taskIdInput.val())
            getDocTaskGenerals(taskIdInput.val())
         }
      })
   }

   // Funcion que elimina un doc especifico mediante parametro de una tarea-----------------------------------
   function deleteDocTask(doc_id) {
      $.ajax({
         type: "DELETE",
         url: "ajax/tareas_docs.php",
         contentType: "application/json",
         data: JSON.stringify({
            doc_id: doc_id
         }),
         success: (response) => {
            console.log(response)
         },
         complete: () => {
            getDocsTask(taskIdInput.val())
            getDocTaskGenerals(taskIdInput.val())
         }
      })
   }

   // Funcion que trae los documentos generales de la tarea
   function getDocTaskGenerals(doc_id) {
      let html2 = ""

      $.get("ajax/tareas_docs.php", {
            tarea_id: doc_id
         },
         function(data) {
            let datos = JSON.parse(data);

            datos.forEach(e => {
               html2 += `<tr>
                  <td>
                     <a target="_blank" href="img/casos_docs/${e.tado_ref}" class="btn-link text-secondary">${e.tado_nombre}</a>
                  </td>
                  <td>
                  <div class="text-white btn-group btn-group-sm">
                        <a target='_blank' class="btn btn-info" href="img/casos_docs/${e.tado_ref}"">
                        <i class="fa-solid fa-eye"></i>
                        </a>
                        <button class="text-white btn btn-danger btn-doc-delete ocultable" onclick='deleteDocTask(${e.tado_id})'>
                              <i class="fa-solid fa-trash"></i>
                              </button>
                        </div>
                  </td>
               </tr>`
            })

            $("#table_body_task_docs").html(html2);
            // getDocTaskGenerals(taskIdInput.val())
         })
   }

   // Funcion que me trae los documentos de la tarea y la bitacora de la tarea
   function getDocsTask(tarea_id) {
      taskIdInput.val(tarea_id)
      html = ""
      let html2 = ""
      let tableDocsBody = document.getElementById("table_body_docs")

      $.ajax({
         type: "GET",
         url: "ajax/tareas-avance.php",
         data: {
            tarea_id: tarea_id
         },
         success: res => {
            let avances = JSON.parse(res)
            avances.forEach(e => {

               if (e.documentos != null) {
                  html2 = ""
                  let docus = e.documentos
                  docus = docus.split(",")
                  console.log(docus)
                  docus.forEach(doc => {
                     html2 += `<p>
                           <a href="img/tareas_docs/${doc}" target="_blank" class="link-black text-sm"><i class="fas fa-link mr-1"></i>${doc}</a>
                          </p>`
                  })
               }
               html += `
               <div class="user-block">
                        <span class="">
                          <b class="text-success">Avance representativo: ${e.catb_avance}</b>
                        </span>
                        <span class="description">Fecha - ${e.catb_fecha}</span>
                      </div>
                      <!-- /.user-block -->
                      <p>
                        ${e.catb_descripcion}
                      </p>
                      ${html2}
                    </div><hr>`
            });
            // $("#bitacora-section").html(html)
            $("#table_body_docs").html(html)
         }
      })
   }

   function cerrarCaso(tipo) {
      let datos = {
         id: <?php echo $_GET["caso"] ?>
      };

      if (tipo === 'aprobado') {
         datos.usua_id_aprobado = true;
      } else if (tipo === 'revisado') {
         datos.usua_id_revisado = true;
      } else if (tipo === 'cerrar') {
         datos.usua_id_cerrado = true;
         datos.caso_nota_cierre = $("#input-caso-nota-cierre").val();
      }


      $.ajax({
         type: "PATCH",
         url: "ajax/caso.php",
         contentType: "application/json",
         data: JSON.stringify(datos),
         success: res => {
            try {
               res = JSON.parse(res);
               console.log(res);
               if (res.success) {
                  if (tipo === 'aprobado') {
                     if (res.remainingApprovals > 0) {
                        alert(`Aprobación registrada. Faltan ${res.remainingApprovals} aprobación(es).`);
                     } else {
                        alert('Aprobación registrada exitosamente.');
                     }
                     location.reload();
                  } else if (tipo === 'revisado') {
                     if (res.remainingReviews > 0) {
                        alert(`Revisión registrada. Faltan ${res.remainingReviews} revisión(es).`);
                     } else {
                        alert('Revisión registrada exitosamente.');
                     }
                     location.reload();
                  }
               } else {
                  alert(res.error || "Error al procesar la solicitud");
               }
            } catch (e) {
               console.error("Error al procesar la respuesta:", e);
               alert("Error al procesar la respuesta del servidor");
            }
         },
         error: (jqXHR, textStatus, errorThrown) => {
            console.error("Error en la petición AJAX:", textStatus, errorThrown);
            alert("Error en la comunicación con el servidor");
         }
      });
   }

   <?php if (strtoupper($caso["caso_estado"]) == "CERRADO"): ?>
      $(document).ajaxComplete(function() {
         ocultarTrash();
      });

      function ocultarTrash() {
         $(".fa-trash").hide()
         $(".ocultable").hide()
      }

   <?php endif ?>

   //Ocultar botones de edicion y de eliminar
   <?php if ($_SESSION["administrador_caso"] == 0): ?>

      function soloAdministrador() {
         $(".administrador").hide()
      }

      $(document).ajaxComplete(function() {
         soloAdministrador()
      })
   <?php endif ?>

   $(function() {
      //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL
      $(".btn_agregar_documento").hide()

      <?php echo pantalla_roles("index.php?p=detalle-caso", $_SESSION["login_user"]) ?>
   });
</script>