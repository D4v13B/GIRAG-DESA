<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('conexion.php'); ?> 

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
<th class=tabla_datos_titulo>Nombre</th>
<th class=tabla_datos_titulo>Pais</th>
<th class=tabla_datos_titulo>Imagen</th>
<th class="tabla_datos_titulo">Prefijo</th>
<th class="tabla_datos_titulo">DK</th>
<th class=tabla_datos_titulo_icono></th>
</tr>
</thead>
<tbody>
<?php
$f_liae_nombre = isset($_GET['f_liae_nombre']) ? $_GET['f_liae_nombre'] : '';
$f_pais_id = isset($_GET['f_pais_id']) ? $_GET['f_pais_id'] : '';
$f_liae_ref = isset($_GET['f_liae_ref']) ? $_GET['f_liae_ref'] : '';

$where='';
if($f_liae_nombre!='' && $f_liae_nombre!='null') $where .="AND a.liae_nombre LIKE '%$f_liae_nombre%'";
if($f_pais_id!='' && $f_pais_id!='null') $where .="AND a.pais_id IN ($f_pais_id)";
if($f_liae_ref!='' && $f_liae_ref!='null') $where .="AND a.liae_ref LIKE '%$f_liae_ref%'";

$qsql ="select * from lineas_aereas a,paises b
WHERE 1=1
AND a.pais_id=b.pais_id
$where
";
// echo "Query: " . $qsql . "<br>"; // Para ver la consulta

$rs = mysql_query($qsql);
if (!$rs) {
    echo "Error MySQL: " . mysql_error() . "<br>"; // Para ver si hay error en la query
}

// echo "Número de resultados: " . mysql_num_rows($rs) . "<br>"; // Para ver cuántos registros retorna
$num = mysql_num_rows($rs);
$i=0;
while ($i<$num)
{
?>
<tr class='tabla_datos_tr'>
  <td class="tabla_datos"><?php echo mysql_result($rs, $i, 'liae_nombre'); ?></td> <!-- Nombre -->
  <td class="tabla_datos"><?php echo mysql_result($rs, $i, 'pais_nombre'); ?></td> <!-- País -->
  <td class="tabla_datos">
    <img style="width: 100px;" src="https://giraglogicdesa.girag.aero/img/liae_ref/<?php echo mysql_result($rs, $i, 'liae_ref'); ?>" alt="sin logo">
  </td> <!-- Imagen -->
  <td class="tabla_datos"><?php echo mysql_result($rs, $i, 'liae_prefijo'); ?></td> <!-- Prefijo -->
  <td class="tabla_datos"><?php echo mysql_result($rs, $i, 'liae_dk'); ?></td> <!-- DK -->
  <td class="tabla_datos_iconos">
    <div class="btn-group btn-group-sm">
      <a class="btn" href="javascript:editar(<?php echo mysql_result($rs, $i, 'liae_id'); ?>)">
        <!-- SVG editar -->
        ✏️
      </a>
      <a class="btn" href="javascript:borrar(<?php echo mysql_result($rs, $i, 'liae_id'); ?>)">
        <!-- SVG borrar -->
        🗑️
      </a>
    </div>
  </td> <!-- Botones -->
</tr>


<?php
$i++;
}
?>
</tbody>
</table>
</div>

