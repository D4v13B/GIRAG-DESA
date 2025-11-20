<?php 

session_start();
include('conexion.php'); 
include('funciones.php'); 
$user_check = $_SESSION['login_user'];

?>



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
            <th>Fecha</th>

            <th class=tabla_datos_titulo>Guía</th>

            <th class=tabla_datos_titulo>Shipper</th>

            <th class=tabla_datos_titulo>Consignee</th>

            <th class=tabla_datos_titulo>Peso</th>

            <th class=tabla_datos_titulo>Piezas</th>

            <th class=tabla_datos_titulo>Descripción</th>

            <th class=tabla_datos_titulo>Tipo</th>

            <th class=tabla_datos_titulo>Notificada</th> 
            <th class=tabla_datos_titulo>Facturada</th> 

            <th class=tabla_datos_titulo_icono></th>

         </tr>

      </thead>

      <tbody>

         <?php
         $f_cade_peso = $_GET['f_cade_peso'];

         $f_cade_peso = $_GET['f_cade_peso'];

         $f_cade_piezas = $_GET['f_cade_piezas'];

         $f_cade_desc = $_GET['f_cade_desc'];

         $f_cade_guia = $_GET['f_cade_guia'];

         $f_cade_tipo_id = $_GET['f_cade_tipo_id'];

         $where = '';

         if ($f_cade_peso != '' && $f_cade_peso != 'null') $where .= "AND a.cade_peso LIKE '%$f_cade_peso%'";

         if ($f_cade_piezas != '' && $f_cade_piezas != 'null') $where .= "AND a.cade_piezas LIKE '%$f_cade_piezas%'";

         if ($f_cade_desc != '' && $f_cade_desc != 'null') $where .= "AND a.cade_desc LIKE '%$f_cade_desc%'";

         if ($f_cade_guia != '' && $f_cade_guia != 'null') $where .= "AND a.cade_guia LIKE '%$f_cade_guia%'";

         if ($f_cade_tipo_id != '' && $f_cade_tipo_id != 'null') $where .= "AND a.cade_tipo_id IN ($f_cade_tipo_id)";


         $qsql = "
SELECT 
  a.*, 
  b.cade_descripcion AS cade_tipo_nombre,
  c.carg_fecha_registro,
  (SELECT ship_nombre FROM shipper WHERE ship_id=a.ship_id) AS shipper,
  (SELECT cons_nombre FROM consignee WHERE cons_id=a.cons_id) AS consignee
FROM carga_detalles a
JOIN carga_detalle_tipo b ON a.cade_tipo_id = b.cade_tipo_id
JOIN carga c ON a.carg_id = c.carg_id
WHERE 1=1
  $where
ORDER BY a.cade_id DESC
";




         $rs = mysql_query($qsql);

         $num = mysql_num_rows($rs);

         $i = 0;

         while ($i < $num) {

            // Obtener el estado
            $notificada = mysql_result($rs, $i, 'cade_notificada'); // 1=Sí, 0=No
            $facturada = mysql_result($rs, $i, 'cade_facturada');   // 1=Sí, 0=No

            // Lógica de visualización para Notificada
            if ($notificada == 1) {
                $estado_notificada = '<span class="text-success font-weight-bold" title="Notificada">SÍ <i class="fa-solid fa-check-circle"></i></span>';
            } else {
                $estado_notificada = '<span class="text-danger" title="Pendiente de Notificar">NO <i class="fa-solid fa-circle-xmark"></i></span>';
            }

            // Lógica de visualización para Facturada
            if ($facturada == 1) {
                $estado_facturada = '<span class="text-info font-weight-bold" title="Facturada">SÍ <i class="fa-solid fa-file-invoice"></i></span>';
            } else {
                $estado_facturada = '<span class="text-danger" title="Pendiente de Facturar">NO <i class="fa-solid fa-circle-xmark"></i></span>';
            }
          ?>

            <tr class='tabla_datos_tr'>
                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'carg_fecha_registro'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_guia'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'shipper'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'consignee'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_peso'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_piezas'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_desc'); ?></td>

                <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_tipo_nombre'); ?></td>
                
                <td class=tabla_datos><?php echo $estado_notificada; ?></td> 
                <td class=tabla_datos><?php echo $estado_facturada; ?></td> 

                <td class=tabla_datos_iconos>

                  <div Class='btn-group btn-group-sm'>

                     <a Class='btn btn' href='javascript:editar(<?php echo mysql_result($rs, $i, 'cade_id'); ?>)' ;>

                        <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>

                           <path fill='#FFD43B' d='M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z' />

                        </svg>

                     </a>

                     <a Class='btn btn_borrar_factura' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'cade_id'); ?>)' ;>

                        <svg style='width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->

                           <path fill='#ad0000' d='M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z' />

                        </svg>

                     </a>


                     <button type="button" class="btn" data-toggle="modal" data-target="#modalCajaDetalles" onclick="mostrarCajaDetalles(<?php echo mysql_result($rs, $i, 'cade_id'); ?>, 0, '<?php echo mysql_result($rs, $i, 'cade_guia'); ?>', '<?php echo mysql_result($rs, $i, 'carg_id'); ?>')" data-title='SERVICIOS' title='SERVICIOS'>
                        <i class="fa-solid fa-cash-register text-success"></i>
                     </button>
                     <button type="button" class="btn" data-toggle="modal" data-target="#modalCajaDetalles" onclick="mostrarCajaDetalles(<?php echo mysql_result($rs, $i, 'cade_id'); ?>, 0, '<?php echo mysql_result($rs, $i, 'cade_guia'); ?>', '<?php echo mysql_result($rs, $i, 'carg_id'); ?>')" data-title='FACTURAR' title='FACTURAR'>
                        <i class="fa-solid fa-file-invoice text-info"></i>
                     </button>

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



<?php include "./caja_servicios_detalles.php" ?>

<script>
   $(function() {

      //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL
      $(".btn_borrar_factura").hide();
      <?php echo pantalla_roles("index.php?p=cade_carga_detalles_mostrar", $_SESSION["login_user"]) ?>
        <?php echo pantalla_roles("index.php?p=redg_reportes_documentos_gerarquia_mostrar", ($_SESSION["login_user"])); ?>

   })
</script>