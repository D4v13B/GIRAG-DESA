<?php 
include('conexion.php');

$id = $_GET['id'];
$qsql = "SELECT * FROM casos WHERE caso_id='$id'";
$rs = mysql_query($qsql);

// Asegúrate de que exista un resultado antes de intentar acceder a él
if (mysql_num_rows($rs) > 0) {
    $i = 0; // Siempre será 0 si solo hay un resultado
    echo mysql_result($rs, $i, 'caso_id') . '||';
    echo mysql_result($rs, $i, 'caso_descripcion') . '||';
    echo mysql_result($rs, $i, 'usua_id_abierto') . '||';
    echo mysql_result($rs, $i, 'caso_estado') . '||';
    echo mysql_result($rs, $i, 'depa_id') . '||';
    echo mysql_result($rs, $i, 'cati_id') . '||';
    echo mysql_result($rs, $i, 'inso_id') . '||';
    echo mysql_result($rs, $i, 'inpr_id') . '||';
    echo mysql_result($rs, $i, 'imec_id') . '||';
    echo mysql_result($rs, $i, 'impe_id') . '||';
    echo mysql_result($rs, $i, 'imma_id') . '||';
    echo mysql_result($rs, $i, 'equi_id') . '||';
    echo mysql_result($rs, $i, 'caso_fecha') . '||';
    echo mysql_result($rs, $i, 'caso_nota') . '||';
    echo mysql_result($rs, $i, 'usua_id_aprobado') . '||';
    echo mysql_result($rs, $i, 'usua_id_asignado') . '||';
    echo mysql_result($rs, $i, 'caso_ubicacion') . '||';
    echo mysql_result($rs, $i, 'cacl_id') . '||';
    echo mysql_result($rs, $i, 'cacd_id') . '||';
    echo mysql_result($rs, $i, 'caso_referencia') . '||';
    echo mysql_result($rs, $i, 'proc_id') . '||';
    echo mysql_result($rs, $i, 'depa_id_quien_reporta') . '||';
    echo mysql_result($rs, $i, 'caso_externo') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_revision') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_revision2') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_revision3') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_aprobacion') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_aprobacion2') . '||';
    echo mysql_result($rs, $i, 'usua_id_encargado_aprobacion3') . '||';
}
?>
