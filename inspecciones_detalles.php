<?php
include_once "conexion.php";

// Obtener el ID de inspección desde la URL
$id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;

if ($id === 0) {
    die("ID de inspección no válido");
}

// Consulta para obtener los datos de la inspección
$sql = "SELECT * FROM inspecciones WHERE insp_id = $id";
$result = mysql_query($sql);
$inspeccion = mysql_fetch_assoc($result);
$usuario_encargado = $inspeccion['usua_id_inspeccion'];
$sql = "SELECT i.*, u.usua_nombre 
        FROM inspecciones i
        JOIN usuarios u ON i.usua_id_inspeccion = u.usua_id 
        WHERE i.insp_id = $id";
$result = mysql_query($sql);
$usuario = mysql_fetch_assoc($result);


// Verifica si la inspección existe
if (!$inspeccion) {
    die("Inspección no encontrada");
}

// Obtener el inti_id del registro actual
$inti_id = $inspeccion['inti_id'];

// Consulta para obtener el inti_nombre
$sql_inti = "SELECT inti_nombre FROM inspecciones_tipos WHERE inti_id = $inti_id";
$result_inti = mysql_query($sql_inti);
$inti_data = mysql_fetch_assoc($result_inti);
$inti_nombre = $inti_data ? $inti_data['inti_nombre'] : '';

// Tomamos el id del usuario
$usuaID = $_SESSION['login_user'];

// Tomar las opciones de selección
$sql = "SELECT * FROM inspecciones_seleccion";
$opciones = mysql_query($sql);

// Tomar las preguntas utilizando el inti_id de la inspección actual
$sql = "SELECT * FROM inspecciones_preguntas WHERE inti_id = '$inti_id'";
$preguntas1 = mysql_query($sql);

// Obtener el tipo, para saber
$sql = "SELECT * FROM inspecciones WHERE inti_id = '$inti_id'";
$result = mysql_query($sql) or die("Error en la consulta: " . mysql_error());
$row = mysql_fetch_assoc($result);

// Obtener operaciones
$sql = "SELECT * FROM inspecciones_tipo_operacion";
$operaciones = mysql_query($sql);



$respuestas_guardadas = [];
$sql_respuestas = "SELECT inpr_id, inse_id, inde_comentario FROM inspecciones_detalles WHERE insp_id = '$id'";
$resultado_respuestas = mysql_query($sql_respuestas);

while ($respuesta = mysql_fetch_assoc($resultado_respuestas)) {
    $inpr_id = $respuesta['inpr_id'];
    $respuestas_guardadas[$inpr_id] = [
        'inse_id' => $respuesta['inse_id'],
        'comentario' => $respuesta['inde_comentario']
    ];
}
// Consulta para obtener los campos de cabecera dinámicos según el tipo de inspección
$sql_cabecera = "SELECT intc_id, intc_etiqueta, intc_tipo_campo 
                 FROM inspecciones_tipo_cabecera 
                 WHERE inti_id = '$inti_id' 
                 ORDER BY intc_id";
$result_cabecera = mysql_query($sql_cabecera);

// Verificar si hay campos de cabecera para este tipo de inspección
$campos_cabecera = [];
if ($result_cabecera && mysql_num_rows($result_cabecera) > 0) {
    while ($campo = mysql_fetch_assoc($result_cabecera)) {
        $campos_cabecera[] = $campo;
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<div id="form-container" class="d-flex flex-column align-items-center">
    <div class="container bg-white p-4 rounded"> <!-- Contenedor con fondo blanco -->


        <button class="btn btn-secondary btn-sm" onclick="irAtras()" title="Regresar">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h2 class="text-center mb-4"><?php echo htmlspecialchars($inti_nombre); ?></h2>
        <p class="text-center mb-4"> Inspección Realizada Por: <?php echo $usuario['usua_nombre']; ?></p>
        <input type="hidden" name="insp_id" value="<?php echo $id; ?>">
        
<?php
// Primero consultamos los datos existentes
$datos_existentes = [];
if (!empty($id)) { // Asumiendo que $id contiene el insp_id
    $sql = "SELECT intc_id, inca_respuesta FROM inspecciones_cabecera WHERE insp_id = '$id'";
    $result = mysql_query($sql);
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $datos_existentes[$row['intc_id']] = $row['inca_respuesta'];
        }
    }
}
?>

<form id="formCabeceraInspeccion" method="post">
    <input type="hidden" name="insp_id" value="<?php echo $id; ?>">
    <div class="mb-4">
        <div class="row">
            <?php foreach ($campos_cabecera as $campo): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                    <label for="campo_<?php echo $campo['intc_id']; ?>" class="form-label small fw-bold text-dark text-center d-block">
                        <?php echo htmlspecialchars($campo['intc_etiqueta']); ?>
                    </label>
                    
                    <?php
                    $tipo_campo = strtolower($campo['intc_tipo_campo']);
                    $campo_id = 'campo_' . $campo['intc_id'];
                    $campo_name = 'cabecera[' . $campo['intc_id'] . ']';
                    $valor_actual = $datos_existentes[$campo['intc_id']] ?? '';
                    $input_classes = 'form-control form-control-sm text-dark';
                    $select_classes = 'form-select form-select-sm border-primary rounded-3 text-dark fw-semibold shadow-sm';
                   
                    switch ($tipo_campo) {
                        case 'texto':
                        case 'text':
                            echo '<input type="text" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '">';
                            break;
                            
                        case 'numerico':
                        case 'number':
                        case 'numero':
                            echo '<input type="number" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '">';
                            break;
                            
                        case 'fecha':
                        case 'date':
                            // Formatear fecha para input type="date" (YYYY-MM-DD)
                            $fecha_value = '';
                            if (!empty($valor_actual)) {
                                $fecha_timestamp = strtotime($valor_actual);
                                $fecha_value = date('Y-m-d', $fecha_timestamp);
                            }
                            echo '<input type="date" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . $fecha_value . '">';
                            break;
                            
                        case 'hora':
                        case 'time':
                            // Formatear hora para input type="time" (HH:MM)
                            $hora_value = '';
                            if (!empty($valor_actual)) {
                                $hora_timestamp = strtotime($valor_actual);
                                $hora_value = date('H:i', $hora_timestamp);
                            }
                            echo '<input type="time" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . $hora_value . '">';
                            break;
                            
                        case 'email':
                            echo '<input type="email" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '">';
                            break;
                            
                        case 'telefono':
                        case 'tel':
                            echo '<input type="tel" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '">';
                            break;
                            
                        case 'textarea':
                        case 'area_texto':
                            echo '<textarea id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" rows="2">' . htmlspecialchars($valor_actual) . '</textarea>';
                            break;
                            
                        case 'select':
                        case 'seleccion':
                            echo '<div class="text-center position-relative">';
                            echo '<select id="' . $campo_id . '" name="' . $campo_name . '" class="' . $select_classes . '">';
                            echo '<option value="">Seleccione...</option>';
                            
                            $etiqueta_lower = strtolower(trim($campo['intc_etiqueta']));
                            if (strpos($etiqueta_lower, 'tipo de operaci') !== false || 
                                strpos($etiqueta_lower, 'tipo operaci') !== false ||
                                $etiqueta_lower == 'tipo de operación' ||
                                $etiqueta_lower == 'tipo operacion') {
                                
                                $sql_operaciones = "SELECT * FROM inspecciones_tipo_operacion ORDER BY into_nombre";
                                $result_operaciones = mysql_query($sql_operaciones);
                                if ($result_operaciones && mysql_num_rows($result_operaciones) > 0) {
                                    while ($row_op = mysql_fetch_assoc($result_operaciones)) {
                                        $selected = ($row_op['into_id'] == $valor_actual) ? 'selected' : '';
                                        echo '<option value="' . $row_op['into_id'] . '" ' . $selected . ' class="text-dark fw-normal">' . htmlspecialchars($row_op['into_nombre']) . '</option>';
                                    }
                                }
                            }
                            
                            echo '</select>';
                            echo '</div>';
                            break;
                            
                        default:
                            echo '<input type="text" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '">';
                            break;
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Botón de submit -->
  <!-- Botón de submit - Solo se muestra si hay campos -->
    <div class="text-end mt-3" id="submitButtonContainer" style="display: none;">
        <button type="submit" class="btn btn-primary">
            <?php echo empty($datos_existentes) ? 'Guardar Datos' : 'Actualizar Datos'; ?>
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inicializar el manejador del formulario
    handleFormSubmit('formCabeceraInspeccion');
});
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay campos en el formulario
    function verificarCampos() {
        const form = document.getElementById('formCabeceraInspeccion');
        const submitContainer = document.getElementById('submitButtonContainer');
        
        // Buscar todos los inputs, selects y textareas (excluyendo hidden inputs)
        const campos = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        
        // Si hay al menos un campo visible, mostrar el botón
        if (campos.length > 0) {
            submitContainer.style.display = 'block';
        } else {
            submitContainer.style.display = 'none';
        }
    }
    
    // Ejecutar la verificación al cargar la página
    verificarCampos();
});
</script>


   
<div class="container d-flex justify-content-center">
    <div class="col-lg-8 col-md-10">
        <!-- Sección de Inspección y Referencias -->
        <form id="inspectionForm" method="post" enctype="multipart/form-data">
            <!-- Mostrar el nombre del inti_id -->
            <!-- Sección de las preguntas con tabla responsiva -->
            <div class="form-row justify-content-center">
                <div class="form-group col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>VERIFICAR/REVISAR</th>
                                    <?php
                                    // Reiniciar el puntero del resultado de las opciones
                                    mysql_data_seek($opciones, 0);
                                    // Contar y mostrar las opciones como encabezados
                                    if ($opciones) {
                                        while ($opcion = mysql_fetch_assoc($opciones)) {
                                            echo '<th>' . htmlspecialchars($opcion['inse_nombre']) . '</th>';
                                        }
                                    }
                                    ?>
                                    <th>OBSERVACIONES O COMENTARIOS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($preguntas1) {
                                    while ($pregunta = mysql_fetch_assoc($preguntas1)) {
                                        $inpr_id = $pregunta['inpr_id'];
                                        $respuesta_actual = isset($respuestas_guardadas[$inpr_id]) ? $respuestas_guardadas[$inpr_id] : null;
                                
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($pregunta['inpr_nombre']) . '</td>';
                                
                                        mysql_data_seek($opciones, 0);
                                        if ($opciones) {
                                            while ($opcion = mysql_fetch_assoc($opciones)) {
                                                $checked = ($respuesta_actual && $respuesta_actual['inse_id'] == $opcion['inse_id']) ? 'checked' : '';
                                                echo '<td>';
                                                echo '<label style="display: inline-block;">';
                                                echo '<input type="radio" name="seleccion' . $inpr_id . '" value="' . htmlspecialchars($opcion['inse_id']) . '" ' . $checked . '>';
                                                echo '</label>';
                                                echo '</td>';
                                            }
                                        } else {
                                            echo '<td colspan="' . (mysql_num_rows($opciones)) . '">No hay opciones disponibles.</td>';
                                        }
                                
                                        $comentario = $respuesta_actual ? htmlspecialchars($respuesta_actual['comentario']) : '';
                                        echo '<td><textarea rows="3" class="form-control form-control-sm" name="comentarios_' . $inpr_id . '">' . $comentario . '</textarea></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3">No hay preguntas disponibles.</td></tr>';
                                }
                                
                                ?>
                            </tbody>
                        </table>
                    </div> <!-- Fin de tabla responsiva -->
                </div>
            </div>

            <!-- Botón para guardar inspección -->
            <div class="form-row">
                <div class="col text-center">
                    <input type="button" value="Guardar Preguntas" class="btn btn-primary btn-sm" onclick="guardarInspeccion();">
                </div>
            </div>
            <!-- Botón de enviar -->
            <!-- <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
                    </button>
                </div> -->
        </form> <!-- Fin de inspectionForm -->

        <!-- REFERENCIAS -->
        <form id="referenceForm" enctype="multipart/form-data">
            <input type="hidden" name="insp_id" value="<?php echo $id; ?>" id="insp_id">

            <!-- Nombre de la referencia -->
            <div class="form-group">
                <label for="nombre">Nombre de la referencia:</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre de la referencia" class="form-control" required>
            </div>

            <!-- Input para Imagen de Referencia -->
            <div class="form-group">
                <label for="referencia">Imagen de Referencia:</label>
                <input type="file" name="referencia[]" id="referencia" class="form-control" accept="image/*" onchange="previewImages('referencia')" multiple required>
                <div id="referenciaPreview" class="file-preview"></div>
            </div>

            <!-- Input para Acción Correctiva -->
            <div class="form-group">
                <label for="accion_correctiva">Acción Correctiva:</label>
                <input type="file" name="accion_correctiva[]" id="accion_correctiva" class="form-control" accept="image/*" onchange="previewImages('accion_correctiva')" multiple>
                <div id="accionCorrectivaPreview" class="file-preview"></div>
            </div>

            <!-- Comentario -->
            <div class="form-group">
                <label for="comentario">Comentario:</label>
                <input type="text" name="comentario" id="comentario" placeholder="Comentario" class="form-control" required>
            </div>
            <!-- Select para elegir usuario -->
            <div class="form-group" style="width: 100%">
                <label for="usuario_id">Usuario:</label>
                <select name="usua_id" id="usua_id" class="form-control" required>
                    <option value="">Seleccione un usuario</option>
                    <?php
                    // Consulta para obtener usuarios (usa tu propia consulta aquí)
                    $query = "SELECT usua_id, usua_nombre FROM usuarios ORDER BY usua_nombre";
                    $result = mysql_query($query);

                    if ($result && mysql_num_rows($result) > 0) {
                        while ($row = mysql_fetch_assoc($result)) {
                            echo '<option value="' . $row['usua_id'] . '">' . $row['usua_nombre'] . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>


            <button type="button" id="saveReferenceBtn" class="btn btn-primary" onclick="saveReference()">Guardar Referencia</button>

        </form> <!-- Fin de referenceForm -->
        <div class="table-responsive">
            <table class="table table-bordered mt-3" id="referencesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre de la referencia</th>
                        <th>Imágenes de Referencia</th>
                        <th>Acciones Correctivas</th>
                        <th>Comentario</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí se llenarán las referencias guardadas -->
                </tbody>
            </table>
        </div>
        <button type="button" id="generarReportebtn" class="btn btn-primary" onclick="generarReporte()">Generar Reporte</button>
    </div>
</div>

<script>
    function irAtras() {
        window.history.back(); // Navega a la página anterior
    }
    // Limitar imagenes a 2, en input-file
    function previewImages(inputId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(inputId + "Preview");

        // Limitar a 2 archivos
        if (input.files.length > 2) {
            alert("Solo puedes seleccionar un máximo de 2 imágenes.");
            input.value = ""; // Limpiar el input
            preview.innerHTML = ""; // Limpiar la vista previa
            return;
        }

        // preview.innerHTML = ""; // Limpiar la vista previa antes de mostrar las nuevas imágenes

        // // Mostrar las imágenes seleccionadas
        // Array.from(input.files).forEach(file => {
        //     const reader = new FileReader();
        //     reader.onload = function(e) {
        //         const img = document.createElement("img");
        //         img.src = e.target.result;
        //         img.style.width = "100px"; // Tamaño de la imagen
        //         img.style.marginRight = "10px"; // Espaciado entre imágenes
        //         preview.appendChild(img);
        //     };
        //     reader.readAsDataURL(file);
        // });
    }
    // Función para limpiar el formulario
    function clearForm() {
        // Obtener el formulario
        const form = document.getElementById('referenceForm');

        // Obtener los inputs de tipo file
        const inputReferencia = document.getElementById('referencia');
        const inputAccionCorrectiva = document.getElementById('accion_correctiva');

        // Limpiar los valores de los inputs file
        inputReferencia.value = '';
        inputAccionCorrectiva.value = '';

        // Resetear el formulario
        form.reset();

        // Limpiar las previsualizaciones
        document.getElementById('referenciaPreview').innerHTML = '';
        document.getElementById('accionCorrectivaPreview').innerHTML = '';

        // Si estás usando arrays para almacenar las imágenes, también límpialos
        if (typeof filesArrayReferencia !== 'undefined') filesArrayReferencia = [];
        if (typeof filesArrayAccionCorrectiva !== 'undefined') filesArrayAccionCorrectiva = [];
    }

    // Arrays para almacenar archivos seleccionados
    let selectedReferenceFiles = [];
    let selectedCorrectionFiles = [];

    // Manejar la selección de archivos de referencia
    function handleReferenceFileSelect(event) {
        const input = event.target;
        const preview = document.getElementById('referenciaPreview');

        selectedReferenceFiles.push(...Array.from(input.files));
        updatePreview(selectedReferenceFiles, preview, 'referencia');
    }

    // Manejar la selección de archivos de acción correctiva
    function handleCorrectionFileSelect(event) {
        const input = event.target;
        const preview = document.getElementById('accionCorrectivaPreview');

        selectedCorrectionFiles.push(...Array.from(input.files));
        updatePreview(selectedCorrectionFiles, preview, 'accion_correctiva');
    }

    // Arrays to store files
    let filesArrayReferencia = [];
    let filesArrayAccionCorrectiva = [];

    // Update preview of selected files
    function updatePreview(filesArray, previewElement) {
        previewElement.innerHTML = '';
        if (filesArray.length > 0) {
            filesArray.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'file-item';
                    div.innerHTML = `
                    <img src="${e.target.result}" alt="${file.name}" class="image-preview">
                    <button type="button" class="remove-file" data-index="${index}" data-type="${previewElement.id}">X</button>
                    <div class="file-name">${file.name}</div>
                `;
                    previewElement.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    // Add files to corresponding array and update preview
    function addFiles(inputElement, fileType) {
        const newFiles = Array.from(inputElement.files);
        if (fileType === 'referencia') {
            filesArrayReferencia = [...filesArrayReferencia, ...newFiles];
            updatePreview(filesArrayReferencia, document.getElementById('referenciaPreview'));
        } else if (fileType === 'accion_correctiva') {
            filesArrayAccionCorrectiva = [...filesArrayAccionCorrectiva, ...newFiles];
            updatePreview(filesArrayAccionCorrectiva, document.getElementById('accionCorrectivaPreview'));
        }
    }
    // Remove selected file from array and update input and preview
    function removeFile(index, fileType) {
        let targetArray;
        let previewElement;
        let inputName;

        if (fileType === 'referenciaPreview') {
            targetArray = filesArrayReferencia;
            previewElement = document.getElementById('referenciaPreview');
            inputName = 'referencia';
        } else if (fileType === 'accionCorrectivaPreview') {
            targetArray = filesArrayAccionCorrectiva;
            previewElement = document.getElementById('accionCorrectivaPreview');
            inputName = 'accion_correctiva';
        }

        // Remove only the file at the specified index
        targetArray.splice(index, 1);

        // Update the corresponding array
        if (fileType === 'referenciaPreview') {
            filesArrayReferencia = [...targetArray];
        } else {
            filesArrayAccionCorrectiva = [...targetArray];
        }

        // Update input and preview
        updateFileInput(inputName, targetArray);
        updatePreview(targetArray, previewElement);
    }

    // Update input with remaining files
    function updateFileInput(fileType, filesArray) {
        const inputElement = document.querySelector(`input[name="${fileType}[]"]`);
        const dataTransfer = new DataTransfer();
        filesArray.forEach(file => {
            dataTransfer.items.add(file);
        });
        inputElement.files = dataTransfer.files;
    }

    // Handle file selection for reference files
    function handleReferenceFileSelect(event) {
        addFiles(event.target, 'referencia');
    }

    // Handle file selection for correction files
    function handleCorrectionFileSelect(event) {
        addFiles(event.target, 'accion_correctiva');
    }

    // Event listener for remove buttons
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-file')) {
            const index = parseInt(event.target.getAttribute('data-index'));
            const fileType = event.target.getAttribute('data-type');
            removeFile(index, fileType);
        }
    });

    // Event listeners for file inputs
    document.getElementById('referencia').addEventListener('change', handleReferenceFileSelect);
    document.getElementById('accion_correctiva').addEventListener('change', handleCorrectionFileSelect);

    let referenceCount = 0;

    // Función que guarda las referencias creadas
    function saveReference() {
        const nombre = document.getElementById('nombre').value;
        const comentario = document.getElementById('comentario').value;
        const referenciaFiles = document.getElementById('referencia').files;
        const accionCorrectivaFiles = document.getElementById('accion_correctiva').files;
        const usua_id = document.getElementById('usua_id').value;

        // Validar que los campos no estén vacíos
        if (!nombre || referenciaFiles.length === 0 || !comentario) {
            alert("Por favor, completa todos los campos.");
            return;
        }

        let formData = new FormData();
        formData.append('nombre', nombre);
        formData.append('comentario', comentario);
        formData.append('usua_id', usua_id);
        for (let i = 0; i < referenciaFiles.length; i++) {
            formData.append('referencia[]', referenciaFiles[i]);
        }
        for (let i = 0; i < accionCorrectivaFiles.length; i++) {
            formData.append('accion_correctiva[]', accionCorrectivaFiles[i]);
        }

        const insp_id = document.getElementById('insp_id').value;
        formData.append('insp_id', insp_id);

        $.ajax({
            url: 'ajax/registro_detalle_referencia.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log("Respuesta del servidor:", response);
                try {
                    const jsonResponse = JSON.parse(response);
                    alert(jsonResponse.message);

                    // Solo cargar las referencias desde el servidor
                    // y eliminar la llamada a addReferenceToTable()
                    loadReferences();

                    // Limpiar el formulario después de guardar exitosamente
                    clearForm();
                } catch (e) {
                    console.error("Error al procesar la respuesta:", e);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", status, error);
                alert("Hubo un problema al guardar la referencia. Error: " + error);
            }
        });
    }

    function getFileNames(files) {
        let fileNames = [];
        for (let i = 0; i < files.length; i++) {
            fileNames.push(files[i].name);
        }
        return fileNames.join(', ');
    }

    $(document).ready(function() {
        loadReferences();
    });

    // Función para cargar las referencias en la tabla
    function loadReferences() {
        const insp_id = document.getElementById('insp_id').value;

        // Reiniciar el contador de referencias cada vez que se cargan nuevas
        referenceCount = 0;

        // Limpiar la tabla antes de agregar nuevas filas
        $('#referencesTable tbody').empty();

        $.ajax({
            url: 'ajax/registro_detalle_referencia.php?insp_id=' + insp_id,
            type: 'GET',
            success: function(response) {
                try {
                    const jsonResponse = JSON.parse(response);
                    console.log("Respuesta del servidor:", jsonResponse);

                    if (jsonResponse.status === 'success') {
                        const groupedReferences = {};

                        jsonResponse.data.forEach(ref => {
                            console.log("Referencia:", ref);

                            if (typeof ref.tipo !== 'undefined') {
                                const nombreNormalizado = ref.nombre.trim();

                                if (!groupedReferences[nombreNormalizado]) {
                                    groupedReferences[nombreNormalizado] = {
                                        referencia: [],
                                        correccion: [],
                                        comentario: ref.comentario,
                                        usua_nombre: ref.usua_nombre
                                    };
                                }

                                if (ref.tipo === "1") {
                                    groupedReferences[nombreNormalizado].referencia.push(ref.ruta);
                                } else if (ref.tipo === "2") {
                                    groupedReferences[nombreNormalizado].correccion.push(ref.ruta);
                                } else {
                                    console.warn("Tipo no reconocido:", ref.tipo);
                                }
                            } else {
                                console.warn("Tipo no definido para:", ref);
                            }
                        });

                        for (const nombre in groupedReferences) {
                            const fotosReferencia = groupedReferences[nombre].referencia;
                            const fotosCorreccion = groupedReferences[nombre].correccion;
                            const comentario = groupedReferences[nombre].comentario;
                            const usua_nombre = groupedReferences[nombre].usua_nombre;
                            addReferenceToTable(nombre, fotosReferencia, fotosCorreccion, comentario, usua_nombre);
                        }
                    } else {
                        alert("Error al cargar las referencias: " + jsonResponse.message);
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta:", e);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", status, error);
                alert("Hubo un problema al cargar las referencias. Error: " + error);
            }
        });
    }

    function addReferenceToTable(nombre, fotosReferencia, fotosCorreccion, comentario, usua_nombre) {
        referenceCount++;

        const fotosReferenciaHTML = Array.isArray(fotosReferencia) ?
            fotosReferencia.map(foto => {
                const rutaCompleta = `img/referencias/${foto}`;
                console.log("Ruta completa de la imagen de referencia:", rutaCompleta);
                return `<img src="${rutaCompleta}" alt="${foto}" style="width: 50px; height: 50px;">`;
            }).join(' ') :
            'No hay imágenes de referencia';

        const fotosCorreccionHTML = Array.isArray(fotosCorreccion) ?
            fotosCorreccion.map(foto => {
                const rutaCompleta = `img/referencias/${foto}`;
                console.log("Ruta completa de la imagen de corrección:", rutaCompleta);
                return `<img src="${rutaCompleta}" alt="${foto}" style="width: 50px; height: 50px;">`;
            }).join(' ') :
            'No hay imágenes de corrección';

        const referenciasRow = `
<tr>
  <td>${referenceCount}</td>
  <td>${nombre}</td>
  <td>${fotosReferenciaHTML}</td>
  <td>${fotosCorreccionHTML}</td>
  <td>${comentario}</td>
  <td>${usua_nombre}</td>
</tr>
`;

        $('#referencesTable tbody').append(referenciasRow);
    }

    // Guardar las preguntas del form
    function guardarInspeccion() {
        const formData = new FormData(document.getElementById('inspectionForm'));
        const urlParams = new URLSearchParams(window.location.search);
        const insp_id = urlParams.get('insp_id');

        if (!formData.has('insp_id')) {
            formData.append('insp_id', insp_id);
        }

        for (const [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        $.ajax({
            url: "ajax/registro_detalle_inspeccion.php",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta del servidor:", response);
                if (response.status === 'success') {
                    alert("Preguntas Guardadas");
                } else {
                    alert("Error al guardar preguntas: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", status, error);
                console.log("Respuesta del servidor:", xhr.responseText);
                alert("Hubo un problema al guardar las preguntas. Por favor, revisa la consola para más detalles.");
            }
        });
    }

    function generarReporte() {
        const urlParams = new URLSearchParams(window.location.search);
        const inspeccionId = urlParams.get('insp_id');

        if (!inspeccionId) {
            alert('No se encontró el ID de inspección en la URL');
            return;
        }

        document.getElementById('generarReportebtn').disabled = true;
        document.getElementById('generarReportebtn').innerHTML = 'Generando...';

        const formData = new FormData();
        formData.append('insp_id', inspeccionId);

        fetch('ajax/generar_reporte_inspeccion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('generarReportebtn').disabled = false;
                document.getElementById('generarReportebtn').innerHTML = 'Generar Reporte';

                if (data.status === 'success') {
                    alert(data.message);

                    if (data.file) {
                        const enlaceDescarga = document.createElement('a');
                        enlaceDescarga.href = "../inspecciones/" + data.file;
                        enlaceDescarga.textContent = 'Descargar reporte';
                        enlaceDescarga.download = data.file;
                        document.body.appendChild(enlaceDescarga);
                        enlaceDescarga.click();
                        document.body.removeChild(enlaceDescarga);
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                document.getElementById('generarReportebtn').disabled = false;
                document.getElementById('generarReportebtn').innerHTML = 'Generar Reporte';
            });
    }

    function handleFormSubmit(formId) {
        $(`#${formId}`).on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            const inspId = $('input[name="insp_id"]').val();
            formData.append('insp_id', inspId);

            $.ajax({
                url: 'ajax/registro_inspeccion.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log("Respuesta del servidor:", response);
                    if (response.status === 'success') {
                        alert("Datos guardados correctamente");
                    } else {
                        alert("Error al guardar la inspección: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: Hubo un problema en la conexión con el servidor');
                }
            });
        });
    }

    $(document).ready(function() {
        const formIds = ['formGroup1', 'formGroup2', 'formGroup3', 'formGroup4'];

        formIds.forEach(formId => {
            if ($(`#${formId}`).length > 0) {
                handleFormSubmit(formId);
            }
        });

        $('.conditional-form').each(function() {
            if ($(this).find('button[type="submit"]').length === 0) {
                $(this).append(`
        <div class="text-center mt-3">
          <button type="submit" class="btn btn-primary">
            Guardar Cambios
          </button>
        </div>
      `);
            }
        });
    });
</script>
    <style>
        .file-item {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            width: 100px;
            /* Limitar el ancho de cada contenedor */
        }
    
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    
        .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
        }
    
        .file-name {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
            word-wrap: break-word;
            max-width: 100px;
        }
    
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    
        .remove-file {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
        }
    
        .file-name {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
            word-wrap: break-word;
            max-width: 100px;
        }
    
    
        .container {
            background-color: #fff;
            /* Fondo blanco para todo el div principal */
            padding: 20px;
            /* Espaciado alrededor del contenedor */
            border-radius: 5px;
            /* Bordes redondeados */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Sombra sutil */
        }
    
    
    
        .text-center p {
            color: #6c757d;
            /* Color gris para el texto de Inspección Realizada Por */
        }
    
        .table {
            margin-top: 20px;
            /* Espacio arriba de la tabla */
        }
    
        .table thead th {
            background-color: #f8f9fa;
            /* Fondo claro para el encabezado de la tabla */
            text-align: center;
            /* Centrar texto en encabezados */
        }
    
        .table tbody tr {
            transition: background-color 0.2s;
            /* Transición suave al pasar el ratón */
        }
    
        .table tbody tr:hover {
            background-color: #f1f1f1;
            /* Color de fondo al pasar el ratón */
        }
    
        .btn-primary,
        .btn-success {
            font-size: 1rem;
            /* Tamaño de fuente para botones */
            padding: 5px 15px;
            /* Espaciado interno más pequeño */
            margin: 10px auto;
            /* Centrar el botón */
            display: block;
            /* Hacer que el botón se comporte como un bloque */
        }
    
        .form-group {
            margin-bottom: 15px;
            /* Espacio entre grupos de formulario */
        }
    
        input[type="text"],
        input[type="file"] {
            width: 70%;
            /* Hacer el input de texto más estrecho */
            margin: 0 auto;
            /* Centrar los inputs */
            display: block;
            /* Hacer que el input se comporte como un bloque */
        }
    
        #referenceForm {
    
            /* Línea entre el botón de guardar y referencias */
            padding-top: 20px;
            /* Espacio superior */
        }
    
        .file-preview {
            display: flex;
            flex-wrap: wrap;
            margin-top: 10px;
        }
    
        .file-item img {
            max-width: 100%;
            max-height: 100px;
            border: 1px solid #dee2e6;
            /* Borde de Bootstrap */
            border-radius: 0.25rem;
            /* Bordes redondeados de Bootstrap */
        }
    
        .file-item .remove-file {
            position: absolute;
            top: 0;
            right: 0;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            padding: 2px 5px;
            /* Espaciado alrededor del botón */
        }
    
        /* Centrar etiquetas e inputs */
        .form-group {
            text-align: center;
            /* Centra el texto de las etiquetas */
        }
    
        /* Ajustar ancho de inputs */
        .form-control {
            width: 60%;
            /* Ajusta el ancho de los inputs al 60% */
            margin: 0 auto;
            /* Centra los inputs */
        }
    
        /* Hacer botones de referencia angostos */
        .btn {
            padding: 5px 10px;
            /* Ajusta el padding de los botones */
        }
    
        /* Estilo para el botón de firmar documento */
        #signDocumentBtn {
            margin-top: 10px;
            /* Espacio superior */
            width: auto;
            /* Ajusta el ancho automático */
        }
    </style>