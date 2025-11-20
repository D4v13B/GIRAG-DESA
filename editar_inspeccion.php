<?php
include_once "conexion.php";
include_once "funciones.php";
session_start();

// Obtener el ID de inspección desde la URL
$id = isset($_GET['insp_id']) ? intval($_GET['insp_id']) : 0;

if ($id === 0) {
    die("ID de inspección no válido");
}

// 1. Consulta para obtener los datos de la inspección y el estado de completado
$sql = "SELECT
            i.*,
            u.usua_nombre,
            CASE WHEN EXISTS (
                SELECT 1
                FROM inspecciones_referencias ir
                LEFT JOIN inspecciones_fotos inf ON ir.inre_id = inf.inre_id AND inf.info_tipo = '2'
                WHERE ir.insp_id = i.insp_id AND inf.info_id IS NOT NULL
            ) THEN 'Sí' ELSE 'No' END AS completado
        FROM
            inspecciones i
        JOIN
            usuarios u ON i.usua_id_inspeccion = u.usua_id
        WHERE
            i.insp_id = $id";

$result = mysql_query($sql);
$inspeccion = mysql_fetch_assoc($result);

// Verifica si la inspección existe
if (!$inspeccion) {
    die("Inspección no encontrada");
}

$esta_completada = ($inspeccion['completado'] === 'Sí');
$inti_id = $inspeccion['inti_id'];

// 2. Obtener el nombre del tipo de inspección
$sql_inti = "SELECT inti_nombre FROM inspecciones_tipos WHERE inti_id = $inti_id";
$result_inti = mysql_query($sql_inti);
$inti_data = mysql_fetch_assoc($result_inti);
$inti_nombre = $inti_data ? $inti_data['inti_nombre'] : '';

// 3. Tomar las opciones de selección y preguntas
$sql = "SELECT * FROM inspecciones_seleccion";
$opciones = mysql_query($sql);
$sql = "SELECT * FROM inspecciones_preguntas WHERE inti_id = '$inti_id'";
$preguntas1 = mysql_query($sql);
$sql = "SELECT * FROM inspecciones_tipo_operacion";
$operaciones = mysql_query($sql);

// 4. Obtener las respuestas guardadas para las preguntas
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

// 5. Obtener los campos de cabecera dinámicos
$sql_cabecera = "SELECT intc_id, intc_etiqueta, intc_tipo_campo
                 FROM inspecciones_tipo_cabecera
                 WHERE inti_id = '$inti_id'
                 ORDER BY intc_id";
$result_cabecera = mysql_query($sql_cabecera);
$campos_cabecera = [];
if ($result_cabecera && mysql_num_rows($result_cabecera) > 0) {
    while ($campo = mysql_fetch_assoc($result_cabecera)) {
        $campos_cabecera[] = $campo;
    }
}

// 6. Consultar los datos de cabecera ya guardados
$datos_existentes = [];
$sql = "SELECT intc_id, inca_respuesta FROM inspecciones_cabecera WHERE insp_id = '$id'";
$result = mysql_query($sql);
if ($result && mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        $datos_existentes[$row['intc_id']] = $row['inca_respuesta'];
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<div id="form-container" class="d-flex flex-column align-items-center">
    <div class="container bg-white p-4 rounded">
        <button class="btn btn-secondary btn-sm" onclick="irAtras()" title="Regresar">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h2 class="text-center mb-4">
            <?php echo $esta_completada ? 'Inspección Completada' : 'Completar Inspección'; ?>
        </h2>
        <p class="text-center mb-4">
            Inspección Realizada Por: <?php echo htmlspecialchars($inspeccion['usua_nombre']); ?>
        </p>
        <p class="text-center text-danger">
            <?php echo $esta_completada ? 'Esta inspección ya está completa y no puede ser editada.' : 'Estado de la Inspección: En progreso'; ?>
        </p>
        <input type="hidden" name="insp_id" value="<?php echo $id; ?>">

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
                            $disabled = $esta_completada ? 'disabled' : '';
                            $tipo_campo = strtolower($campo['intc_tipo_campo']);
                            $campo_id = 'campo_' . $campo['intc_id'];
                            $campo_name = 'cabecera[' . $campo['intc_id'] . ']';
                            $valor_actual = $datos_existentes[$campo['intc_id']] ?? '';
                            $input_classes = 'form-control form-control-sm text-dark';
                            $select_classes = 'form-select form-select-sm border-primary rounded-3 text-dark fw-semibold shadow-sm';

                            switch ($tipo_campo) {
                                case 'texto':
                                case 'text':
                                    echo '<input type="text" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '" ' . $disabled . '>';
                                    break;

                                case 'numerico':
                                case 'number':
                                case 'numero':
                                    echo '<input type="number" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '" ' . $disabled . '>';
                                    break;

                                case 'fecha':
                                case 'date':
                                    $fecha_value = '';
                                    if (!empty($valor_actual)) {
                                        $fecha_timestamp = strtotime($valor_actual);
                                        $fecha_value = date('Y-m-d', $fecha_timestamp);
                                    }
                                    echo '<input type="date" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . $fecha_value . '" ' . $disabled . '>';
                                    break;

                                case 'hora':
                                case 'time':
                                    $hora_value = '';
                                    if (!empty($valor_actual)) {
                                        $hora_timestamp = strtotime($valor_actual);
                                        $hora_value = date('H:i', $hora_timestamp);
                                    }
                                    echo '<input type="time" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . $hora_value . '" ' . $disabled . '>';
                                    break;

                                case 'email':
                                    echo '<input type="email" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '" ' . $disabled . '>';
                                    break;

                                case 'telefono':
                                case 'tel':
                                    echo '<input type="tel" id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" value="' . htmlspecialchars($valor_actual) . '" ' . $disabled . '>';
                                    break;

                                case 'textarea':
                                case 'area_texto':
                                    echo '<textarea id="' . $campo_id . '" name="' . $campo_name . '" class="' . $input_classes . '" rows="2" ' . $disabled . '>' . htmlspecialchars($valor_actual) . '</textarea>';
                                    break;

                                case 'select':
                                case 'seleccion':
                                    echo '<div class="text-center position-relative">';
                                    echo '<select id="' . $campo_id . '" name="' . $campo_name . '" class="' . $select_classes . '" ' . $disabled . '>';
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
                                    echo '<input type="text" id="' . $campo_id . '" name="' . $campo_name . '" class="'. $input_classes . '" value="' . htmlspecialchars($valor_actual) . '" ' . $disabled . '>';
                                    break;
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-end mt-3" id="submitButtonContainer" style="display: none;">
                <button type="submit" class="btn btn-primary" <?php echo $esta_completada ? 'disabled' : ''; ?>>
                    <?php echo empty($datos_existentes) ? 'Guardar Datos' : 'Actualizar Datos'; ?>
                </button>
            </div>
        </form>
        <hr>

        <div class="container d-flex justify-content-center">
            <div class="col-lg-8 col-md-10">
                <form id="inspectionForm" method="post" enctype="multipart/form-data">
                    <div class="form-row justify-content-center">
                        <div class="form-group col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>VERIFICAR/REVISAR</th>
                                            <?php
                                            mysql_data_seek($opciones, 0);
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
                                                        $disabled = $esta_completada ? 'disabled' : '';
                                                        echo '<td>';
                                                        echo '<label style="display: inline-block;">';
                                                        echo '<input type="radio" name="seleccion' . $inpr_id . '" value="' . htmlspecialchars($opcion['inse_id']) . '" ' . $checked . ' ' . $disabled . '>';
                                                        echo '</label>';
                                                        echo '</td>';
                                                    }
                                                } else {
                                                    echo '<td colspan="' . (mysql_num_rows($opciones)) . '">No hay opciones disponibles.</td>';
                                                }

                                                $comentario = $respuesta_actual ? htmlspecialchars($respuesta_actual['comentario']) : '';
                                                $disabled = $esta_completada ? 'disabled' : '';
                                                echo '<td><textarea rows="3" class="form-control form-control-sm" name="comentarios_' . $inpr_id . '" ' . $disabled . '>' . $comentario . '</textarea></td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="3">No hay preguntas disponibles.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php if (!$esta_completada): ?>
                        <div class="form-row">
                            <div class="col text-center">
                                <input type="button" value="Guardar Preguntas" class="btn btn-primary btn-sm" onclick="guardarInspeccion();">
                            </div>
                        </div>
                    <?php endif; ?>
                </form>

                <hr>

                <form id="referenceForm" enctype="multipart/form-data">
                    <input type="hidden" name="insp_id" value="<?php echo $id; ?>" id="insp_id">

                    <div class="form-group">
                        <label for="nombre">Nombre de la referencia:</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Nombre de la referencia" class="form-control" <?php echo $esta_completada ? 'disabled' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <label for="referencia">Imagen de Referencia:</label>
                        <input type="file" name="referencia[]" id="referencia" class="form-control" accept="image/*" multiple <?php echo $esta_completada ? 'disabled' : ''; ?>>
                        <div id="referenciaPreview" class="file-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="accion_correctiva">Acción Correctiva:</label>
                        <input type="file" name="accion_correctiva[]" id="accion_correctiva" class="form-control" accept="image/*" multiple <?php echo $esta_completada ? 'disabled' : ''; ?>>
                        <div id="accionCorrectivaPreview" class="file-preview"></div>
                    </div>

                    <div class="form-group">
                        <label for="comentario">Comentario:</label>
                        <input type="text" name="comentario" id="comentario" placeholder="Comentario" class="form-control" <?php echo $esta_completada ? 'disabled' : ''; ?>>
                    </div>
                    <div class="form-group" style="width: 100%">
                        <label for="usuario_id">Usuario:</label>
                        <select name="usua_id" id="usua_id" class="form-control" <?php echo $esta_completada ? 'disabled' : ''; ?>>
                            <option value="">Seleccione un usuario</option>
                            <?php
                            $query = "SELECT usua_id, usua_nombre FROM usuarios ORDER BY usua_nombre";
                            $result_users = mysql_query($query);

                            if ($result_users && mysql_num_rows($result_users) > 0) {
                                while ($row = mysql_fetch_assoc($result_users)) {
                                    echo '<option value="' . $row['usua_id'] . '">' . $row['usua_nombre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <?php if (!$esta_completada): ?>
                        <button type="button" id="saveReferenceBtn" class="btn btn-primary" onclick="saveReference()">
                            Guardar Referencia
                        </button>
                    <?php endif; ?>
                </form>

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
                            </tbody>
                    </table>
                </div>

                <?php if (!$esta_completada): ?>
                    <button type="button" id="generarReportebtn" class="btn btn-primary" onclick="generarReporte()">Generar Reporte</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function irAtras() {
        window.history.back();
    }

    // Función para limpiar el formulario de referencias
    function clearForm() {
        const form = document.getElementById('referenceForm');
        form.reset();
        filesArrayReferencia = [];
        filesArrayAccionCorrectiva = [];
        updatePreview(filesArrayReferencia, document.getElementById('referenciaPreview'));
        updatePreview(filesArrayAccionCorrectiva, document.getElementById('accionCorrectivaPreview'));
    }

    // Funciones para manejar los archivos (mantener como en inspecciones_detalles.php)
    let filesArrayReferencia = [];
    let filesArrayAccionCorrectiva = [];

    function updatePreview(filesArray, previewElement) {
        previewElement.innerHTML = '';
        if (filesArray.length > 0) {
            filesArray.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'file-item';
                const reader = new FileReader();
                reader.onload = function(e) {
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
        targetArray.splice(index, 1);
        if (fileType === 'referenciaPreview') {
            filesArrayReferencia = [...targetArray];
        } else {
            filesArrayAccionCorrectiva = [...targetArray];
        }
        updateFileInput(inputName, targetArray);
        updatePreview(targetArray, previewElement);
    }

    function updateFileInput(fileType, filesArray) {
        const inputElement = document.querySelector(`input[name="${fileType}[]"]`);
        const dataTransfer = new DataTransfer();
        filesArray.forEach(file => {
            dataTransfer.items.add(file);
        });
        inputElement.files = dataTransfer.files;
    }

    function handleReferenceFileSelect(event) {
        addFiles(event.target, 'referencia');
    }

    function handleCorrectionFileSelect(event) {
        addFiles(event.target, 'accion_correctiva');
    }

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-file')) {
            const index = parseInt(event.target.getAttribute('data-index'));
            const fileType = event.target.getAttribute('data-type');
            removeFile(index, fileType);
        }
    });

    document.getElementById('referencia').addEventListener('change', handleReferenceFileSelect);
    document.getElementById('accion_correctiva').addEventListener('change', handleCorrectionFileSelect);

    // Funciones para guardar y cargar datos
    function saveReference() {
        const nombre = document.getElementById('nombre').value;
        const comentario = document.getElementById('comentario').value;
        const referenciaFiles = document.getElementById('referencia').files;
        const accionCorrectivaFiles = document.getElementById('accion_correctiva').files;
        const usua_id = document.getElementById('usua_id').value;

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
                try {
                    const jsonResponse = JSON.parse(response);
                    alert(jsonResponse.message);
                    loadReferences();
                    clearForm();
                } catch (e) {
                    console.error("Error al procesar la respuesta:", e);
                }
            },
            error: function(xhr, status, error) {
                alert("Hubo un problema al guardar la referencia. Error: " + error);
            }
        });
    }

    // Funciones para guardar y cargar datos
    function loadReferences() {
        const insp_id = document.getElementById('insp_id').value;
        $('#referencesTable tbody').empty();

        $.ajax({
            url: 'ajax/registro_detalle_referencia.php?insp_id=' + insp_id,
            type: 'GET',
            success: function(response) {
                try {
                    const jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === 'success') {
                        const groupedReferences = {};
                        jsonResponse.data.forEach(ref => {
                            const nombreNormalizado = ref.nombre.trim();
                            if (!groupedReferences[nombreNormalizado]) {
                                groupedReferences[nombreNormalizado] = {
                                    referencia: [],
                                    correccion: [],
                                    comentario: ref.comentario,
                                    usua_nombre: ref.usua_nombre,
                                    id: ref.inre_id,
                                    usua_id: ref.usua_id
                                };
                            }

                            if (ref.tipo === "1") {
                                groupedReferences[nombreNormalizado].referencia.push(ref.ruta);
                            } else if (ref.tipo === "2") {
                                groupedReferences[nombreNormalizado].correccion.push(ref.ruta);
                            }
                        });

                        let referenceCount = 0;
                        let hasMissingCorrectiveAction = false;
                        for (const nombre in groupedReferences) {
                            referenceCount++;
                            const data = groupedReferences[nombre];
                            addReferenceToTable(referenceCount, nombre, data.referencia, data.correccion, data.comentario, data.usua_nombre, data.id);

                            // Verificar si falta la acción correctiva
                            if (data.referencia.length > 0 && data.correccion.length === 0 && !hasMissingCorrectiveAction) {
                                prefillReferenceForm(nombre, data.comentario, data.usua_id);
                                hasMissingCorrectiveAction = true;
                            }
                        }

                        // Después de cargar, enfocar en el campo de acción correctiva si es necesario
                        if(hasMissingCorrectiveAction) {
                            focusOnMissingCorrectiveAction();
                        }
                    } else {
                        alert("Error al cargar las referencias: " + jsonResponse.message);
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta:", e);
                }
            },
            error: function(xhr, status, error) {
                alert("Hubo un problema al cargar las referencias. Error: " + error);
            }
        });
    }

    function addReferenceToTable(index, nombre, fotosReferencia, fotosCorreccion, comentario, usua_nombre, inre_id) {
        const fotosReferenciaHTML = Array.isArray(fotosReferencia) ?
            fotosReferencia.map(foto => {
                const rutaCompleta = `img/referencias/${foto}`;
                return `<img src="${rutaCompleta}" alt="${foto}" style="width: 50px; height: 50px;">`;
            }).join(' ') :
            'No hay imágenes de referencia';

        const fotosCorreccionHTML = Array.isArray(fotosCorreccion) && fotosCorreccion.length > 0 ?
            fotosCorreccion.map(foto => {
                const rutaCompleta = `img/referencias/${foto}`;
                return `<img src="${rutaCompleta}" alt="${foto}" style="width: 50px; height: 50px;">`;
            }).join(' ') :
            '<span class="text-danger">PENDIENTE</span>';

        const referenciasRow = `
<tr>
  <td>${index}</td>
  <td>${nombre}</td>
  <td>${fotosReferenciaHTML}</td>
  <td>${fotosCorreccionHTML}</td>
  <td>${comentario}</td>
  <td>${usua_nombre}</td>
</tr>
`;
        $('#referencesTable tbody').append(referenciasRow);
    }

    // Nueva función para precargar el formulario
    function prefillReferenceForm(nombre, comentario, usua_id) {
        document.getElementById('nombre').value = nombre;
        document.getElementById('comentario').value = comentario;
        document.getElementById('usua_id').value = usua_id;
    }

    // Función para enfocar en la acción correctiva pendiente
    function focusOnMissingCorrectiveAction() {
        const referenceTable = document.getElementById('referencesTable');
        const rows = referenceTable.querySelectorAll('tbody tr');
        let hasMissing = false;
        let firstMissingRow = null;

        rows.forEach(row => {
            const correctiveCell = row.cells[3]; // La celda de "Acciones Correctivas" es la 4ª (índice 3)
            if (correctiveCell && correctiveCell.textContent.trim() === 'PENDIENTE') {
                hasMissing = true;
                correctiveCell.style.backgroundColor = 'yellow';
                if (!firstMissingRow) {
                    firstMissingRow = row;
                }
            } else {
                correctiveCell.style.backgroundColor = '';
            }
        });

        if(hasMissing && firstMissingRow) {
            firstMissingRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('accion_correctiva').focus();
        }
    }


    // Funciones para guardar preguntas y generar reporte (mantener como en inspecciones_detalles.php)
    function guardarInspeccion() {
        const formData = new FormData(document.getElementById('inspectionForm'));
        const urlParams = new URLSearchParams(window.location.search);
        const insp_id = urlParams.get('insp_id');
        formData.append('insp_id', insp_id);
        $.ajax({
            url: "ajax/registro_detalle_inspeccion.php",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert("Preguntas Guardadas");
                } else {
                    alert("Error al guardar preguntas: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("Hubo un problema al guardar las preguntas.");
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
        const formIds = ['formCabeceraInspeccion'];
        formIds.forEach(formId => {
            if ($(`#${formId}`).length > 0) {
                handleFormSubmit(formId);
            }
        });
        loadReferences();
        document.addEventListener('DOMContentLoaded', function() {
            function verificarCampos() {
                const form = document.getElementById('formCabeceraInspeccion');
                const submitContainer = document.getElementById('submitButtonContainer');
                const campos = form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
                if (campos.length > 0) {
                    submitContainer.style.display = 'block';
                } else {
                    submitContainer.style.display = 'none';
                }
            }
            verificarCampos();
        });
    });
</script>
<style>
    /* El resto del CSS de inspecciones_detalles.php va aquí */
    .file-item {
        position: relative;
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
        width: 100px;
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
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .text-center p {
        color: #6c757d;
    }
    .table {
        margin-top: 20px;
    }
    .table thead th {
        background-color: #f8f9fa;
        text-align: center;
    }
    .table tbody tr {
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .btn-primary,
    .btn-success {
        font-size: 1rem;
        padding: 5px 15px;
        margin: 10px auto;
        display: block;
    }
    .form-group {
        margin-bottom: 15px;
        text-align: center;
    }
    .form-control {
        width: 60%;
        margin: 0 auto;
        display: block;
    }
    .btn {
        padding: 5px 10px;
    }
    #signDocumentBtn {
        margin-top: 10px;
        width: auto;
    }
    .file-preview {
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;
        justify-content: center;
    }
    /* Estilos de tabla en general */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid black;
    }
    th, td {
        padding: 10px;
        text-align: left;
    }
</style>