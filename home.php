<?php

if (!isset($_GET['mes']) || empty($_GET['mes'])) {

  $mes = date('Ym');
} else {

  $mes = $_GET['mes'];
}

if ($mes == '') $mes = date('Ym');

$cade_labels = "";
$cade_data = "";

// Casos por estado
$sql = "SELECT 
  COUNT(caes_id) casos_totales,
  COUNT(CASE WHEN caes_id = 1 THEN 1 END) as casos_abiertos,
  COUNT(CASE WHEN caes_id = 2 THEN 1 END) as casos_cerrados,
  COUNT(CASE WHEN caes_id = 3 THEN 1 END) as casos_proceso,
  (COUNT(CASE WHEN caes_id = 1 THEN 1 END) * 100 / (COUNT(CASE WHEN caes_id = 1 THEN 1 END) + COUNT(CASE WHEN caes_id = 2 THEN 1 END))) as porcentaje_abiertos,
  (COUNT(CASE WHEN caes_id = 2 THEN 1 END) * 100 / (COUNT(CASE WHEN caes_id = 1 THEN 1 END) + COUNT(CASE WHEN caes_id = 2 THEN 1 END))) as porcentaje_cerrados
FROM casos";

$casos_estado = mysql_fetch_assoc(mysql_query($sql));

// Casos por departamentos
$sql = "SELECT 
  COUNT(caso_id) contador,
  (SELECT depa_nombre FROM departamentos WHERE depa_id = a.depa_id) depa_nombre 
  FROM casos a 
  GROUP BY depa_id";

$casos_departamentos = mysql_query($sql);

$cade_data = "[";
while ($fila = mysql_fetch_assoc($casos_departamentos)) {
  $cade_labels .= "'" . $fila["depa_nombre"] . "', ";
  $cade_data .= "'" . $fila["contador"] . "', ";
}
$cade_labels = substr($cade_labels, 0, -2);
$cade_data = substr($cade_data, 0, -2);
$cade_data .= "]";

//Casos recibidos por mes
$sql = "SELECT 
  COALESCE(COUNT(c.caso_id), 0) AS contador,
  m.mes_id,
  m.mes_nombre
FROM meses m
LEFT JOIN casos c ON MONTH(c.caso_fecha_creacion) = m.mes_id
GROUP BY m.mes_id, m.mes_nombre
ORDER BY m.mes_id
";
$casos_recibidos_meses = mysql_query($sql);

$casos_meses = [
  "Enero" => 0,
  "Febrero" => 0,
  "Marzo" => 0,
  "Abril" => 0,
  "Mayo" => 0,
  "Junio" => 0,
  "Julio" => 0,
  "Agosto" => 0,
  "Septiembre" => 0,
  "Octubre" => 0,
  "Noviembre" => 0,
  "Diciembre" => 0
];

while ($fila = mysql_fetch_assoc($casos_recibidos_meses)) {
  $casos_meses[$fila["mes_nombre"]] = $fila["contador"];
}

?>

<section class="content">
  <div class="container-fluid">
    <div class="row" id="sms_1">
      <div class="col-md-6">
        <!-- CASOS POR DEPARTAMENTOS CHART -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Casos por Departamentos</h3>

            <!-- <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div> -->
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas id="casos_departamentos" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>

          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <div class="col-md-6" id="sms_2">
        <!-- CASOS POR ESTADO CHART -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Casos por estado</h3>

            <!-- <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div> -->
          </div>
          <div class="card-body d-flex flex-wrap">
            <canvas id="mychart1" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            <!-- <div style="align-content: center">
              <h6 style="20px">Abiertos: <?php //echo $casos_estado["casos_abiertos"]
                                          ?></h6>
              <h6 style="20px">Cerrados: <?php //echo $casos_estado["casos_cerrados"]
                                          ?></h6>
              <h6 style="20px">En Proceso: <?php //echo $casos_estado["casos_proceso"]
                                            ?></h6>
            </div> -->
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>

    <!-- Casos por mesess -->
    <div class="row" id="sms_1">
      <div class="col-md-6">
        <!-- CASOS POR MESES CHART -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Casos por Meses</h3>

            <!-- <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div> -->
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas id="casos_meses" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>

          </div>
          <!-- /.card-body -->
        </div>
      </div>

      <div class="col-md-6">
        <!-- CASOS HASTA EL MOMENTO -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title">Casos hasta el momento</h3>

            <!-- <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div> -->
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas id="casos_hasta_momento" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>

          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>
  </div>
</section>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const casos_estado = document.getElementById("mychart1")
  const casos_departamentos = document.getElementById("casos_departamentos")
  const casos_meses = document.getElementById("casos_meses")
  const casos_hasta_momento = document.getElementById("casos_hasta_momento")

  // const data = ;

  new Chart(casos_estado, {
    type: 'doughnut',
    data: {
      labels: [
        'Abiertos <?php echo $casos_estado["casos_abiertos"] ?>',
        'Cerrados <?php echo $casos_estado["casos_cerrados"] ?>',
        'En proceso <?php echo $casos_estado["casos_proceso"] ?>'
      ],
      datasets: [{
        data: [<?php echo !empty($casos_estado["casos_abiertos"]) ? $casos_estado["casos_abiertos"] : 0 ?>, <?php echo !empty($casos_estado["casos_cerrados"]) ? $casos_estado["casos_cerrados"] : 0  ?>, <?php echo !empty($casos_estado["casos_proceso"]) ? $casos_estado["casos_proceso"] : 0 ?>],
        backgroundColor: [
          'rgb(54, 162, 235)',
          'rgb(255, 99, 132)',
          'rgb(255, 205, 86)'
        ],
        hoverOffset: 4
      }]
    },
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Casos por departamentos'
      }
    }
  })

  new Chart(casos_departamentos, {
    type: 'bar',
    data: {
      labels: [<?php echo !empty($cade_labels) ? $cade_labels : 0 ?>],
      datasets: [{
        label: "Cantidad de casos",
        data: <?php echo !empty($cade_data) ? $cade_data : 0 ?>
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Casos por departamentos'
        }
      }
    },
  })

  let contador = <?php echo json_encode($casos_meses); ?>

  let datosCompletos = Object.values(contador).map(function(value) {
    return value || 0;
  });

  new Chart(casos_meses, {
    type: 'bar',
    data: {
      labels: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
      datasets: [{
        label: "Cantidad de casos",
        data: datosCompletos,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Casos por meses'
        }
      }
    },
  })

  new Chart(casos_hasta_momento, {
    type: 'doughnut',
    data: {
      labels: [
        'Abiertos <?php echo $casos_estado["porcentaje_abiertos"] ?>%',
        'Cerrados <?php echo $casos_estado["porcentaje_cerrados"] ?>%',
      ],
      datasets: [{
        data: [<?php echo !empty($casos_estado["porcentaje_abiertos"]) ? $casos_estado["porcentaje_abiertos"] : 0 ?>, <?php echo !empty($casos_estado["porcentaje_cerrados"]) ? $casos_estado["porcentaje_cerrados"] : 0  ?>],
        backgroundColor: [
          'rgb(54, 162, 235)',
          'rgb(255, 99, 132)'
        ],
        hoverOffset: 4
      }]
    },
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Casos por departamentos'
      }
    }
  })
</script>