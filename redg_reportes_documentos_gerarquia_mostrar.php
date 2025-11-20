<?php
include('conexion.php');
include('funciones.php');
session_start();
$user_check = $_SESSION['login_user'];


$f_redg_nombre = $_GET['f_redg_nombre'];
$f_redg_nivel = $_GET['f_redg_nivel'];
$f_redg_padre = $_GET['f_redg_padre'];
$where = '';
if ($f_redg_nombre != '' && $f_redg_nombre != 'null') $where .= "AND a.redg_nombre LIKE '%$f_redg_nombre%'";
if ($f_redg_nivel != '' && $f_redg_nivel != 'null') $where .= "AND a.redg_nivel = '$f_redg_nivel'";
if ($f_redg_padre != '' && $f_redg_padre != 'null') $where .= "AND a.redg_padre = '$f_redg_padre'";
$qsql = "select * from reportes_documentos_gerarquia a
WHERE 1=1
$where
order by redg_nombre 
";
$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i = 0;
$ruta = '<a href="javascript:abrir_carpeta(\'0\',\'0\')" class="boton-navegacion" style="text-decoration: none;">RAIZ</a>';
if ($f_redg_padre != '') {
    $arr_ruta = obtener_ruta($f_redg_padre);
    //echo "arreglo:" . count($arr_ruta);
    $arr_count = count($arr_ruta);
    while ($arr_count >= 0) {
        $ruta .= " > " . "$arr_ruta[$arr_count] ";
        $arr_count--;
    }
}
echo $ruta;
//saco el máximo valor del array
function obtener_ruta($padre)
{
    //en base a su padre puedo ir iendo para atrás obteniendo los padres de cada folder
    //saco el padre
    $j = 0;
    while ($padre != '') {
        $abuelo_nombre = obtener_valor("select redg_nombre from reportes_documentos_gerarquia where redg_id='$padre'", "redg_nombre");
        $abuelo_nivel = obtener_valor("select redg_nivel from reportes_documentos_gerarquia where redg_id='$padre'", "redg_nivel");
        $abuelo_id = obtener_valor("select redg_id from reportes_documentos_gerarquia where redg_id='$padre'", "redg_id");
        $padre = obtener_valor("select redg_padre from reportes_documentos_gerarquia where redg_id='$padre'", "redg_padre");
        if ($padre == 0) $padre = '';
        $abuelo_nombre = '<a href="javascript:abrir_carpeta(\'' . $abuelo_nivel . '\',\'' . $abuelo_id . '\');" class="boton-navegacion" style="text-decoration: none;">' . $abuelo_nombre . "</a>";
        $cadena_ruta[$j] = $abuelo_nombre;
        $j++;
    }
    return $cadena_ruta;
}
?>

<!-- FancyBox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />

<!-- FancyBox JS -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script src='jquery/sorter/tablesort.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.number.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.date.min.js'></script>
<script>
    $(function() {
        new Tablesort(document.getElementById('resultado'));
    });
</script>
<!-- Div fijo que contiene el checkbox y el botón -->
<div id="controlDiv" class="fixed-top p-3 text-center" style="display: none; border-bottom: 1px solid #ddd; background-color: rgba(0, 0, 0, 0.3);">
    <!-- Añade este atributo onclick directamente al botón en el HTML -->
    <button id="performAction" class="btn btn-success btn-sm fs-7" disabled onclick="procesarDocumentosSeleccionados()">Aceptar Documentos Seleccionados</button>
</div>


<div class='table-responsive table-striped table-bordered table-hover table-sm' style='text-align: center; align-items:center'>
    <table id='resultado' class=table align-middle>
        <thead class='thead-dark'>
            <tr>
                <TH style="width:30px"></TH>

                <th style="width:150px !important">Nombre</th>
                <th class="tabla_datos_titulo_trabajo"></th>
                <th class="tabla_datos_titulo_iconos" style="width:50px"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($i < $num) {
                $tipo = "fas fa-folder";
                $nivel = mysql_result($rs, $i, 'redg_nivel');
                $id = mysql_result($rs, $i, 'redg_id');
                $is_document = false; // Bandera para indicar si es un documento
            ?>
                <tr class='tabla_datos_tr'>
                    <td><i class="<?php echo $tipo ?>"></i></td>

                    <td class=tabla_datos style="text-align:left !important"><a href="javascript:abrir_carpeta('<?php echo $nivel ?>','<?php echo $id ?>')"><?php echo mysql_result($rs, $i, 'redg_nombre'); ?></td>
                    <TD>
                        <div></div>
                    </td>
                    <td class=tabla_datos_iconos>
                        <div Class='btn-group btn-group-sm btn_borrar_documento'>
                            <a class='btn btn_borrar_documento' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'redg_id'); ?>)' ;>
                                <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'>
                                    <path fill='#ad0000' d='M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z' />
                                </svg>
                            </a>
                        </div>

                    </td>
                </tr>

            <?php
                $i++;
            }


            // Ahora los archivos
            $sql = "SELECT * FROM reportes_documentos WHERE redg_id='$f_redg_padre' AND rede_id = 3";
            $referencia_global = mysql_fetch_assoc(mysql_query($sql));

            $qsql = "SELECT redo_id, redo_titulo, redo_descripcion FROM reportes_documentos WHERE redg_id='$f_redg_padre' order by redo_titulo";
            $rs = mysql_query($qsql);
            $num = mysql_num_rows($rs);
            $i = 0;

            while ($i < $num) {
                $redo_id = mysql_result($rs, $i, 'redo_id');

                // Obtener la referencia específica para cada documento
                $sql_referencia = "SELECT * FROM reportes_documentos WHERE redo_id='$redo_id' AND rede_id = 3";
                $referencia = mysql_fetch_assoc(mysql_query($sql_referencia));

                $tipo = "fas fa-file";
                $is_document = true; // Marcar como documento
            ?>
                <tr class='tabla_datos_tr'>
                    <td><i class="<?php echo $tipo ?>"></i></td>

                    <td class=tabla_datos style="text-align:left !important"><a href="index.php?p=reportes-detalles&id=<?php echo mysql_result($rs, $i, 'redo_id'); ?>" style="text-decoration:none;color:#000000"><?php echo mysql_result($rs, $i, 'redo_titulo'); ?></td>
                    <td>
                        <?php if (!empty($referencia["redo_ref"])) : ?>
                            <?php
                            // Archivo PDF
                            $archivo = $referencia["redo_ref"];
                            $enlace = 'ajax/visor-pdf.php?archivo=' . urlencode($archivo);
                            ?>
                            <div>
                                <a href="<?php echo htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                    <?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="tabla_datos_iconos">
    <div class="btn-group btn-group-sm">
        <div class="form-check form-check-inline">
            <input type="checkbox" class="form-check-input document-checkbox" data-redo-id="<?php echo $redo_id; ?>" style="margin-right: 5px;">
        </div>
        <a class="btn text-success" href="index.php?p=reportes-detalles&id=<?php echo mysql_result($rs, $i, 'redo_id'); ?>">
            <i class="fa-solid fa-eye"></i>
        </a>
        <a class="btn" href="javascript:borrar_archivo(<?php echo mysql_result($rs, $i, 'redo_id'); ?>)">
            <svg style="width: 22px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path fill="#ad0000" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
            </svg>
        </a>
        <a class="btn text-primary share-btn <?php echo empty($referencia["redo_ref"]) ? 'disabled' : ''; ?>" 
           data-file="<?php echo urlencode($referencia["redo_ref"] ?? ''); ?>" 
           title="Compartir"
           <?php echo empty($referencia["redo_ref"]) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
           <i class="fas fa-share-alt"></i>
        </a>
    </div>
</td>

                </tr>
            <?php
                $i++;
            }
            ?>


        </tbody>
    </table>
</div>


<script>
   
   document.querySelectorAll('.share-btn').forEach(function(button) {
    button.addEventListener('click', function () {
        const archivo = this.getAttribute('data-file');

        // Obtener ruta absoluta del archivo
        const currentUrl = window.location.href.split('?')[0]; // sin parámetros
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/')); // ruta del proyecto
        const fullUrl = `${baseUrl}/ajax/visor-pdf.php?archivo=${encodeURIComponent(archivo)}`;

        // Llamar a TinyURL
        fetch(`https://tinyurl.com/api-create.php?url=${encodeURIComponent(fullUrl)}`)
            .then(response => response.text())
            .then(shortUrl => {
                // Copiar al portapapeles
                navigator.clipboard.writeText(shortUrl).then(function () {
                    alert("¡Enlace copiado al portapapeles!\n\n" + shortUrl);
                }, function (err) {
                    console.error("Error al copiar: ", err);
                    prompt("Copia manualmente este enlace:", shortUrl);
                });
            })
            .catch(error => {
                alert("Error al generar el enlace corto.");
                console.error("TinyURL error:", error);
            });
    });
});




    // Función que muestra la ventana con el document0
    document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM cargado - Inicializando script");

        // Inicializar Fancybox
        Fancybox.bind("[data-fancybox]", {
            iframe: {
                preload: false,
                css: {
                    width: '80%',
                    height: '80vh'
                }
            }
        });

        // Verificar si los elementos existen al cargar el DOM
        const performActionButton = document.getElementById('performAction');
        const controlDiv = document.getElementById('controlDiv');

        console.log("¿Botón encontrado?", performActionButton);
        console.log("¿Control div encontrado?", controlDiv);

        // Asignar evento al botón directamente después de verificar que existe
        if (performActionButton) {
            console.log("Asignando evento click al botón");

            performActionButton.addEventListener('click', function(e) {
                console.log("Botón presionado");
                e.preventDefault(); // Prevenir comportamiento por defecto

                const selectedIds = [];
                document.querySelectorAll('.document-checkbox:checked').forEach(checkbox => {
                    selectedIds.push(checkbox.getAttribute('data-redo-id'));
                });

                console.log("IDs seleccionados:", selectedIds);

                if (selectedIds.length > 0) {
                    console.log("Enviando solicitud AJAX a:", 'ajax/aceptar_grupo_documentos.php');

                    $.ajax({
                        type: 'POST',
                        url: 'ajax/aceptar_grupo_documentos.php',
                        data: {
                            redo_id: selectedIds
                        },
                        success: function(response) {
                            console.log("Respuesta exitosa del servidor:", response);
                            alert('Documentos aceptados: ' + response);

                        },
                        error: function(xhr, status, error) {
                            console.error("Error en la solicitud:", status, error);
                            console.log("Respuesta del servidor:", xhr.responseText);
                            alert('Hubo un error al procesar los documentos: ' + error);
                        }
                    });
                } else {
                    console.log("No hay documentos seleccionados");
                }
            });
        } else {
            console.error("El botón performAction no fue encontrado en el DOM");
        }
    });

    // Event delegation para los checkboxes
    document.addEventListener('change', function(event) {
        if (event.target && event.target.classList.contains('document-checkbox')) {
            console.log("Checkbox cambiado");
            const selectedCheckboxes = document.querySelectorAll('.document-checkbox:checked');
            const performActionButton = document.getElementById('performAction');
            const controlDiv = document.getElementById('controlDiv');

            console.log("Checkboxes seleccionados:", selectedCheckboxes.length);

            if (selectedCheckboxes.length > 0) {
                console.log("Mostrando div de control y habilitando botón");
                controlDiv.style.display = 'block';
                performActionButton.disabled = false;
            } else {
                console.log("Ocultando div de control y deshabilitando botón");
                controlDiv.style.display = 'none';
                performActionButton.disabled = true;
            }
        }
    });

    // Función alternativa que puede ser llamada directamente desde el atributo onclick
    function procesarDocumentosSeleccionados() {
        console.log("Función procesarDocumentosSeleccionados llamada");
        const selectedIds = [];
        document.querySelectorAll('.document-checkbox:checked').forEach(checkbox => {
            selectedIds.push(checkbox.getAttribute('data-redo-id'));
        });

        console.log("IDs seleccionados (método alternativo):", selectedIds);

        if (selectedIds.length > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax/aceptar_grupo_documentos.php',
                data: {
                    redo_id: selectedIds
                },
                success: function(response) {
                    console.log("Respuesta exitosa:", response);
                    // Desmarcar todos los checkboxes
                    $('.document-checkbox:checked').prop('checked', false);

                    // Ocultar el div de control y deshabilitar el botón
                    const controlDiv = document.getElementById('controlDiv');
                    const performActionButton = document.getElementById('performAction');
                    if (controlDiv) controlDiv.style.display = 'none';
                    if (performActionButton) performActionButton.disabled = true;

                    alert('Documento(s) Aceptados');

                },
                error: function(xhr, status, error) {
                    console.error("Error:", status, error);
                    console.log("Respuesta:", xhr.responseText);
                    alert('Hubo un error al procesar los documentos: ' + error);
                }
            });
        }
    }
</script>
<script>
    $(function() {
        console.log("[Init] Deshabilitando los botones .btn_borrar_documento");


        $(".btn_borrar_documento").hide();
        console.log("[Init] Botones .btn_borrar_documento ocultados:", $(".btn_borrar_documento").length);


        console.log("[Init] Llamando a la función pantalla_roles con usuario:", <?php echo json_encode($_SESSION["login_user"]); ?>);
        <?php echo pantalla_roles("index.php?p=redg_reportes_documentos_gerarquia_mostrar", ($_SESSION["login_user"])); ?>


        console.log("[Init] Después de ejecutar pantalla_roles");
    });
</script>
<style>
    /* Estilo para el div fijo */
    #controlDiv {
        position: fixed;
        top: 0;
        width: 100%;
        background-color: #f1f1f1;
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #ccc;
        z-index: 1000;
    }

    /* Estilo para dar espacio en la parte superior del contenido principal */
    #mainContent {
        margin-top: 60px;
        /* Ajusta este margen según el alto de #controlDiv */
    }

    /* Estilo del botón */
    #actionButton {
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        display: none;
        /* El botón está oculto inicialmente */
    }

    .boton-navegacion {
        display: inline-block;
        padding: 5px 10px;
        background-color: #f0f0f0;
        color: #4CAF50;
        /* Cambiado a verde claro */
        text-align: center;
        text-decoration: none;
        border: 1px solid #4CAF50;
        /* Cambiado a verde claro */
        border-radius: 4px;
        transition: background-color 0.3s, color 0.3s;
        font-size: 14px;
        margin-right: 10px;
    }

    .boton-navegacion:hover {
        background-color: #4CAF50;
        /* Cambia el color de fondo al pasar el mouse */
        color: white;
        /* Cambia el color del texto al pasar el mouse */
    }
</style>