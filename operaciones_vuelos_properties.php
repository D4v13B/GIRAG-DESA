<?php


$qsql = "SELECT * FROM operaciones_vuelos_glosario_secciones ovgs
LEFT JOIN operaciones_vuelos_glosario ovg ON ovg.ovgs_id = ovgs.ovgs_id";

$rs = mysql_query($qsql);

$data = [];

$opvu_id = 1;

while ($row = mysql_fetch_assoc($rs)) {

    $tab         = $row['ovgs_tab'];
    $seccion     = $row['ovgs_seccion'] ?: "";
    $subseccion  = $row['ovgs_subseccion'] ?: "";

    // Inicializar estructuras si no existen
    if (!isset($data[$tab])) {
        $data[$tab] = [];
    }

    if (!isset($data[$tab][$seccion])) {
        $data[$tab][$seccion] = [];
    }

    if (!isset($data[$tab][$seccion][$subseccion])) {
        $data[$tab][$seccion][$subseccion] = [];
    }

    // Si esta fila tiene campo definido, agregarlo
    if (!empty($row['opvg_id'])) {
        $data[$tab][$seccion][$subseccion][] = [
            "opvg_id"  => (int)$row['opvg_id'],
            "etiqueta" => $row['opvg_etiqueta'],
            "tipo"     => (int)$row['opvg_tipo'],
            "ovgs_id"  => (int)$row['ovgs_id'],
            "opvg_name" => $row['opvg_name']
        ];
    }
}

// echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

?>

<style>
    .text-custom-purple {
        color: #7c3aed !important;
    }

    .bg-custom-light-purple {
        background-color: #f3f0fd !important;
    }

    .bg-custom-green {
        background-color: #10b981 !important;
    }

    .bg-custom-green:hover {
        background-color: #059669 !important;
    }

    /* Adaptación del estilo de pestaña activa de Tailwind a Bootstrap nav-tabs */
    .nav-tabs .nav-item .nav-link.active {
        border-bottom: 3px solid #7c3aed !important;
        /* Púrpura */
        color: #7c3aed !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        background-color: transparent !important;
    }

    /* Colores para Badges */
    .badge-draft {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-open {
        background-color: #d1fae5;
        color: #065f46;
    }

    /* Estilo para la barra superior (Comentado en el original, pero adaptable) */
    .header-bg {
        background-color: #7c3aed;
        /* Púrpura oscuro para la barra superior */
    }
</style>

<div class="bg-white p-5">
    <div class="text-muted mb-3">
        <span class="text-primary cursor-pointer" onclick="showListView()">Reporte</span> / <span id="reporte-id">REP-12258</span>
    </div>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="d-flex mb-3 mb-md-0">
            <button class="btn btn-sm text-white font-weight-medium shadow-sm btn-success mr-2">EDITAR</button>
            <button class="btn btn-sm btn-outline-secondary font-weight-medium shadow-sm mr-2">CREAR</button>

            <!-- <div class="dropdown mr-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle font-weight-medium shadow-sm" type="button" id="dropdownImprimir" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Imprimir
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownImprimir">
                                <a class="dropdown-item small" href="#">Imprimir A</a>
                                <a class="dropdown-item small" href="#">Imprimir B</a>
                            </div>
                        </div> -->

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle font-weight-medium shadow-sm" type="button" id="dropdownAccion" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Acción
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownAccion">
                    <a class="dropdown-item small" href="#">...</a>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center small text-muted">
            <span class="font-weight-medium badge badge-warning py-1 px-3 mr-3">BORRADOR</span>
        </div>
    </div>

    <div class="row border p-3 rounded bg-light mb-4">
        <div class="col-md-4 mb-3 mb-md-0 small">
            <p class="mb-1"><span class="text-muted">Número de Reporte:</span> <span class="font-weight-medium text-dark">REP-12258</span></p>
            <p class="mb-1"><span class="text-muted">Escoja el Vuelo:</span> <span class="font-weight-medium text-primary">LAN CARGO L7-1813 BOG 16-09-2025</span></p>
            <p class="mb-1"><span class="text-muted">Tipo de Servicio:</span> <span class="font-weight-medium">Escala Comercial Carguero</span></p>
            <p class="mb-1"><span class="text-muted">Origen:</span> <span class="font-weight-medium">MIA</span></p>
            <p class="mb-1"><span class="text-muted">Destino:</span> <span class="font-weight-medium">BOG</span></p>
            <p class="mb-0"><span class="text-muted">Fecha del Vuelo:</span> <span class="font-weight-medium">16/09/2025</span></p>
        </div>
        <div class="col-md-4 mb-3 mb-md-0 small">
            <p class="mb-1"><span class="text-muted">Vuelo:</span> <span class="font-weight-medium text-primary">L7-1813</span></p>
            <p class="mb-1"><span class="text-muted">Línea Aérea:</span> <span class="font-weight-medium">LAN CARGO</span></p>
            <p class="mb-1"><span class="text-muted">Matricula:</span> <span class="font-weight-medium">CCBDC</span></p>
            <p class="mb-0"><span class="text-muted">Tipo de Aeronave:</span> <span class="font-weight-medium">B767/3</span></p>
        </div>
        <div class="col-md-4 small d-flex flex-column justify-content-between">
            <div>
                <p class="mb-1"><span class="text-muted">ETA:</span> <span class="font-weight-medium">16/09/2025 07:25:03 AM</span></p>
                <p class="mb-1"><span class="text-muted">ETD:</span> <span class="font-weight-medium">16/09/2025 08:35:03 AM</span></p>
                <p class="mb-1"><span class="text-muted">Sup. Rampa:</span> <span class="font-weight-medium">JORGE PEREA</span></p>
            </div>
        </div>
    </div>

    <form id="form-vuelo" method="POST">
        <?php echo renderGlosarioTemplate($data) ?>
        <input type="hidden" name="opvu_id" value="<?php echo $opvu_id; ?>" />
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

    <!-- 
        <div class="tab-pane fade" id="tab-observaciones" role="tabpanel" aria-labelledby="observaciones-tab">
            <h5 class="font-weight-semibold text-dark mb-3">Observaciones de la Operación</h5>
            <div class="small">
                <p class="text-muted">Aun no hay observaciones registradas.</p>
            </div>
        </div> -->

</div>
</div>


<script>
    function showDetailView(reporteId) {
        reporteIdEl.textContent = reporteId
        $('#carga-tab').tab('show');
        setTimeout(function() {
            $('#sub-carga-tab').tab('show');
        }, 10);
    }

    $(document).ready(function() {

        $('#form-vuelo').on('submit', function(e) {
            e.preventDefault(); // Evita que se recargue la página

            var formData = $(this).serialize(); // Serializa todos los inputs

            $.ajax({
                url: './ajax/operaciones_vuelos.php', // misma página PHP, puede cambiar a un endpoint específico
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#mensaje-guardar').html('<div class="alert alert-success">Datos guardados correctamente</div>');
                    } else {
                        $('#mensaje-guardar').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#mensaje-guardar').html('<div class="alert alert-danger">Error en la solicitud AJAX: ' + error + '</div>');
                }
            });

        });

    });
</script>