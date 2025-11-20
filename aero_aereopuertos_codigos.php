

<script>
function crear() {
    $('#result').load('aero_aereopuertos_codigos_crear.php',
        {
            'i_pais_id': $('#i_pais_id').val(),
            'i_aeco_codigo': $('#i_aeco_codigo').val(),
            'i_aeco_nombre': $('#i_aeco_nombre').val()
        },
        function(){
            $('#modal').hide('slow');
            $('#overlay').hide();
            mostrar();
        }
    );
}

function modificar() {
    $('#result').load('aero_aereopuertos_codigos_modificar.php?id=' + $('#h2_id').val(),
        {
            'm_aeco_id': $('#m_aeco_id').val(),
            'm_pais_id': $('#m_pais_id').val(),
            'm_aeco_codigo': $('#m_aeco_codigo').val(),
            'm_aeco_nombre': $('#m_aeco_nombre').val()
        },
        function(){
            $('#modal2').hide('slow');
            $('#overlay2').hide();
            mostrar();
        }
    );
}

function borrar(id) {
    if (confirm('¿Está seguro?')) {
        $('#result').load('aero_aereopuertos_codigos_borrar.php?id=' + id, function() {
            mostrar(); // refresca la lista
        });
    }
}


function editar(id) {
    $('#modal2').show();
    $('#overlay2').show();
    $('#h2_id').val(id);
    $.get('aero_aereopuertos_codigos_datos.php?id=' + id, function(data){
        var resp = data;
        r_array = resp.split('||');
        $('#m_pais_id').val(r_array[1]);
        $('#m_aeco_codigo').val(r_array[2]);
        $('#m_aeco_nombre').val(r_array[3]);
    });
}

function mostrar() {
    $('#datos_mostrar').load('aero_aereopuertos_codigos_mostrar.php?nochk=jjjlae222'
        + "&f_pais_id=" + $('#f_pais_id').val()
        + "&f_aeco_codigo=" + $('#f_aeco_codigo').val()
        + "&f_aeco_nombre=" + $('#f_aeco_nombre').val()
    );
}

// Lógica de carga de archivo
$("#cargar-archivo").on("click", function() {
    const datos = new FormData($("#form-archivo")[0]);
    $.ajax({
        url: "ajax/cargar-destino-final.php",
        method: "POST",
        data: datos,
        processData: false,
        contentType: false,
        success: res => {
            console.log(res);
            if (res.includes('{"success":true}')) {
                alert("Destinos registrados correctamente");
                $("#form-archivo")[0].reset();
                $('#modal3').hide('slow');
                $('#overlay3').hide();
                mostrar();
            } else {
                alert("Error al registrar destinos, formato Inválido");
            }
        },
        error: function() {
            alert("Error al procesar el archivo");
        },
        complete: function() {
            console.log("lesgoo");
        }
    });
});
</script>

<div id='separador'>
<table width='' class='filtros'>
    <tr>
        <?php echo catalogo('paises', 'Pais', 'pais_nombre', 'f_pais_id', 'pais_id', 'pais_nombre', '0', '1', '150');?>
        <?php echo entrada('input', 'Codigo de aereouerto', 'f_aeco_codigo', '150')?>
        <?php echo entrada('input', 'Nombre de aereopuerto', 'f_aeco_nombre', '150')?>
    </tr>
    <tr>
        <td class='tabla_datos'>
            <div id='b_mostrar'>
                <a href='javascript:mostrar()' class='botones'>Mostrar</a>
            </div>
        </td>
        <td>
            <div id='dmodal' style='text-align:right'>
                <a href='#' class='botones'>Nuevo</a>
            </div>
        </td>
        <td>
            <div id='dmodal3' style='text-align:right'>
                <a href='#' class='botones'>Cargar Excel</a>
            </div>
        </td>
    </tr>
</table>
</div>

<div id='columna6'>
    <div id='datos_mostrar'></div>
</div>

<div id='overlay' class='overlay'></div>
<div id='modal' class='modal-custom'>
    <div class='modal-header'>
        <h2 class="text-white">Nuevo Aeropuerto</h2>
        <a href='#' id='close' class='close-btn'>&times;</a>
    </div>
    <div id='content'>
        <table>
            <tr>
                <?php echo catalogo('paises', 'Pais', 'pais_nombre', 'i_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
            </tr>
            <tr>
                <?php echo entrada('input', 'Codigo de aereouerto', 'i_aeco_codigo', '150');?>
            </tr>
            <tr>
                <?php echo entrada('input', 'Nombre de aereopuerto', 'i_aeco_nombre', '150');?>
            </tr>
            <tr>
                <td colspan=2><a href='javascript:crear()' class='botones'>Crear</a></td>
            </tr>
        </table>
    </div>
</div>

<div id='overlay2' class='overlay'></div>
<div id='modal2' class='modal-custom'>
    <div class='modal-header'>
        <h2>Modificar</h2>
        <a href='javascript:void(0);' id='close2' class='close-btn'>&times;</a>
    </div>
    <div id='content2'>
        <input type=hidden id=h2_id>
        <table>
            <tr>
                <?php echo catalogo('paises', 'Pais', 'pais_nombre', 'm_pais_id', 'pais_id', 'pais_nombre', '0', '0', '150');?>
            </tr>
            <tr>
                <?php echo entrada('input', 'Codigo de aereouerto', 'm_aeco_codigo', '150');?>
            </tr>
            <tr>
                <?php echo entrada('input', 'Nombre de aereopuerto', 'm_aeco_nombre', '150');?>
            </tr>
            <tr>
                <td colspan=2><a href='javascript:modificar()' class='botones'>Modificar</a></td>
            </tr>
        </table>
    </div>
</div>

<div id='overlay3' class='overlay'></div>
<div id='modal3' class='modal-custom'>
    <div class='modal-header'>
        <h2 class="text-white">Registrar</h2>
        <a href='#' id='close3' class='close-btn'>&times;</a>
    </div>
    <div id='content3'>
        <div class="alert-info-custom">
            <h6><i class="fas fa-file-excel me-2"></i>Formato del archivo Excel [.xls,.xlsx]:</h6>
            <p><strong>Columnas:</strong> PAIS | CODIGO DE AEROPUERTO | NOMBRE DE AEROPUERTO</p>
            <small>-Sin encabezados-</small>
        </div>
        
        <form id="form-archivo" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel" class="form-label">Selecciona un archivo Excel</label>
                <input class="input-file-custom" type="file" id="excel" name="excel" accept=".xlsx,.xls" required>
            </div>
            <div style="text-align:center; margin-top: 20px;">
                <button type="button" id="cargar-archivo" class="btn-primary-custom">
                    <i class="fas fa-upload me-2"></i>Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<div id=result></div>
<script>
$(document).ready(function() {

    // === Modal de creación ===
    $('#dmodal a').on('click', function(e) {
        e.preventDefault();

        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var modalHeight = $('#modal').outerHeight();
        var top = scrollTop + (windowHeight - modalHeight) / 2;

        $('#modal').css('top', top + 'px');
        $('#modal').show();
        $('#overlay').show();
    });

    $('#close').on('click', function(e) {
        e.preventDefault();
        $('#modal').hide('slow');
        $('#overlay').hide();
    });

    // === Modal de modificación ===
    $('#close2').on('click', function(e) {
        e.preventDefault();
        $('#modal2').hide('slow');
        $('#overlay2').hide();
    });

    // Nota: la lógica de centrado de modal2 está en la función editar()

    // === Modal de carga de Excel ===
    $('#dmodal3 a').on('click', function(e) {
        e.preventDefault();

        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var modalHeight = $('#modal3').outerHeight();
        var top = scrollTop + (windowHeight - modalHeight) / 2;

        $('#modal3').css('top', top + 'px');
        $('#modal3').show();
        $('#overlay3').show();
    });

    $('#close3').on('click', function(e) {
        e.preventDefault();
        $('#modal3').hide('slow');
        $('#overlay3').hide();
    });

    // === Carga del archivo Excel ===
    $("#cargar-archivo").on("click", function() {
        const datos = new FormData($("#form-archivo")[0]);
        $.ajax({
            url: "ajax/cargar-destino-final.php",
            method: "POST",
            data: datos,
            processData: false,
            contentType: false,
            success: res => {
                console.log(res);
                if (res.includes('{"success":true}')) {
                    alert("Destinos registrados correctamente");
                    $("#form-archivo")[0].reset();
                    $('#modal3').hide('slow');
                    $('#overlay3').hide();
                    mostrar();
                } else {
                    alert("Error al registrar destinos, formato Inválido");
                }
            },
            error: function() {
                alert("Error al procesar el archivo");
            },
            complete: function() {
                console.log("lesgoo");
            }
        });
    });
});

// === Centrado dinámico en editar() ===
function editar(id) {
    $('#h2_id').val(id);
    $.get('aero_aereopuertos_codigos_datos.php?id=' + id, function(data){
        var r_array = data.split('||');
        $('#m_pais_id').val(r_array[1]);
        $('#m_aeco_codigo').val(r_array[2]);
        $('#m_aeco_nombre').val(r_array[3]);

        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var modalHeight = $('#modal2').outerHeight();
        var top = scrollTop + (windowHeight - modalHeight) / 2;

        $('#modal2').css('top', top + 'px');
        $('#modal2').show();
        $('#overlay2').show();
    });
}
</script>
<style>
/* Estilos para los Modals */
.overlay {
    position: fixed; /* <-- CAMBIO RECOMENDADO */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    display: none;
}
.modal-custom {
    position: absolute; /* CAMBIADO DE fixed A absolute */
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 1001;
    display: none;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    box-sizing: border-box;
}


.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
}
.close-btn {
    font-size: 1.8rem;
    cursor: pointer;
    color: #888;
    text-decoration: none;
}
.close-btn:hover {
    color: #000;
}
.form-group {
    margin-bottom: 15px;
}
.btn-primary-custom {
    display: inline-block;
    background-color: #007bff;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s;
    border: none;
    cursor: pointer;
    width: 100%;
    font-size: 1.25rem;
    font-weight: 500;
}
.btn-primary-custom:hover {
    background-color: #0056b3;
}
.alert-info-custom {
    background-color: #d9e9f8ff;
    border: 1px solid #a4c2e0ff;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.alert-info-custom h6 {
    margin-top: 0;
    font-weight: 600;
}
.alert-info-custom p {
    margin-bottom: 5px;
}
.alert-info-custom small {
    display: block;
    margin-top: 5px;
}
.input-file-custom {
    display: block;
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

</style>