<?php

use Mpdf\Tag\Em;

 include('conexion.php'); ?>



<div class='table-responsive table-striped table-bordered table-hover table-sm' style='text-align: center; align-items:center'>

   <table class="table align-middle">

      <thead class='thead-dark'>

         <tr>

            <th scope='col'>Tarea</th>

            <th scope='col'>Descripción</th>

            <th scope='col'>Fecha de Inicio</th>

            <th scope='col'>Fecha de cierre</th>

            <th scope='col'>Estado</th>

            <th scope='col'>Persona asignada</th>

            <th scope='col'>Avance</th>

            <th scope='col'></th>

         </tr>

      </thead>

      <tbody>

         <?php

         // $nombre=$_GET['nombre'];
         $where = "";
         $usua_id = $_GET["usua_id"];
         $caes_id = $_GET["caes_id"];
         $fecha_inicio = $_GET["fecha_inicio"];
         $fecha_cierre = $_GET["fecha_cierre"];

         // print_r($_GET);

         if(!empty($usua_id) or !empty($caes_id) or !empty($fecha_inicio) or !empty($fecha_cierre)){
            $partes = [];
            if(!empty($usua_id)) array_push($partes, " usua_id IN ($usua_id)");
            if(!empty($caes_id)) array_push($partes, " cate_estado IN ($caes_id)");
            if(!empty($fecha_inicio)) array_push($partes, " cate_fecha_inicio > ($fecha_inicio)");
            if(!empty($fecha_cierre)) array_push($partes, " cate_fecha_cierre < ($fecha_cierre)");

            $where = "WHERE " . implode(" AND ", $partes);
         }

         // if(!empty($usua_id) or !empty($caes_id)) $where .= "WHERE ";
         // if(!empty($usua_id)) $where .= " usua_id IN ($usua_id) ";
         // if(!empty($usua_id) and !empty($caes_id)) $where .= " AND ";
         // if(!empty($caes_id)) $where .= " cate_estado IN ($caes_id)"; 

         $qsql = "SELECT cate_id, caso_id, cate_descripcion, cate_fecha_cierre, cate_nombre, cate_fecha_inicio, cate_observaciones,
         (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id) usua_asigando,
         (SELECT catb_avance FROM casos_tareas_bitacora WHERE cate_id = a.cate_id ORDER BY catb_id DESC LIMIT 1) cate_avance,
         (SELECT caes_nombre FROM casos_estado WHERE caes_id = a.cate_estado) estado
         FROM casos_tareas a
         $where";
         
         // echo $qsql;

         $rs = mysql_query($qsql);

         $num = mysql_num_rows($rs);

         $i = 0;

         while ($i < $num) {

         ?>

            <tr class='tabla_datos_tr'>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cate_nombre'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cate_descripcion'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cate_fecha_inicio'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cate_fecha_cierre'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'estado'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'usua_asigando'); ?></td>

               <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cate_avance'); ?></td>



               <td>

                  <div class='btn-group btn-group-sm'>

                     <a type="button" data-toggle="tooltip" data-placement="top" title="Modificar" class='btn btn-warning' href='javascript:editar(<?php echo mysql_result($rs, $i, 'cate_id'); ?>)' ;><i class="fa-solid fa-pen-to-square"></i>
                     </a>

                     <!-- <a Class='btn btn-danger' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'cate_id'); ?>)' ;><i class="fa-solid fa-trash"></i></a> -->

                     <button class="btn btn-success btn-finalizar-tarea" data-toggle="modal" data-target="#modal-avance" data-cateid="<?php echo mysql_result($rs, $i, 'cate_id'); ?>" data-avance="<?php echo mysql_result($rs, $i, 'cate_avance'); ?>"><i type="button" data-toggle="tooltip" data-placement="top" title="Bitácora" class="fa-solid fa-clipboard-list"></i></button>

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