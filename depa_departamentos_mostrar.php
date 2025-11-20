<?php include('conexion.php'); ?> 



<div class='table-responsive table-striped table-bordered table-hover table-sm' style='text-align: center; align-items:center'>

<table class=table align-middle>

<thead class='thead-dark'>

<tr>

<th class=tabla_datos_titulo>Departamento</th>

<th class=tabla_datos_titulo>Correo</th>

<th class=tabla_datos_titulo_icono></th>

</tr>

</thead>

<tbody>

<?php

$nombre=$_GET['nombre'];



$qsql ='select * from departamentos';



$rs = mysql_query($qsql);

$num = mysql_num_rows($rs);

$i=0;

while ($i<$num)

{

?>

<tr class='tabla_datos_tr'>

<td class=tabla_datos><?php echo mysql_result($rs, $i, 'depa_nombre'); ?></td>

<td class=tabla_datos><?php echo mysql_result($rs, $i, 'depa_correo'); ?></td>



            <td>

            <div Class='btn-group btn-group-sm'>

                     <a Class='btn' href='javascript:editar(<?php echo mysql_result($rs, $i, 'depa_id'); ?>)' ;>

                        <svg style = 'width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>

                           <path fill = '#FFD43B' d='M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z' />

                        </svg>

                     </a>

                     <a Class='btn' href='javascript:borrar(<?php echo mysql_result($rs, $i, 'depa_id'); ?>)' ;>

                        <svg style = 'width: 22px;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->

                           <path fill = '#ad0000' d='M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z' />

                        </svg>

                     </a>

                     <a target="_blank" style="font-size: 22px;" href="pdfqr.php?depa=<?php echo mysql_result($rs, $i, 'depa_id')?>&tipo=FORM">
                     <i class="fa-solid fa-print"></i>
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



