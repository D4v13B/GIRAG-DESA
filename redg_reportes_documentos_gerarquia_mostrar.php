<?php
include('conexion.php');
include('funciones.php');
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
$ruta = '<a href="javascript:abrir_carpeta(' . "'0','0'" . ')">RAIZ</a>';
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
        $abuelo_nombre = '<a href="javascript:abrir_carpeta(' . "'" . $abuelo_nivel . "','" . $abuelo_id . "'" . ')">' . $abuelo_nombre . "</a>";
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
</head>
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
            ?>
                <tr class='tabla_datos_tr'>
                    <td><i class="<?php echo $tipo ?>"></i></td>
                    <td class=tabla_datos style="text-align:left !important"><a href="javascript:abrir_carpeta('<?php echo $nivel ?>','<?php echo $id ?>')"><?php echo mysql_result($rs, $i, 'redg_nombre'); ?></td>
                    <TD>
                        <div></div>
                    </td>
                    <td class=tabla_datos_iconos>
                        <div Class='btn-group btn-group-sm'>
                            <!-- <a Class='btn' href='javascript:editar(<?php echo mysql_result($rs, $i, 'redg_id'); ?>)' ;>
                                <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
                                    <path fill='#FFD43B' d='M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z' />
                                </svg>
                            </a> -->
                            <a Class='btn' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'redg_id'); ?>)' ;>
                                <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path fill='#ad0000' d='M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z' />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php
                $i++;
            }
            //AHORA LOS ARCHIVOS 

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
            ?>
                <tr class='tabla_datos_tr'>
                    <td><i class="<?php echo $tipo ?>"></i></td>
                    <td class=tabla_datos style="text-align:left !important"><a href="" style="text-decoration:none;color:#000000"><?php echo mysql_result($rs, $i, 'redo_titulo'); ?></td>
                    <td>
                        <!-- Abrir enlace usando visualizador de Windows -->
                        <?php if (!empty($referencia["redo_ref"])) : ?>
                            <div>
                                <?php
                                $file_url = "https://giraglogic.girag.aero/manuales-uso/" . $referencia["redo_ref"];
                                $viewer_url = "https://view.officeapps.live.com/op/embed.aspx?src=" . urlencode($file_url);
                                ?>
                                <a href="<?php echo htmlspecialchars($viewer_url, ENT_QUOTES, 'UTF-8'); ?>" data-fancybox data-type="iframe">
                                    <?php echo htmlspecialchars($referencia["redo_ref"], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php endif; ?>




                    </td>
                    <td class=tabla_datos_iconos>
                        <div class='btn-group btn-group-sm'>
                            <a class="btn text-success" href="index.php?p=reportes-detalles&id=<?php echo mysql_result($rs, $i, 'redo_id'); ?>"><i class="fa-solid fa-eye"></i></a>
                            <!-- <a class='btn' href='javascript:editar_archivo(<?php echo mysql_result($rs, $i, 'redo_id'); ?>)'>
                                <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
                                    <path fill='#FFD43B' d='M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z' />
                                </svg>
                            </a> -->
                            <a class='btn' href='javascript:borrar_archivo(<?php echo mysql_result($rs, $i, 'redo_id'); ?>)'>
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
            ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script>
    // Ocultar los controles exclusivos por rol
    $(".btn_inspeccionar_documento").hide();

    <?php echo pantalla_roles("index.php?p=redg_reportes_documentos_gerarquia", $_SESSION["login_user"]) ?>


  // Función que muestra la ventana con el document0
    document.addEventListener('DOMContentLoaded', function() {
        Fancybox.bind("[data-fancybox]", {
            iframe: {
                preload: false,
                css: {
                    width: '80%',
                    height: '80%'
                }
            }
        });
    });
</script>