<?php
include "./../conexion.php";

// Obtener glosario
$glosario = [];
$rs = mysql_query("SELECT opvg_id, opvg_name, opvg_tipo FROM operaciones_vuelos_glosario");
while($row = mysql_fetch_assoc($rs)) {
    $glosario[$row['opvg_name']] = [
        'id'   => $row['opvg_id'],
        'tipo' => $row['opvg_tipo']
    ];
}

$opvu_id = isset($_POST['opvu_id']) ? (int)$_POST['opvu_id'] : 1;

foreach($_POST as $name => $valor){
    print_r($valor);
    if($name == 'opvu_id') continue;

    if(isset($glosario[$name])){
        $campo = $glosario[$name];
        $opvg_id = $campo['id'];
        $tipo    = $campo['tipo'];

        $fecha = $decimal = $entero = $firma = "NULL";

        switch($tipo){
            case 1: $fecha = "'".date('Y-m-d H:i:s', strtotime($valor))."'"; break;
            case 2: $decimal = floatval($valor); break;
            case 3: $entero = intval($valor); break;
            default: $firma = "'".mysql_real_escape_string($valor)."'"; break;
        }

        $sql = "INSERT INTO operaciones_vuelos_detalles
            (opvu_id, opvg_id, opvd_fecha, opvd_decimal, opvd_entero, opvd_firma)
            VALUES (
                $opvu_id,
                $opvg_id,
                $fecha,
                ".($decimal !== "NULL" ? $decimal : "NULL").",
                ".($entero !== "NULL" ? $entero : "NULL").",
                $firma
            )
            ON DUPLICATE KEY UPDATE
                opvu_id = VALUES(opvu_id),
                opvg_id = VALUES(opvg_id)
        ";

        mysql_query($sql);
    }
}
echo json_encode(['success'=>true]);
?>
