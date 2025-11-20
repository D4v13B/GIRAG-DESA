<?php
include_once "conexion.php";
// Tomamos el id del usuario
$usuaID = $_SESSION['login_user'];

//Tomar seleccion
$sql = "SELECT * FROM inspecciones_seleccion";
$opciones = mysql_query($sql);
// Tomar las preguntas, para el formulario1
$sql = "SELECT * FROM inspecciones_preguntas WHERE inti_id = '1'";
$preguntas1 = mysql_query($sql);
$sql = "SELECT * FROM inspecciones_tipo_operacion";
$operaciones = mysql_query($sql);

?>

<style>
    /* Estilos para centrar el div y el borde */
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        background-color: white;
    }

    .form-container {
        text-align: center;
        width: 100%;
        max-width: 600px;
        /* Máximo ancho */
        margin: auto;
    }

    h1 {
        font-size: 20px;
        /* Tamaño del título más pequeño */
    }

    .formulario {
        display: none;
        margin-top: 20px;
    }

    form {
        text-align: center;
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"] {
        margin-bottom: 10px;
    }

    .text-justify {
        text-align: justify;
        /* Justificar texto */
    }

    /* Estilos personalizados para el select */
    select {
        width: 100%;
        padding: 10px;
        border: 2px solid #ccc;
        border-radius: 10px;
        background-color: #f9f9f9;
        font-size: 16px;
        color: #333;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url('https://cdn-icons-png.flaticon.com/512/60/60995.png');
        background-repeat: no-repeat;
        background-position: right 10px top 50%;
        background-size: 20px;
    }

    /* Añadir estilos hover y focus */
    select:hover {
        border-color: #888;
    }

    select:focus {
        outline: none;
        border-color: #555;
    }

    /* Responsividad: ajustes para pantallas más pequeñas */
    @media (max-width: 576px) {
        .form-container {
            width: 100%;
        }
    }

    /* Estilo para ajustar el tamaño de letra en móviles */
    @media (max-width: 576px) {
        table {
            font-size: 0.9em;
            /* Reducir el tamaño de la fuente */
        }

        td,
        th {
            text-align: justify;
            /* Justificar el texto */
        }

        .form-container {
            font-size: 0.8em;
            /* Tamaño de letra más pequeño para dispositivos móviles */
        }
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    #inputs-container .form-group {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    #inputs-container input,
    #inputs-container textarea {
        flex-grow: 1;
        /* Hace que los inputs se expandan para ocupar el espacio disponible */
    }

    #inputs-container .form-row {
        text-align: center;
        /* Centra los elementos horizontalmente */
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    #addBtn {
        margin-top: 20px;
    }
</style>

<body>
    <div class="container-fluid" style="background-color: white; width: 100%;">
        <div class="form-container">
            <h1>Gestión de Inspecciones</h1>

            <!-- Botón Agregar Inspección con ícono -->
            <button type="button" class="btn btn-primary mb-2 mr-2" data-toggle="modal" data-target="#inspectionModal">
                <i class="fas fa-plus-circle"></i> Agregar Inspección
            </button>

            <!-- Botón Ver Inspecciones con ícono -->
            <button type="button" class="btn btn-primary mb-2" id="verInspeccionesBtn">
                <i class="fas fa-eye"></i> Ver Inspecciones
            </button>
        </div>
    </div>

    <!-- Contenedor de la tabla de inspecciones -->
    <div id="inspeccionesTableContainer" style="display: none; padding: 10px; margin: 0 auto; max-width: 100%;">
        <!-- <div class="search-container mt-3 mb-4">
    <div class="input-group" style="flex-wrap: nowrap; margin: 0 auto; max-width: 500px;"> 
        <input type="text" class="form-control" placeholder="Buscar inspecciones..." style="flex-grow: 1; min-width: 200px;"> 
        <div class="input-group-append">
            <button type="button" class="btn btn-secondary" style="width: 100px;"> 
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>
</div> -->



        <table id="inspeccionesTable" class="table table-striped" style="background-color: white; margin: 20px auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Realizada por</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Reporte</th>
                    <th>Completado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Los datos de las inspecciones se insertarán aquí -->
            </tbody>
        </table>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="inspectionModal" tabindex="-1" role="dialog" aria-labelledby="inspectionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inspectionModalLabel">Seleccionar Inspección</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="inspectionForm">
                        <div class="form-group">
                            <label for="inspectionType">Tipo de Inspección:</label>
                            <select class="form-control" id="inspectionType" name="inti_id" onchange="mostrarInputs()">
                                <option value="">Seleccione una opción</option>
                                <?php
                                // Consulta para obtener los tipos de inspecciones
                                $query = "SELECT inti_id, inti_nombre FROM inspecciones_tipos";
                                $result = mysql_query($query) or die("Error en la consulta: " . mysql_error());

                                // Verificar si hay resultados
                                if (mysql_num_rows($result) > 0) {
                                    // Recorrer los resultados y crear las opciones del select
                                    while ($row = mysql_fetch_assoc($result)) {
                                        echo '<option value="' . $row['inti_id'] . '">' . $row['inti_nombre'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No se encontraron tipos de inspecciones</option>';
                                }
                                ?>
                            </select>
                        </div>

                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" onclick="inspeccion()">Guardar Inspección</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formSelector = document.getElementById('formSelector');
            const formulariosContainer = document.getElementById('formularios');

            formSelector.addEventListener('change', function() {
                console.log("Valor seleccionado: " + this.value);

                if (this.value) {
                    formulariosContainer.style.display = 'block';
                } else {
                    formulariosContainer.style.display = 'none';
                }

                const formularios = document.querySelectorAll('.formulario');
                formularios.forEach(function(form) {
                    form.style.display = 'none';
                });

                const selectedForm = document.getElementById('form' + this.value);
                if (selectedForm) {
                    selectedForm.style.display = 'block';
                }
            });
        });

        function mostrarInputs() {
            const selectedValue = document.getElementById("inspectionType").value;
            const inputsContainer = document.getElementById("inputs-container");
            const allInputGroups = Array.from(document.querySelectorAll(".conditional-input"));

            // Ocultar todos los grupos de inputs
            allInputGroups.forEach(inputGroup => {
                inputGroup.style.display = "none";
            });

            // Mostrar el contenedor de inputs si se seleccionó un tipo de inspección
            if (selectedValue) {
                inputsContainer.style.display = "block";

                // Mostrar el grupo de inputs correspondiente
                const inputGroupToShow = document.getElementById("inputGroup" + selectedValue);
                if (inputGroupToShow) {
                    inputGroupToShow.style.display = "block";
                }
            } else {
                inputsContainer.style.display = "none"; // Ocultar el contenedor si no hay selección
            }
        }



        // FUNCIÓN PARA SELECCIONAR EL TIPO DE INSPECCIÓN Y GUARDAR EL ID
        function inspeccion() {
            const datos = new FormData($("#inspectionForm")[0]);

            console.log([...datos])
            $.ajax({
                url: "ajax/registro_inspecciones.php",
                method: "POST",
                contentType: false,
                processData: false,
                data: datos,
                success: function(res) {
                    alert("Mensaje enviado exitosamente");
                    const insp_id = res.insp_id;
                    $("#insp_id").val(insp_id);
                    console.log(insp_id);
                    if (insp_id) {
                        window.location.href = "index.php?p=inspecciones_detalles&insp_id=" + insp_id;
                    }
                },

            });
        }

        // Tabla de las inspecciones
        document.addEventListener('DOMContentLoaded', function() {
            const verInspeccionesBtn = document.getElementById('verInspeccionesBtn');
            const inspeccionesTableContainer = document.getElementById('inspeccionesTableContainer');
            const inspeccionesTable = document.getElementById('inspeccionesTable');

            // Remover el onclick del botón en HTML y añadirlo aquí
            verInspeccionesBtn.addEventListener('click', function() {
                inspeccionesTableContainer.style.display = 'block';
                cargarInspecciones();
            });

        })

        //Muestra las inspecciones en la tabla
        function cargarInspecciones() {
            fetch('ajax/registro_inspecciones.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    const tbody = inspeccionesTable.querySelector('tbody');
                    tbody.innerHTML = ''; // Limpiar tabla

                    data.forEach(inspeccion => {
                        const row = tbody.insertRow();
                        // Determina el estado y el color de la celda "Completado"
                        const estadoCompletado = inspeccion.completado;
                        const colorEstado = estadoCompletado === 'No' ? 'red' : 'green';

                        row.innerHTML = `
                            <td>${inspeccion.insp_id}</td>
                            <td>${inspeccion.usua_id_inspeccion}</td>
                            <td>${inspeccion.insp_fecha}</td>
                            <td>${inspeccion.tipo_inspeccion}</td>
                            <td>
                                ${inspeccion.insp_reporte ? `
                                    <a href="../inspecciones/${inspeccion.insp_reporte}" target="_blank">
                                        ${inspeccion.insp_reporte}
                                    </a>
                                ` : ''}
                            </td>
                            <td>
                                <span style="color: ${colorEstado}; font-weight: bold;">
                                    ${estadoCompletado}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton_${inspeccion.insp_id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton_${inspeccion.insp_id}">
                                        <a class="dropdown-item" href="index.php?p=inspecciones_detalles&insp_id=${inspeccion.insp_id}">
                                            <i class="fa-solid fa-eye" style="color: green;"></i> Ver
                                        </a>
                                        ${inspeccion.completado !== 'Sí' ? `
                                            <a class="dropdown-item" href="index.php?p=editar_inspeccion&insp_id=${inspeccion.insp_id}">
                                                <i class="fa-solid fa-pen-to-square" style="color: blue;"></i> Completar
                                            </a>
                                        ` : ''}
                                        <a class="dropdown-item" href="#" onclick="eliminarInspeccion(${inspeccion.insp_id})">
                                            <i class="fa-solid fa-trash" style="color: red;"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </td>
                        `;
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        function eliminarInspeccion(insp_id) {
            if (confirm('¿Estás segura de que quieres eliminar esta inspección?')) {
                $.ajax({
                    url: 'inspeccion_borrar.php',
                    type: 'GET',
                    data: {
                        id_inspeccion: insp_id
                    },
                    success: function(response) {
                        if (response.trim() === 'ok') {
                            alert('Inspección eliminada exitosamente.');
                            cargarInspecciones();
                        } else {
                            alert('Error al eliminar: ' + response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        alert('Ocurrió un error al eliminar la inspección.');
                    }
                });
            }
        }
    </script>

</body>