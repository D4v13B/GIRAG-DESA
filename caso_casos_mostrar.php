<?php
session_start();

$user_check = $_SESSION['login_user'];

include('conexion.php');
include("funciones.php");
$aprueba = obtener_valor("SELECT usua_sms_aprueba FROM usuarios WHERE usua_id = $user_check", "usua_sms_aprueba");

// Variable para verificar si el usuario es administrador
$es_administrador = $_SESSION["administrador_caso"] == 1;
?>
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
</style>
<!-- Context Menu -->
<ul id="contextMenu" class="dropdown-menu">
  <?php if ($es_administrador): ?>
    <li><a id="btn-editar" class="dropdown-item" href="#" data-action="edit">Editar</a></li>
    <li><a id="btn-delete" class="dropdown-item" href="#" data-action="delete">Borrar</a></li>
  <?php endif; ?>
  <li><a id="btn-view" class="dropdown-item" href="#" data-action="view">Ver Detalles</a></li>
</ul>

<div class="table-responsive table-striped table-bordered table-hover table-sm" style="text-align: center; align-items:center">
  <table class="table align-middle">
    <thead class="thead-dark">
      <tr style="background-color: rgba(147, 147, 147, 0.05)">
        <th scope="col">ID</th>
        <th scope="col">Descripción</th>
        <th scope="col">Revisado por</th>
        <th scope="col">Estado</th>
        <th scope="col">Departamento afectado</th>
        <th scope="col">Tipo</th>
        <th scope="col">Ubicación</th>
        <th scope="col">Incidencia Seguridad Operacional</th>
        <th scope="col">Incidencia de Procesos</th>
        <th scope="col">Impacto Económico</th>
        <th scope="col">Impacto Personas</th>
        <th scope="col">Impacto Medio Ambiente</th>
        <th scope="col">Equipos</th>
        <th scope="col">Fecha de Incidencia</th>
        <th scope="col">Fecha de Creación</th>
        <th scope="col">Cerrador por</th>
        <th scope="col">Fecha de Cierre</th>
        <th scope="col">Responsable de acciones</th>
        <th scope="col">Programa de Gestión</th>
        <th scope="col">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $usua_id_revisado = $_GET['usua_id_revisado'];
      $cati_id = $_GET["cati_id"];
      $equi_id = $_GET["equi_id"];
      $usua_id_aprobado = $_GET["usua_id_aprobado"];
      $usua_id_asignado = $_GET["usua_id_asignado"];
      $depa_id = $_GET["depa_id"];
      $caes_id = $_GET["caes_id"];
      $caso_id = $_GET["caso_id"];
      $referencia = $_GET["referencia"];


      $where = '';

      if ($usua_id_revisado != '') $where .= " AND  a.usua_id_revisado IN ($usua_id_revisado)";
      if ($cati_id != "") $where .= " AND a.cati_id IN ($cati_id)";
      if ($equi_id != "") $where .= " AND a.equi_id IN ($equi_id)";
      if ($usua_id_aprobado != "") $where .= " AND a.usua_id_aprobado IN ($usua_id_aprobado)";
      if ($usua_id_asignado != "") $where .= " AND a.usua_id_asignado IN ($usua_id_asignado)";
      if ($caes_id != "") $where .= " AND a.caes_id IN ($caes_id)";
      if ($depa_id != "") $where .= " AND a.depa_id IN ($depa_id)";
      if ($caso_id != "") $where .= " AND a.caso_id IN ($caso_id)";
      if ($referencia != "") $where .= " AND a.caso_referencia like '%$referencia%'";

      if ($_SESSION["administrador_caso"] == 0) {
        $qsql = "SELECT DISTINCT
          a.caso_fecha_analisis,
          a.caes_id,
          a.caso_id,
          a.caso_descripcion,
          b.cati_nombre,
          i.inso_nombre,
          h.inpr_nombre,
          a.caso_fecha_creacion,
          c.depa_nombre,
          a.caso_ubicacion,
          e.imec_nombre,
          f.imma_nombre,
          d.equi_nombre,
          a.caso_fecha,
          a.caso_nota,
          g.impe_nombre,
          a.usua_id_aprobado,
          a.usua_id_revisado,
          (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_revisado) AS revisado,
          (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_aprobado) AS aprobado,
          (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_asignado) AS usua_asignado,
          (SELECT usua_nombre FROM usuarios WHERE usua_id = a.usua_id_cerrado) AS usua_cerrado,
          (SELECT depa_nombre FROM departamentos WHERE depa_id = a.depa_id) AS depa_nombre,
          (SELECT caes_nombre FROM casos_estado WHERE caes_id = a.caes_id) AS caso_estado
        FROM
          casos a
        LEFT JOIN
          casos_tareas j ON a.caso_id = j.caso_id
        LEFT JOIN
          casos_tipos b ON a.cati_id = b.cati_id
        LEFT JOIN
          departamentos c ON a.depa_id = c.depa_id
        LEFT JOIN
          equipos d ON a.equi_id = d.equi_id
        LEFT JOIN
          impacto_economico e ON a.imec_id = e.imec_id
        LEFT JOIN
          impacto_medio_ambiente f ON a.imma_id = f.imma_id
        LEFT JOIN
          impacto_personas g ON a.impe_id = g.impe_id
        LEFT JOIN
          incidencia_procesos h ON a.inpr_id = h.inpr_id
        LEFT JOIN
          incidencia_seg_op i ON a.inso_id = i.inso_id
        WHERE
          a.usua_id_asignado = '$user_check'
          OR j.usua_id = '$user_check'
          OR j.usua_id_2 = '$user_check'
          OR j.usua_id_3 = '$user_check'
          OR a.usua_id_encargado_aprobacion = '$user_check'
          OR a.usua_id_encargado_aprobacion2 = '$user_check'
          OR a.usua_id_encargado_aprobacion3 = '$user_check'
          OR a.usua_id_encargado_revision = '$user_check'
          OR a.usua_id_encargado_revision2 = '$user_check'
          OR a.usua_id_encargado_revision3 = '$user_check'
        ORDER BY
          caso_id DESC";



      } elseif ($_SESSION["administrador_caso"] == 1) {
        $qsql = "SELECT caso_fecha_analisis, caes_id, caso_id, caso_descripcion, cati_nombre, inso_nombre, inpr_nombre, caso_fecha_creacion, depa_nombre, caso_ubicacion, imec_nombre, imma_nombre, equi_nombre, caso_fecha, caso_nota, impe_nombre, usua_id_aprobado, usua_id_revisado,caso_fecha_cierre,usua_id_encargado_revision, usua_id_encargado_aprobacion,
        (SELECT usua_nombre FROM usuarios WHERE usua_id = usua_id_revisado) revisado,
        (SELECT usua_nombre FROM usuarios WHERE  usua_id = usua_id_aprobado) aprobado,
        (SELECT usua_nombre FROM usuarios WHERE usua_id=usua_id_asignado) usua_asignado,
        (SELECT usua_nombre FROM usuarios WHERE usua_id=usua_id_cerrado) usua_cerrado,
        (SELECT depa_nombre FROM departamentos WHERE depa_id=a.depa_id) depa_nombre,
        (SELECT caes_nombre FROM casos_estado WHERE caes_id=a.caes_id) caso_estado
        FROM casos a, casos_tipos b, departamentos c, equipos d, impacto_economico e, impacto_medio_ambiente f, impacto_personas g, incidencia_procesos h, incidencia_seg_op i
        WHERE a.cati_id=b.cati_id
        AND a.depa_id=c.depa_id
        AND a.equi_id=d.equi_id
        AND a.imec_id=e.imec_id
        AND a.imma_id=f.imma_id
        AND a.impe_id=g.impe_id
        AND a.inpr_id=h.inpr_id
        AND a.inso_id=i.inso_id
        $where
        ORDER BY caso_id DESC";
      }
      //echo $qsql;
      $rs = mysql_query($qsql);
      $num = mysql_num_rows($rs);
      $i = 0;
      while ($i < $num) {
      ?>
        <tr>
          <td><?php echo mysql_result($rs, $i, 'caso_id'); ?></td>
          <td>
            <a href="index.php?p=detalle-caso&caso=<?php echo mysql_result($rs, $i, 'caso_id'); ?>">
              <?php echo mysql_result($rs, $i, 'caso_descripcion'); ?>
            </a>
          </td>
          <td><?php echo mysql_result($rs, $i, 'revisado'); ?></td> <!-- Revisado por-->
          <td>
            <?php
              $casoEstado = mysql_result($rs, $i, 'caso_estado');
              if($casoEstado == "Abierto"){ //Este caso esta abierto
                echo "<span class='btn btn-success btn-sm'>".$casoEstado."</span>";
              }elseif($casoEstado == "Cerrado"){//Cerrado
                echo "<span class='btn btn-danger btn-sm'>".$casoEstado."</span>";
              }elseif($casoEstado == "En Proceso"){//Proceso
                echo "<span class='btn btn-warning btn-sm'>".$casoEstado."</span>";
              }else{
                echo $casoEstado;
              }
            ?></td>
          <td><?php echo mysql_result($rs, $i, 'depa_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'cati_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'caso_ubicacion'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'inso_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'inpr_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'imec_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'impe_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'imma_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'equi_nombre'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'caso_fecha'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'caso_fecha_creacion'); ?></td>
          <td><?php echo mysql_result($rs, $i, 'usua_cerrado'); ?></td>
<td>
  <?php
  $casoEstado = mysql_result($rs, $i, 'caso_estado');
  if($casoEstado == "Cerrado") {
    echo mysql_result($rs, $i, 'caso_fecha_cierre');
  } else {
    echo "";  // o puedes dejarlo vacío con solo echo "";
  }
  ?>
</td>
          <td>
            <?php echo mysql_result($rs, $i, 'usua_asignado'); ?> <br>
          </td>
          <td><?php if (mysql_result($rs, $i, 'caso_fecha_analisis') == null) {
                echo "NO";
              } else {
                echo "SI";
              }

              ?></td>
          <td>
            <button class=" btn btn-context-menu" data-id="<?php echo mysql_result($rs, $i, 'caso_id'); ?>">
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
          </td>
        </tr>
      <?php
        $i++;
      }
      ?>
    </tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    const $contextMenu = $("#contextMenu")
    const $contextModal = $("#contextModal")

    // Mostrar el menú contextual al hacer clic en el boton de opciones
    $(".btn-context-menu").on("click", function(e) {
      e.preventDefault()
      e.stopPropagation()

      const casoId = $(this).data("id")

      $contextMenu.css({
        display: "block",
        left: e.pageX - 150,
        top: e.pageY
      });
      $("#btn-editar").attr("href", `javascript:editar(${casoId})`)
      $("#btn-delete").attr("href", `javascript:borrar(${casoId})`)
      $("#btn-view").attr("href", `index.php?p=detalle-caso&caso=${casoId}`)
    });

    // Ocultar el menú contextual al hacer clic en cualquier lugar
    $(document).click(function() {
      $contextMenu.hide()
    });

    $contextMenu.click(function(e) {
      e.stopPropagation()
    });
  })
</script>
