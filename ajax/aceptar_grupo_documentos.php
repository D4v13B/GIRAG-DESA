<?php
include('../conexion.php');
include('../funciones.php');
session_start();

if (isset($_POST["redo_id"])) {
    $redo_ids = $_POST["redo_id"]; // Esto es un array ahora
    
    foreach ($redo_ids as $redo_id) {
        // Luego, tu lógica actual, usando el $redo_id individualmente
        $estado_id = 5; // Estado para Aceptar

        // Obtener el procesado_por y rede_id para el redo_id recibido
        $sql = "SELECT redb_id, redb_procesado_por, rede_id FROM reportes_documentos_bitacora WHERE redo_id = $redo_id";
        $res = mysql_query($sql);

        if ($res && mysql_num_rows($res) > 0) {
            $row = mysql_fetch_assoc($res);
            $bitacora_id = $row["redb_id"];
            $procesado_por = $row["redb_procesado_por"];
            $rede_id = $row["rede_id"];

            // Obtener la última bitácora para el redo_id obtenido
            $sql_last_bitacora = "SELECT * FROM reportes_documentos_bitacora WHERE redo_id = $redo_id ORDER BY redb_id DESC LIMIT 1";
            $res_last = mysql_query($sql_last_bitacora);

            if ($res_last && mysql_num_rows($res_last) > 0) {
                $last_bitacora = mysql_fetch_assoc($res_last);

                // Si el procesado_por está vacío, actualizamos la bitácora
                if (empty($procesado_por)) {
                    // Actualizar la bitácora existente para marcarla como procesada (aceptada)
                    $sql_update = "UPDATE reportes_documentos_bitacora 
                                   SET rede_id = $estado_id, redb_procesado_por = '{$_SESSION["login_user"]}' 
                                   WHERE redb_id = $bitacora_id";
                    $res_update = mysql_query($sql_update);

                    if (!$res_update) {
                        echo "Error al actualizar la bitácora con ID $bitacora_id: " . mysql_error();
                    }
                } else {
                    // Si ya está procesada, insertamos una nueva bitácora
                    $sql_insert = "INSERT INTO reportes_documentos_bitacora (redo_id, redb_fecha, redb_ref, rede_id, redb_procesado_por) 
                                   VALUES ('{$last_bitacora['redo_id']}', NOW(), '{$last_bitacora['redb_ref']}', $estado_id, '{$_SESSION["login_user"]}')";
                    $res_insert = mysql_query($sql_insert);

                    if (!$res_insert) {
                        echo "Error al insertar la nueva bitácora para el documento con redo_id $redo_id: " . mysql_error();
                    }
                }
            } else {
                echo "No se encontró una bitácora existente para el redo_id $redo_id.";
            }
        } else {
            echo "No se encontró el redo_id $redo_id en la base de datos.";
        }

        echo "El documento con redo_id $redo_id ha sido procesado con éxito.";
    }
} else {
    echo "Faltan datos requeridos.";
}

?>
