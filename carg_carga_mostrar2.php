<?php include('conexion.php'); ?>
<script src='jquery/sorter/tablesort.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.number.min.js'></script>
<script src='jquery/sorter/sorts/tablesort.date.min.js'></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script> -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

<script>
   $(function() {
      new Tablesort(document.getElementById('resultado'));
   });
</script>
<div class='table-responsive table-striped table-bordered table-hover table-sm' style='text-align: center; align-items:center'>
   <table id='resultado' class=table align-middle>
      <thead class='thead-dark'>
         <tr>
            <th class=tabla_datos_titulo>Tipo</th>
            <th class=tabla_datos_titulo>Guia</th>
            <th class=tabla_datos_titulo>Código de Vuelo</th>
            <th class=tabla_datos_titulo>Destino final</th>
            <th class=tabla_datos_titulo>Registrado por</th>
            <th class=tabla_datos_titulo>Fecha de registro</th>
            <th class=tabla_datos_titulo>Fecha de recepcion</th>
            <th class=tabla_datos_titulo>Linea aerea</th>
            <th class=tabla_datos_titulo>Estado</th>
            <th class=tabla_datos_titulo_icono></th>
         </tr>
      </thead>
      <tbody>
<?php
$f_carg_guia = $_GET['f_carg_guia'] ?? '';
$where = '';

if ($f_carg_guia != '') {
    $where .= "AND a.carg_guia LIKE '%".mysql_real_escape_string($f_carg_guia)."%'";
}

$qsql = "SELECT * FROM carga a 
         INNER JOIN carga_tipos b ON a.cati_id=b.cati_id
         INNER JOIN aereopuertos_codigos c ON a.aeco_id_destino_final=c.aeco_id
         INNER JOIN usuarios d ON a.usua_id_creador=d.usua_id
         INNER JOIN lineas_aereas e ON a.liae_id=e.liae_id
         INNER JOIN carga_estado f ON a.caes_id=f.caes_id
         INNER JOIN vuelos g ON a.vuel_id=g.vuel_id
         WHERE 1=1 
         $where
         ORDER BY carg_fecha_registro DESC";
 
 $rs = mysql_query($qsql);
 $num = mysql_num_rows($rs);
 $i = 0;
 while ($i < $num) {
 ?>
    <tr class='tabla_datos_tr'>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'cati_nombre'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'carg_guia'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'vuel_codigo'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'aeco_nombre'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'usua_nombre'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'carg_fecha_registro'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'carg_recepcion_real'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'liae_nombre'); ?></td>
       <td class=tabla_datos><?php echo mysql_result($rs, $i, 'caes_nombre'); ?></td>

       <td class="tabla_datos_iconos">
          <div class="dropdown">
             <button class="btn btn-light btn-sm" type="button" id="dropdownMenu<?php echo mysql_result($rs, $i, 'carg_id'); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
             </button>
             <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?php echo mysql_result($rs, $i, 'carg_id'); ?>">
             <a class="dropdown-item" 
href="index.php?p=<?php 
if (mysql_result($rs, $i, 'cati_nombre') == 'Export') {
 echo 'recibos-carga-export';
} elseif (mysql_result($rs, $i, 'cati_nombre') == 'Import') {
 echo 'recibos-carga';
} elseif (mysql_result($rs, $i, 'cati_nombre') == 'Transferencia exportación') {
 echo 'recibos-carga-transf-export';
} elseif (mysql_result($rs, $i, 'cati_nombre') == 'Transferencia importación') {
 echo 'recibos-carga-transf-import';
} else {
 echo 'recibos-carga'; // Ruta por defecto
}
?>&carg_id=<?php echo mysql_result($rs, $i, 'carg_id') ?>">
<i class="fa-solid fa-eye mr-2"></i> Ver
</a>
                <a class="dropdown-item" href="javascript:editar(<?php echo mysql_result($rs, $i, 'carg_id'); ?>)">
                   <svg style="width: 22px;" class="mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                      <path fill="#FFD43B" d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                   </svg> Editar
                </a>
                <a class="dropdown-item" href="javascript:borrar(<?php echo mysql_result($rs, $i, 'carg_id'); ?>)">
                   <svg style="width: 22px;" class="mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                      <path fill="#ad0000" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
                   </svg> Borrar
                </a>
             </div>
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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
.table-striped tbody tr:nth-of-type(odd) {
background-color: whitesmoke;
}

.table-striped tbody tr:nth-of-type(even) {
background-color: #ffff;
}

.table-hover tbody tr:hover {
background-color: rgba(2, 2, 2, 0.1);
}

.table-responsive {
overflow: visible !important;
}

.dropdown {
position: relative;
}

.dropdown-menu {
position: absolute;
z-index: 1000;
}
</style>