<?php
// error_reporting(0);
include 'funciones_ui.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vuelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .ui-menu-item {
            cursor: pointer;
        }
        .ui-autocomplete {
            z-index: 9999 !important;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .ui-menu {
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .ui-menu-item-wrapper:hover {
            background-color: #e9ecef;
            color: #000;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5 bg-white min-vh-100 d-flex justify-content-center align-items-start">
        <div class="p-4 col-12 col-md-10">
            <h2 class="fw-bold text-center mb-4">Vuelos</h2>

            <ul class="nav nav-tabs mb-4" id="vuelosTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ver-tab" data-bs-toggle="tab" data-bs-target="#ver" type="button" role="tab">Ver Vuelos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subir-tab" data-bs-toggle="tab" data-bs-target="#subir" type="button" role="tab">Subir Vuelos</button>
                </li>
            </ul>

            <div class="tab-content" id="vuelosTabContent">
                <div class="tab-pane fade show active" id="ver" role="tabpanel">
                    <div class="text-center mb-4">
                        <button id="btn-ver-vuelos" class="btn btn-primary me-2">Ver Vuelos</button>
                        <button id="btn-crear-vuelo" class="btn btn-success">Crear Vuelo</button>
                    </div>
                    <div id="tabla-contenedor" class="table-responsive d-none">
                        <table class="table table-hover table-striped table-bordered align-middle shadow-sm rounded-3 overflow-hidden">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th class="text-uppercase">Aerolínea</th>
                                    <th class="text-uppercase">Salida</th>
                                    <th class="text-uppercase">Entrada</th>
                                    <th class="text-uppercase">Código</th>
                                </tr>
                                <tr>
                                    <th><select id="filtro-aerolinea" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                        </select></th>
                                    <th><select id="filtro-salida" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                        </select></th>
                                    <th><select id="filtro-entrada" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                        </select></th>
                                    <th><input type="text" id="filtro-codigo" class="form-control form-control-sm" placeholder="Buscar código"></th>
                                </tr>
                            </thead>
                            <tbody id="tabla-vuelos" class="text-center">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="subir" role="tabpanel">
                    <div class="alert alert-primary border-0 rounded-3 mt-3">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-file-excel me-2"></i>Formato del archivo Excel [.xls, .xlsx]:
                        </h6>
                        <p class="mb-0 small">
                            <strong>Columnas:</strong> AEROLÍNEA | AEROPUERTO DE SALIDA | AEROPUERTO DE ENTRADA | CÓDIGO DE VUELO
                        </p>
                        <small>- Sin encabezados -</small>
                    </div>
                    <form id="form-archivo" enctype="multipart/form-data">
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Selecciona un archivo Excel</label>
                            <input class="form-control" type="file" id="excel" name="excel" accept=".xlsx,.xls" required>
                        </div>
                        <div class="text-center">
                            <button type="button" id="cargar-archivo" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCrearVuelo" tabindex="-1" aria-labelledby="crearVueloLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="crearVueloLabel">Crear Vuelo Individual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="form-crear-vuelo">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Aerolínea</label>
                                <input type="text" class="form-control" id="aerolinea_input">
                                <input type="hidden" class="form-control" name="aerolinea" id="aerolinea_hidden">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aeropuerto de Salida</label>
                                <input type="text" class="form-control" id="salida_input">
                                <input type="hidden" class="form-control" name="salida" id="salida_hidden">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aeropuerto de Entrada</label>
                                <input type="text" class="form-control" id="entrada_input">
                                <input type="hidden" class="form-control" name="entrada" id="entrada_hidden">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código de Vuelo</label>
                                <input type="text" class="form-control" name="codigo" required>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-5">Registrar Vuelo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Botón para mostrar tabla
        $("#btn-ver-vuelos").on("click", function() {
            $("#tabla-contenedor").removeClass("d-none");
            cargarVuelos();
        });

        let modalCrearVueloInstance;
        $("#btn-crear-vuelo").on("click", function() {
            modalCrearVueloInstance = new bootstrap.Modal(document.getElementById("modalCrearVuelo"));
            modalCrearVueloInstance.show();
        });

        // Cierra el modal con el botón X
        document.querySelector("#modalCrearVuelo .btn-close").addEventListener("click", function() {
            modalCrearVueloInstance.hide();
        });

        // Autocompletar para la Aerolínea
        $("#aerolinea_input").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "obtener_aerolineas.php",
                    dataType: "json",
                    data: {
                        codigo_aerolineas: request.term
                    },
                    success: function(data) {
                        response(data);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en autocompletar aerolíneas:", error);
                        response([]);
                    }
                });
            },
            minLength: 2, // Puedes ajustar esto a 1 si quieres
            select: function(event, ui) {
                // Asigna el valor del campo visible
                $("#aerolinea_input").val(ui.item.value);
                // Asigna el ID del campo oculto que se enviará al servidor
                $("#aerolinea_hidden").val(ui.item.id);
                return false; // Evita que se propague el evento
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + item.label + "</div>")
                .appendTo(ul);
        };

        // Autocompletar para Aeropuerto de Salida
        $("#salida_input").autocomplete({
    source: function(request, response) {
        $.ajax({
            url: "obtener_aeropuertos.php",
            dataType: "json",
            data: {
                codigo_aeropuerto: request.term
            },
            success: function(data) {
                response(data);
            },
            error: function(xhr, status, error) {
                console.error("Error en autocompletar aeropuertos de salida:", error);
                console.error("Respuesta:", xhr.responseText);
                response([]);
            }
        });
    },
    minLength: 2,
    select: function(event, ui) {
        $("#salida_input").val(ui.item.value);
        $("#salida_hidden").val(ui.item.id);
        return false;
    }
}).autocomplete("instance")._renderItem = function(ul, item) {
    return $("<li>")
        .append("<div>" + item.label + "</div>")
        .appendTo(ul);
};

        // Autocompletar para Aeropuerto de Entrada
        $("#entrada_input").autocomplete({
    source: function(request, response) {
        $.ajax({
            url: "obtener_aeropuertos.php",
            dataType: "json",
            data: {
                codigo_aeropuerto: request.term
            },
            success: function(data) {
                response(data);
            },
            error: function(xhr, status, error) {
                console.error("Error en autocompletar aeropuertos de entrada:", error);
                console.error("Respuesta:", xhr.responseText);
                response([]);
            }
        });
    },
    minLength: 2,
    select: function(event, ui) {
        $("#entrada_input").val(ui.item.value);
        $("#entrada_hidden").val(ui.item.id);
        return false;
    }
}).autocomplete("instance")._renderItem = function(ul, item) {
    return $("<li>")
        .append("<div>" + item.label + "</div>")
        .appendTo(ul);
};

        // Envío del formulario para crear un vuelo
        $("#form-crear-vuelo").on("submit", function(e) {
            e.preventDefault();
            const datos = $(this).serialize();

            $.post("ajax/vuelos_datos.php", datos, function(res) {
                if (res.success) {
                    alert(res.message || "Vuelo creado exitosamente");
                    $("#form-crear-vuelo")[0].reset();
                    modalCrearVueloInstance.hide();
                    cargarVuelos();
                } else {
                    alert(res.message || "Error desconocido");
                }
            }, 'json')
            .fail(function(xhr, status, error) {
                console.error("Error AJAX:", error);
                console.error("Respuesta:", xhr.responseText);
                alert("Error de conexión: " + error);
            });
        });

        // Subir archivo Excel
        $("#cargar-archivo").on("click", function() {
            const archivoInput = $("#form-archivo input[type='file']");
            if (!archivoInput.val() || archivoInput[0].files.length === 0) {
                alert("Por favor, selecciona un archivo antes de continuar.");
                return;
            }
            const datos = new FormData($("#form-archivo")[0]);
            $.ajax({
                url: "ajax/cargar-vuelos.php",
                method: "POST",
                data: datos,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    // console.log(res);
                    if (res.success) {
                        alert(res.message);
                        $("#form-archivo")[0].reset();
                    } else {
                        alert(res.message || "Error al registrar los vuelos");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX:", xhr.responseText);
                    alert("Error al procesar el archivo: " + error);
                }
            });
        });

        // Cargar tabla de vuelos
        function cargarVuelos() {
            $.get("ajax/vuelos_datos.php", function(datos) {
                let html = '';
                const aerolineas = new Set();
                const salidas = new Set();
                const entradas = new Set();

                datos.forEach(vuelo => {
                    html += `
                        <tr>
                            <td>${vuelo.aerolinea}</td>
                            <td>${vuelo.salida}</td>
                            <td>${vuelo.entrada}</td>
                            <td>${vuelo.codigo}</td>
                        </tr>
                    `;
                    aerolineas.add(vuelo.aerolinea);
                    salidas.add(vuelo.salida);
                    entradas.add(vuelo.entrada);
                });

                $("#tabla-vuelos").html(html);
                llenarSelect("#filtro-aerolinea", aerolineas);
                llenarSelect("#filtro-salida", salidas);
                llenarSelect("#filtro-entrada", entradas);
            }, 'json');
        }

        function llenarSelect(selector, valores) {
            const $select = $(selector);
            $select.html('<option value="">Todas</option>');
            [...valores].sort().forEach(v => {
                $select.append(`<option value="${v}">${v}</option>`);
            });
        }

        function aplicarFiltros() {
            const filtroAerolinea = $("#filtro-aerolinea").val().toLowerCase();
            const filtroSalida = $("#filtro-salida").val().toLowerCase();
            const filtroEntrada = $("#filtro-entrada").val().toLowerCase();
            const filtroCodigo = $("#filtro-codigo").val().toLowerCase();

            $("#tabla-vuelos tr").filter(function() {
                const tds = $(this).find("td");
                const aerolinea = tds.eq(0).text().toLowerCase();
                const salida = tds.eq(1).text().toLowerCase();
                const entrada = tds.eq(2).text().toLowerCase();
                const codigo = tds.eq(3).text().toLowerCase();

                const match =
                    (!filtroAerolinea || aerolinea === filtroAerolinea) &&
                    (!filtroSalida || salida === filtroSalida) &&
                    (!filtroEntrada || entrada === filtroEntrada) &&
                    (!filtroCodigo || codigo.includes(filtroCodigo));

                $(this).toggle(match);
            });
        }

        // Eventos
        $("#filtro-aerolinea, #filtro-salida, #filtro-entrada").on("change", aplicarFiltros);
        $("#filtro-codigo").on("keyup", aplicarFiltros);
    </script>
    <script>
// Esta funcion la agregue para que deje de joder el Jquery
// Función para centrar un elemento en la pantalla
jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
    this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
    return this;
}
</script>
</body>
</html>