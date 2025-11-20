<?php include('conexion.php'); ?> 

<script src='jquery/sorter/tablesort.min.js'></script>
        <script src='jquery/sorter/sorts/tablesort.number.min.js'></script>
        <script src='jquery/sorter/sorts/tablesort.date.min.js'></script>
        <script>$(function() {
          new Tablesort(document.getElementById('resultado'));
        });
        </script><div class='table-responsive table-striped table-bordered table-hover table-sm' style='text-align: center; align-items:center'>
<table id='resultado' class=table align-middle>
<thead class='thead-dark'>
<tr>
<th class=tabla_datos_titulo>Servicio</th>
<th class=tabla_datos_titulo>Tipo</th>
<th class=tabla_datos_titulo>Monto Base</th>
<th class=tabla_datos_titulo>Monto Kg adicional</th>
<th class=tabla_datos_titulo>Peso Mínimo</th>
<th class=tabla_datos_titulo>Lleva Itbms</th>
<th class=tabla_datos_titulo>Lleva AIT</th>
<th class=tabla_datos_titulo>Es AIT</th>
<th class=tabla_datos_titulo>Aerolinea</th>
<th class=tabla_datos_titulo>Es Reembolsable</th>
<th class=tabla_datos_titulo>Cuenta</th>

<th class=tabla_datos_titulo_icono></th>
</tr>
</thead>
<tbody>
<?php
$f_case_nombre=$_GET['f_case_nombre'];
$f_cadt_id=$_GET['f_cadt_id'];
$f_case_monto=$_GET['f_case_monto'];
$f_case_itbms=$_GET['f_case_itbms'];
$f_case_reembolsable=$_GET['f_case_reembolsable'];
$f_case_peso_minimo=$_GET['f_case_peso_minimo'];
$where='';
if($f_case_nombre!='' && $f_case_nombre!='null') $where .="AND a.case_nombre LIKE '%$f_case_nombre%'";
if($f_cadt_id!='' && $f_cadt_id!='null') $where .="AND a.cadt_id IN ($f_cadt_id)";
if($f_case_monto!='' && $f_case_monto!='null') $where .="AND a.case_monto LIKE '%$f_case_monto%'";
if($f_case_itbms!='' && $f_case_itbms!='null') $where .="AND a.case_itbms LIKE '%$f_case_itbms%'";
if($f_case_reembolsable!='' && $f_case_reembolsable!='null') $where .="AND a.case_reembolsable LIKE '%$f_case_reembolsable%'";
if($f_case_peso_minimo!='' && $f_case_peso_minimo!='null') $where .="AND a.case_peso_minimo LIKE '%$f_case_peso_minimo%'";

$qsql ="SELECT a.*, 
   IF(a.case_itbms=0,'NO','SI') itbms,
   IF(a.case_reembolsable=0,'NO','SI') reembolsable,
   IF(a.case_ait=0,'NO','SI') ait,   
   IF(a.case_es_ait=0,'NO','SI') es_ait,   
   a.case_cuenta,
   COALESCE((SELECT liae_nombre 
            FROM lineas_aereas 
            WHERE liae_id=a.liae_id), 'N/A') aerolinea,
   COALESCE((SELECT cade_descripcion 
            FROM carga_detalle_tipo 
            WHERE cade_tipo_id=a.cadt_id), 'N/A') cade_descripcion
FROM carga_servicios a
WHERE 1=1 $where

";

$rs = mysql_query($qsql);
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'case_nombre'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'cade_descripcion'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'case_monto'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'case_monto_max'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'case_peso_minimo'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'itbms'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'ait'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'es_ait'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'aerolinea'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'reembolsable'); ?></td>
<td class=tabla_datos><?php echo mysql_result($rs, $i, 'case_cuenta'); ?></td>

            <td class=tabla_datos_iconos>
            <div Class='btn-group btn-group-sm'>
                     <a Class='btn' href='javascript:editar(<?php echo mysql_result($rs, $i, 'case_id'); ?>)' ;>
                        <svg style = 'width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
                           <path fill = '#FFD43B' d='M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z' />
                        </svg>
                     </a>
                     <a Class='btn' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'case_id'); ?>)' ;>
                        <svg style = 'width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                           <path fill = '#ad0000' d='M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z' />
                        </svg>
                     </a>
                  </div></td>
</tr>
<?php
$i++;
}
?>
</tbody>
</table>
</div>

