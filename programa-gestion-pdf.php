<?php


include "conexion.php";

require_once __DIR__ . '/vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf([
   'tempDir' => __DIR__ . "/temp",
   'format'  => 'Letter' // Establecer tamaño de página en Carta
]);




$caso_id = $_GET["caso"];

$stmt = "SELECT cont_detalle FROM contratos WHERE cont_nombre = 'PROGRAMA-GESTION'";
$plantilla = mysql_fetch_assoc(mysql_query($stmt))["cont_detalle"];

// print_r($plantilla);

$stmt = "SELECT 
    a.caso_id, 
    caso_fecha, 
    caso_descripcion, 
    caso_nota, 
    caso_fecha_analisis, 
    caso_beneficio, 
    -- First reviewer/approver IDs
    usua_id_revisado,
    usua_id_aprobado,
    -- Second reviewer/approver IDs
    usua_id_revisado2,
    usua_id_aprobado2,
    -- Third reviewer/approver IDs
    usua_id_revisado3,
    usua_id_aprobado3,

    -- First reviewer name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_revisado AND a.usca_id=b.usca_id) revisado,

    -- Second reviewer name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_revisado2 AND a.usca_id=b.usca_id) revisado2,

    -- Third reviewer name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_revisado3 AND a.usca_id=b.usca_id) revisado3,

    -- First approver name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_aprobado AND a.usca_id=b.usca_id) aprobado,

    -- Second approver name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_aprobado2 AND a.usca_id=b.usca_id) aprobado2,

    -- Third approver name and position
    (SELECT CONCAT(a.usua_nombre, ' - ', b.usca_nombre)
    FROM usuarios a, usuarios_cargos b
    WHERE a.usua_id = usua_id_aprobado3 AND a.usca_id=b.usca_id) aprobado3,

    -- Rest of the existing fields
    (SELECT usua_nombre FROM usuarios WHERE usua_id=usua_id_asignado) usua_asignado,
    (SELECT proc_nombre FROM procesos WHERE proc_id = a.proc_id) proceso,
    (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_asignado) responsable_programa,
    (SELECT depa_nombre FROM departamentos dp WHERE dp.depa_id = a.depa_id) departamento
FROM casos a 
WHERE caso_id = $caso_id";

$caso_detalles = mysql_fetch_assoc(mysql_query($stmt));
// print_r($caso_detalles);

// Query que me trae todas las tareas de manera detallada
$stmt = "SELECT
    a.*,
    b.catb_avance,
    (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id) AS responsable_1,
    (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_2) AS responsable_2,
    (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_3) AS responsable_3
FROM casos_tareas a
INNER JOIN (
    SELECT
        cate_id,
        MAX(catb_id) AS ultimo_catb_id
    FROM casos_tareas_bitacora
    GROUP BY cate_id
) ultimos_avances ON a.cate_id = ultimos_avances.cate_id
INNER JOIN casos_tareas_bitacora b ON ultimos_avances.cate_id = b.cate_id 
    AND ultimos_avances.ultimo_catb_id = b.catb_id
WHERE a.caso_id = $caso_id";

$res = mysql_query($stmt);
$i = 0;
$descri_html = "";
$promedio = 0;

while ($row = mysql_fetch_assoc($res)) {
    $i++;
    $cate_fecha_inicio = strtotime($row["cate_fecha_inicio"]);
    $cate_fecha_cierre = strtotime($row["cate_fecha_cierre"]);
    $cate_fecha_inicio = date("d-m-Y", $cate_fecha_inicio);
    $cate_fecha_cierre = date("d-m-Y", $cate_fecha_cierre);
    
    // Construir la lista de responsables
    $responsables = array_filter([
        $row["responsable_1"],
        $row["responsable_2"],
        $row["responsable_3"]
    ]);
    
    $responsables_str = implode(" ,", $responsables);
    
    $descri_html .= "<tr>
        <td>$i</td>
        <td>" . $row["cate_nombre"] . "</td>
        <td>" . $cate_fecha_inicio . "</td>
        <td>" . $cate_fecha_cierre . "</td>
        <td>" . $responsables_str . "</td>
        <td>" . $row["catb_avance"] . "%</td>
        <td>" . $row["cate_recursos"] . "</td>
        <td>" . $row["cate_observaciones"] . "</td>
    </tr>";
    
    $promedio += (int)$row["catb_avance"];
}
$caso_fecha_analisis = strtotime($caso_detalles["caso_fecha_analisis"]);

$caso_fecha_analisis = date("d-m-Y", $caso_fecha_analisis);

// Cambiar el encabezado
$plantilla = str_replace("[FECHA_ANALISIS]", $caso_detalles["caso_fecha_analisis"], $plantilla);
$plantilla = str_replace("[NO_CONFORMIDAD]", $caso_detalles["caso_nota"], $plantilla);
$plantilla = str_replace("[CASO_ID]", $caso_detalles["caso_id"], $plantilla);
$plantilla = str_replace("[REPORTE_ANALISIS]", $caso_detalles["caso_descripcion"], $plantilla);
$plantilla = str_replace("[FECHA_CASO]", $caso_fecha_analisis, $plantilla);
$plantilla = str_replace("[AREA]", $caso_detalles["departamento"], $plantilla);
$plantilla = str_replace("[RESPONSABLE]", $caso_detalles["responsable_programa"], $plantilla);
$plantilla = str_replace("[BENEFICIO]", $caso_detalles["caso_beneficio"], $plantilla);
$plantilla = str_replace("[CASO_ID]", $caso_detalles["caso_id"], $plantilla);
$plantilla = str_replace("[PROCESO]", $caso_detalles["proceso"], $plantilla);
$plantilla = str_replace("[REPORTE_ANALISIS]", $caso_detalles["caso_descripcion"], $plantilla);
$plantilla = str_replace("[RESPONSABLE]", $caso_detalles["responsable_programa"], $plantilla);
$plantilla = str_replace("[TABLA_TAREAS]", $descri_html, $plantilla);

// Promedio de avances
$diaActual = date('d/m/Y');
$total = $i > 0 ? (($promedio/ ($i*100)) * 100) : 0;
$plantilla = str_replace("[AVANCE_TOTAL_TAREAS]", $total, $plantilla);

$plantilla = str_replace("[NOMBRE_APROBADO]", $caso_detalles["aprobado"], $plantilla);
$plantilla = str_replace("[NOMBRE_REVISADO]", $caso_detalles["revisado"], $plantilla);
$plantilla = str_replace("[FECHA_ACTUAL]",$diaActual,$plantilla);
$plantilla = str_replace("[NOMBRE_REVISADO2]", $caso_detalles["revisado2"], $plantilla);
$plantilla = str_replace("[NOMBRE_REVISADO3]", $caso_detalles["revisado3"], $plantilla);
$plantilla = str_replace("[NOMBRE_APROBADO2]", $caso_detalles["aprobado2"], $plantilla);
$plantilla = str_replace("[NOMBRE_APROBADO3]", $caso_detalles["aprobado3"], $plantilla);

// Reemplazar la fecha actual en todos los placeholders de fecha
$diaActual = date('d/m/Y');
$plantilla = str_replace("[FECHA_ACTUAL]", $diaActual, $plantilla);

// Buscar las firmas
$usuario_aprobado_id = $caso_detalles['usua_id_aprobado'];
$usuario_revisado_id2 = $caso_detalles['usua_id_revisado2'];
$usuario_revisado_id3 = $caso_detalles['usua_id_revisado3'];
$usuario_revisado_id = $caso_detalles['usua_id_revisado'];
$usuario_aprobado_id2 = $caso_detalles['usua_id_aprobado2'];
$usuario_aprobado_id3 = $caso_detalles['usua_id_aprobado3'];

// Obtener las cantidades de firmas
$stmt = "SELECT cantidad_usua_firmas_aprobado, cantidad_usua_firmas_revisado FROM casos WHERE caso_id = $caso_id";
$cantidades = mysql_fetch_assoc(mysql_query($stmt));
$cantidad_usua_firmas_aprobado = $cantidades['cantidad_usua_firmas_aprobado'];
$cantidad_usua_firmas_revisado = $cantidades['cantidad_usua_firmas_revisado'];

// Función para generar la tabla de firmas
function generateSignatureTable($isReviewer = true, $currentNumber = 1) {
   $type = $isReviewer ? "REVISADO" : "APROBADO";
   $html = '
   <table width="100%" border="1" cellpadding="5" cellspacing="0" style="table-layout: fixed;">
       <tr>
           <td width="45%" style="vertical-align: top; height: 120px;">
               <table width="100%" border="0">
                   <tr>
                       <td style="font-size: 10px;">Revisado por: [NOMBRE_REVISADO' . $currentNumber . ']</td>
                       <td align="right" style="font-size: 10px;">[FECHA_ACTUAL]</td>
                   </tr>
                   <tr>
                       <td colspan="2" height="100" align="left" style="vertical-align: bottom;">[FIRMA_USUARIO_REVISADO' . $currentNumber . ']</td>
                   </tr>
               </table>
           </td>
           <td width="50%" style="vertical-align: top; height: 120px;">
               <table width="100%" border="0">
                   <tr>
                       <td style="font-size: 10px;">Aprobado por: [NOMBRE_APROBADO' . $currentNumber . ']</td>
                       <td align="right" style="font-size: 10px;">[FECHA_ACTUAL]</td>
                   </tr>
                   <tr>
                       <td colspan="2" height="100" align="left" style="vertical-align: bottom;">[FIRMA_USUARIO_APROBADO' . $currentNumber . ']</td>
                   </tr>
               </table>
           </td>
       </tr>
   </table>';
   return $html;
}

// Generar las tablas de firmas según la cantidad de firmas
$primer_html = '';
$segundo_html = '';
$tercero_html = '';

if ($cantidad_usua_firmas_revisado >= 1 || $cantidad_usua_firmas_aprobado >= 1) {
    $primer_html = generateSignatureTable(true, 1); // Primera firma de revisado y aprobado
}

if ($cantidad_usua_firmas_revisado >= 2 || $cantidad_usua_firmas_aprobado >= 2) {
    $segundo_html = generateSignatureTable(true, 2); // Segunda firma de revisado y aprobado
}

if ($cantidad_usua_firmas_revisado >= 3 || $cantidad_usua_firmas_aprobado >= 3) {
    $tercero_html = generateSignatureTable(true, 3); // Tercera firma de revisado y aprobado
}

// Reemplazar los placeholders en la plantilla
$plantilla = str_replace("[REVISADO_APROBADO_PRIMERO]", $primer_html, $plantilla);
$plantilla = str_replace("[REVISADO_APROBADO_SEGUNDO]", $segundo_html, $plantilla);
$plantilla = str_replace("[REVISADO_APROBADO_TERCERO]", $tercero_html, $plantilla);

// Manejar las firmas según la cantidad de firmas
for ($i = 1; $i <= max($cantidad_usua_firmas_revisado, $cantidad_usua_firmas_aprobado); $i++) {
    // Manejar firmas de revisado
    if ($i <= $cantidad_usua_firmas_revisado) {
        // Determinar el campo a usar según la posición
        $usuario_revisado_id = ($i == 1) ? $caso_detalles['usua_id_revisado'] : $caso_detalles['usua_id_revisado' . $i];
        
        if (!empty($usuario_revisado_id)) {
            $signature_query = mysql_query("SELECT usfi_ref FROM usuarios_firmas WHERE usua_id = '$usuario_revisado_id'");
            $res = $signature_query ? mysql_fetch_assoc($signature_query)['usfi_ref'] : '';

            $signature_html = (file_exists("firmas-electronicas/$res") && !empty($res)) 
                ? "<img style='max-width: 100%; max-height: 100px; display: block; margin: auto;' src='firmas-electronicas/$res'>" 
                : "<div style='height:100px; border:1px dashed gray; text-align:center; padding-top:40px;'>Pendiente firma de " . 
                  ($i == 1 ? "primer revisor" : ($i == 2 ? "segundo revisor" : "tercer revisor")) . "</div>";
        } else {
            $signature_html = "<div style='height:150px; border:1px dashed gray; text-align:center; padding-top:50px; font-size: 10px'>Usuario de revisión no definido</div>";
        }

        // Reemplazar el placeholder correspondiente sin usar número para el primer caso
        $placeholder = ($i == 1) ? "[FIRMA_USUARIO_REVISADO1]" : "[FIRMA_USUARIO_REVISADO" . $i . "]";
        $plantilla = str_replace($placeholder, $signature_html, $plantilla);

        // Reemplazar el nombre del revisor
        $nombre_placeholder = ($i == 1) ? "[NOMBRE_REVISADO1]" : "[NOMBRE_REVISADO" . $i . "]";
        $nombre_revisor = ($i == 1) ? $caso_detalles['revisado'] : $caso_detalles['revisado' . $i];
        $plantilla = str_replace($nombre_placeholder, $nombre_revisor, $plantilla);
    }

    // Manejar firmas de aprobado
    if ($i <= $cantidad_usua_firmas_aprobado) {
        // Determinar el campo a usar según la posición
        $usuario_aprobado_id = ($i == 1) ? $caso_detalles['usua_id_aprobado'] : $caso_detalles['usua_id_aprobado' . $i];
        
        if (!empty($usuario_aprobado_id)) {
            $signature_query = mysql_query("SELECT usfi_ref FROM usuarios_firmas WHERE usua_id = '$usuario_aprobado_id'");
            $res = $signature_query ? mysql_fetch_assoc($signature_query)['usfi_ref'] : '';

            $signature_html = (file_exists("firmas-electronicas/$res") && !empty($res)) 
                ? "<img style='max-width: 100%; max-height: 100px; display: block; margin: auto;' src='firmas-electronicas/$res'>" 
                : "<div style='height:100px; border:1px dashed gray; text-align:center; padding-top:40px;'>Pendiente firma de " . 
                  ($i == 1 ? "primer aprobador" : ($i == 2 ? "segundo aprobador" : "tercer aprobador")) . "</div>";
        } else {
            $signature_html = "<div style='height:150px; border:1px dashed gray; text-align:center; padding-top:50px; font-size: 10px'>Usuario de aprobación no definido</div>";
        }

        // Reemplazar el placeholder correspondiente sin usar número para el primer caso
        $placeholder = ($i == 1) ? "[FIRMA_USUARIO_APROBADO1]" : "[FIRMA_USUARIO_APROBADO" . $i . "]";
        $plantilla = str_replace($placeholder, $signature_html, $plantilla);

        // Reemplazar el nombre del aprobador
        $nombre_placeholder = ($i == 1) ? "[NOMBRE_APROBADO1]" : "[NOMBRE_APROBADO" . $i . "]";
        $nombre_aprobador = ($i == 1) ? $caso_detalles['aprobado'] : $caso_detalles['aprobado' . $i];
        $plantilla = str_replace($nombre_placeholder, $nombre_aprobador, $plantilla);
    }
}
// Reemplazar la fecha actual
$diaActual = date('d/m/Y');
$plantilla = str_replace("[FECHA_ACTUAL]", $diaActual, $plantilla);
// Asegurar que todos los placeholders potenciales sean reemplazados
$placeholders = [
    "[FIRMA_USUARIO_REVISADO1]",  // Sin número para el primero
    "[FIRMA_USUARIO_REVISADO2]",
    "[FIRMA_USUARIO_REVISADO3]",
    "[FIRMA_USUARIO_APROBADO1]",  // Sin número para el primero
    "[FIRMA_USUARIO_APROBADO2]",
    "[FIRMA_USUARIO_APROBADO3]"
];

foreach ($placeholders as $placeholder) {
    if (strpos($plantilla, $placeholder) !== false) {
        $plantilla = str_replace($placeholder, 
            "<div style='height:150px; border:1px dashed gray; text-align:center; padding-top:50px;'>Firma pendiente</div>", 
            $plantilla
        );
    }
}
// print_r($plantilla);

ob_start(); 

$output0 = ob_get_clean();

ob_start();
$content = ob_get_clean();


$mpdf->WriteHTML($plantilla);

if(isset($_GET["tipo"]) and $_GET["tipo"] == "guardado"){
   $nombre = time()."-programa-gestion-caso-$caso_id.pdf"; 

   $mpdf->Output("img/casos_docs/".$nombre, 'F');

   $stmt = "INSERT INTO casos_documentos(cado_nombre, caso_id, cado_ref) VALUES('$nombre', '$caso_id', '$nombre')";
   mysql_query($stmt);
   echo mysql_errno();
   echo json_encode(["tipo" => "Guardado hecho"]);
}else{
   $mpdf->Output();
}