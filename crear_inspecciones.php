<?php
include_once "conexion.php";
session_start();

$query = "SELECT depa_id, depa_nombre FROM departamentos ORDER BY depa_nombre";
$result = mysql_query($query); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Inspecciones</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c757d;
            --secondary-color: #495057;
            --accent-color: #5a6c7d;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --text-color: #212529;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: white;
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .main-title {
            font-weight: 500;
            font-size: 1.75rem;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .card-body {
            padding: 1.25rem;
        }

        .btn {
            font-size: 0.875rem;
            font-weight: 400;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--border-color);
            background-color: white;
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-gray);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: white;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(90, 108, 125, 0.1);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .inspection-card {
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
        }

        .inspection-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .field-item, .question-item {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.875rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .field-item:hover, .question-item:hover {
            background-color: var(--light-gray);
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 400;
            padding: 0.25rem 0.5rem;
            background-color: var(--light-gray);
            color: var(--text-muted);
        }

        .empty-state {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state i {
            color: var(--text-muted);
            opacity: 0.5;
        }

        .empty-state h3 {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 1.1rem;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .section-divider {
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }

        .text-small {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .icon-muted {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .btn-group .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }

        .nav-buttons {
            gap: 0.5rem;
        }

        .stats-text {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Vista Principal - Lista de Inspecciones -->
        <div id="mainView">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="main-title mb-0">
                    <i class="fas fa-clipboard-list me-2 icon-muted"></i>
                    Sistema de Inspecciones
                </h1>
                <button class="btn btn-primary" onclick="showCreateForm()">
                    <i class="fas fa-plus me-2"></i>Nueva Inspección
                </button>
            </div>

            <!-- Lista de Inspecciones -->
            <div id="inspectionsList" class="row g-3">
                <!-- Se llena dinámicamente -->
            </div>

           
        </div>

        <!-- Vista de Creación/Edición -->
        <div id="createEditView" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="main-title mb-0" id="formTitle">Nueva Inspección</h1>
                <div class="d-flex nav-buttons">
                    <button class="btn btn-primary" onclick="saveCompleteInspection()">
                        <i class="fas fa-check me-2"></i>Guardar
                    </button>
                    <button class="btn btn-outline-secondary" onclick="cancelEdit()">
                        Cancelar
                    </button>
                </div>
            </div>

          <!-- Información General -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-info-circle me-2"></i>Información General
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Departamento</label>
                    <select class="form-control" id="department" name="department" required>
                        <option value="">Seleccione un departamento</option>
                        <?php
                        // Si usas mysqli
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $row['depa_id'] . '">' . htmlspecialchars($row['depa_nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Título de la Inspección</label>
                    <input type="text" class="form-control" id="title" placeholder="Ej: Inspección de Equipos">
                </div>
            </div>
        </div>
        
    </div>
</div>

            <!-- Campos del Formulario -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i>Campos del Formulario
                </div>
                <div class="card-body">
                    <!-- Agregar nuevo campo -->
                    <div class="section-divider">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Nombre del Campo</label>
                                <input type="text" class="form-control" id="newFieldLabel" placeholder="Ej: Nombre del Inspector">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="newFieldType">
                                    <option value="text">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="date">Fecha</option>
                                    <option value="time">Hora</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary w-100" onclick="addField()">
                                    <i class="fas fa-plus me-2"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de campos -->
                    <div id="fieldsList">
                        <div id="fieldsEmpty" class="text-small text-center py-3" style="display: none;">
                            No hay campos agregados
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preguntas -->
            <!-- Preguntas -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-question-circle me-2"></i>Preguntas de la Inspección
    </div>
    <div class="card-body">
        <!-- Agregar nueva pregunta -->
        <div class="section-divider">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Nueva Pregunta</label>
                    <input type="text" class="form-control" id="newQuestion" placeholder="Ej: ¿Los equipos están en buen estado?">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Referencia (opcional)</label>
                    <input type="text" class="form-control" id="newQuestionRef" placeholder="Código de referencia">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="addQuestion()">
                        <i class="fas fa-plus me-2"></i>Agregar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de preguntas -->
        <div id="questionsList">
            <div id="questionsEmpty" class="text-small text-center py-3" style="display: none;">
                No hay preguntas agregadas
            </div>
        </div>
    </div>
</div>

        <!-- Vista de Visualización -->
        <div id="viewInspection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="main-title mb-0">Vista de Inspección</h1>
                <button class="btn btn-outline-secondary" onclick="closeView()">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </button>
            </div>

            <div id="inspectionDetails">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let inspections = [];
        let currentInspection = null;
        let currentFields = [];
        let currentQuestions = [];

        // Tipos de campo
        const fieldTypes = {
            'text': 'Texto',
            'number': 'Número',
            'date': 'Fecha',
            'time': 'Hora'
        };

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            updateInspectionsList();
        });

        // Mostrar formulario de creación
        function showCreateForm() {
            currentInspection = null;
            clearForm();
            document.getElementById('formTitle').textContent = 'Nueva Inspección';
            showView('createEditView');
        }

        // Mostrar vista
        function showView(viewId) {
            const views = ['mainView', 'createEditView', 'viewInspection'];
            views.forEach(id => {
                document.getElementById(id).style.display = id === viewId ? 'block' : 'none';
            });
        }

        // Limpiar formulario
        function clearForm() {
            document.getElementById('department').value = '';
            document.getElementById('title').value = '';
            document.getElementById('newFieldLabel').value = '';
            document.getElementById('newFieldType').value = 'text';
            document.getElementById('newQuestion').value = '';
            currentFields = [];
            currentQuestions = [];
            updateFieldsList();
            updateQuestionsList();
        }

        // Agregar campo
        function addField() {
            const label = document.getElementById('newFieldLabel').value.trim();
            const type = document.getElementById('newFieldType').value;

            if (label) {
                const field = {
                    id: Date.now(),
                    label: label,
                    type: type
                };
                currentFields.push(field);
                document.getElementById('newFieldLabel').value = '';
                document.getElementById('newFieldType').value = 'text';
                updateFieldsList();
            } else {
                alert('Por favor ingrese el nombre del campo');
            }
        }

        // Eliminar campo
        function removeField(fieldId) {
            currentFields = currentFields.filter(field => field.id !== fieldId);
            updateFieldsList();
        }

        // Actualizar lista de campos
        function updateFieldsList() {
            const container = document.getElementById('fieldsList');

            if (currentFields.length === 0) {
                container.innerHTML = '<div class="text-small text-center py-3">No hay campos agregados</div>';
                return;
            }

            let html = '';
            currentFields.forEach(field => {
                html += `
                    <div class="field-item d-flex justify-content-between align-items-center">
                        <div>
                            <span>${field.label}</span>
                            <span class="badge ms-2">${fieldTypes[field.type]}</span>
                        </div>
                        <button class="btn btn-outline-danger btn-sm" onclick="removeField(${field.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
// Modificamos la función addQuestion para incluir la referencia
function addQuestion() {
    const text = document.getElementById('newQuestion').value.trim();
    const ref = document.getElementById('newQuestionRef').value.trim();

    if (text) {
        const question = {
            id: Date.now(),
            text: text,
            ref: ref || null
        };
        currentQuestions.push(question);
        document.getElementById('newQuestion').value = '';
        document.getElementById('newQuestionRef').value = '';
        updateQuestionsList();
    } else {
        alert('Por favor ingrese la pregunta');
    }
}

// Función corregida para eliminar pregunta
function removeQuestion(questionId) {
    currentQuestions = currentQuestions.filter(question => question.id !== questionId);
    updateQuestionsList();
}

// Actualizamos la función updateQuestionsList - VERSIÓN CORREGIDA
function updateQuestionsList() {
    const container = document.getElementById('questionsList');
    const emptyState = document.getElementById('questionsEmpty');

    if (currentQuestions.length === 0) {
        emptyState.style.display = 'block';
        // NO limpiar el contenedor aquí, ya que contiene el elemento questionsEmpty
        // Solo ocultar cualquier pregunta existente
        const existingQuestions = container.querySelectorAll('.question-item');
        existingQuestions.forEach(item => item.remove());
        return;
    }

    emptyState.style.display = 'none';
    
    // Limpiar preguntas existentes pero mantener el questionsEmpty
    const existingQuestions = container.querySelectorAll('.question-item');
    existingQuestions.forEach(item => item.remove());
    
    // Agregar las nuevas preguntas
    currentQuestions.forEach(question => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-item d-flex justify-content-between align-items-center mb-2';
        questionDiv.innerHTML = `
            <div>
                <span>${question.text}</span>
                ${question.ref ? `<span class="badge bg-secondary ms-2">Ref: ${question.ref}</span>` : ''}
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="removeQuestion(${question.id})">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(questionDiv);
    });
}

        // Guardar inspección
        function saveInspection() {
            const department = document.getElementById('department').value.trim();
            const title = document.getElementById('title').value.trim();

            if (!department || !title) {
                alert('Por favor complete el departamento y título');
                return;
            }

            if (currentFields.length === 0 && currentQuestions.length === 0) {
                alert('Debe agregar al menos un campo o una pregunta');
                return;
            }

            const inspection = {
                id: currentInspection ? currentInspection.id : Date.now(),
                department: department,
                title: title,
                fields: [...currentFields],
                questions: [...currentQuestions],
                createdAt: currentInspection ? currentInspection.createdAt : new Date().toLocaleString()
            };

            if (currentInspection) {
                const index = inspections.findIndex(insp => insp.id === inspection.id);
                inspections[index] = inspection;
            } else {
                inspections.push(inspection);
            }

            updateInspectionsList();
            showView('mainView');
        }

        // Cancelar edición
        function cancelEdit() {
            showView('mainView');
        }

        // Editar inspección
        function editInspection(id) {
            currentInspection = inspections.find(insp => insp.id === id);
            if (currentInspection) {
                document.getElementById('department').value = currentInspection.department;
                document.getElementById('title').value = currentInspection.title;
                currentFields = [...currentInspection.fields];
                currentQuestions = [...currentInspection.questions];
                updateFieldsList();
                updateQuestionsList();
                document.getElementById('formTitle').textContent = 'Editar Inspección';
                showView('createEditView');
            }
        }

        // Ver inspección
        function viewInspection(id) {
            const inspection = inspections.find(insp => insp.id === id);
            if (inspection) {
                let html = `
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">${inspection.title}</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Departamento:</strong> ${inspection.department}</p>
                            <p class="mb-0 text-small">Creado: ${inspection.createdAt}</p>
                        </div>
                    </div>
                `;

                if (inspection.fields.length > 0) {
                    html += `
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-list me-2"></i>Campos del Formulario
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                    `;

                    inspection.fields.forEach(field => {
                        html += `
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">${field.label}</label>
                                    <input type="${field.type}" class="form-control" placeholder="Ingrese ${field.label.toLowerCase()}">
                                </div>
                            </div>
                        `;
                    });

                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                }

                if (inspection.questions.length > 0) {
                    html += `
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-question-circle me-2"></i>Preguntas de la Inspección
                            </div>
                            <div class="card-body">
                    `;

                    inspection.questions.forEach(question => {
                        html += `
                            <div class="question-item mb-2">
                                ${question.text}
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                }

                document.getElementById('inspectionDetails').innerHTML = html;
                showView('viewInspection');
            }
        }

        // Eliminar inspección
        function deleteInspection(id) {
            if (confirm('¿Eliminar esta inspección?')) {
                inspections = inspections.filter(insp => insp.id !== id);
                updateInspectionsList();
            }
        }

        // Cerrar vista
        function closeView() {
            showView('mainView');
        }

        // Actualizar lista de inspecciones
        function updateInspectionsList() {
            const container = document.getElementById('inspectionsList');
            const emptyState = document.getElementById('emptyState');

            if (inspections.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'flex';
                return;
            }

            emptyState.style.display = 'none';
            let html = '';

            inspections.forEach(inspection => {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card inspection-card h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">${inspection.title}</h6>
                                <p class="text-small text-muted mb-2">${inspection.department}</p>
                                <p class="text-small text-muted mb-3">${inspection.createdAt}</p>
                                <div class="stats-text mb-3">
                                    ${inspection.fields.length} campos • ${inspection.questions.length} preguntas
                                </div>
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="viewInspection(${inspection.id})">
                                        Ver
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="editInspection(${inspection.id})">
                                        Editar
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteInspection(${inspection.id})">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Event listeners para Enter key
        document.getElementById('newFieldLabel').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') addField();
        });

        document.getElementById('newQuestion').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') addQuestion();
        });


// Función para guardar toda la inspección (información básica + campos + preguntas)
function saveCompleteInspection(event) {
    // Prevenir el comportamiento por defecto si es llamado desde un formulario
    if (event) event.preventDefault();
    
    const department = document.getElementById('department').value.trim();
    const title = document.getElementById('title').value.trim();

    if (!department || !title) {
        alert('Por favor complete el departamento y título');
        return false;
    }

    if (currentFields.length === 0 && currentQuestions.length === 0) {
        alert('Debe agregar al menos un campo o una pregunta');
        return false;
    }

    // Obtener el botón de forma segura
    const btn = event ? event.target : document.querySelector('button[onclick="saveCompleteInspection()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
    btn.disabled = true;

    // Preparar datos para enviar
    const data = {
        department_id: department,
        title: title,
        fields: currentFields.map(field => ({
            label: field.label,
            type: field.type
        })),
        questions: currentQuestions.map(question => ({
            text: question.text,
            ref: question.ref || null
        }))
    };

    // Enviar por AJAX
    fetch('ajax/guardar_inspecciones_creadas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Error desconocido al guardar');
        }

        alert('Inspección guardada correctamente');
        
        // Guardar el ID de inspección si viene en la respuesta
        if (data.inspection_id) {
            localStorage.setItem('lastInspectionId', data.inspection_id);
            console.log('ID de inspección guardada:', data.inspection_id);
        }
        
        // Actualizar la interfaz sin forzar errores
        try {
            updateInspectionsList();
        } catch (e) {
            console.warn('Error al actualizar lista:', e.message);
        }
        
        showView('mainView');
        clearForm();
    })
    .catch((error) => {
        console.error('Error en saveCompleteInspection:', error);
        alert('Error al guardar: ' + error.message);
    })
    .finally(() => {
        // Restaurar botón
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}// Función para cargar las inspecciones desde el servidor
async function loadInspections() {
    try {
        // Mostrar estado de carga
        const container = document.getElementById('inspectionsList');
        if (container) {
            container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando inspecciones...</p></div>';
        }

        // Hacer la petición al servidor
        const response = await fetch('ajax/inspecciones_tipos_informacion.php');
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const {success, data, message} = await response.json();

        if (!success) {
            throw new Error(message || 'Error al obtener inspecciones');
        }

        // Mapear los datos a la estructura que espera tu aplicación
        inspections = data.map(item => ({
            id: item.id,
            title: item.name,
            department: item.department_name,
            department_id: item.department_id,
            createdAt: '', // No viene en la respuesta
            fields: [],   // No viene en la respuesta
            questions: [] // No viene en la respuesta
        }));

        // Actualizar la vista
        updateInspectionsList();

    } catch (error) {
        console.error('Error al cargar inspecciones:', error);
        
        // Mostrar mensaje de error
        const container = document.getElementById('inspectionsList');
        if (container) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${error.message}
                    </div>
                </div>
            `;
        }
    }
}

// Función para actualizar la lista en el DOM (versión mejorada)
function updateInspectionsList() {
    const container = document.getElementById('inspectionsList');
    if (!container) return;

    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = inspections.length === 0 ? 'flex' : 'none';
    }

    if (inspections.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-4">No hay inspecciones registradas</div>';
        return;
    }

    // Crear tabla con los datos
    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        
                    </tr>
                </thead>
                <tbody>
    `;

    inspections.forEach(inspection => {
        html += `
            <tr>
                <td>${inspection.id}</td>
                <td>${escapeHtml(inspection.title)}</td>
                <td>
                    <span class="badge bg-primary">
                        ${escapeHtml(inspection.department)}
                    </span>
                </td>
                
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = html;
}

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cargar las inspecciones al iniciar
document.addEventListener('DOMContentLoaded', loadInspections);

// Función para recargar las inspecciones cuando sea necesario
function refreshInspections() {
    inspections = []; // Limpiar el array
    loadInspections();
}
    </script>
</body>
</html>