<script src="jquery/dropzone-5.5.0/dist/dropzone.js?nochk=<?php echo date('Ymdst'); ?>"></script>
<link href="jquery/dropzone-5.5.0/dist/dropzone.css?nochk=<?php echo date('Ymdst'); ?>" rel="stylesheet" />
<?php


$reporte_id = $_GET["id"];
$claseBtnEstado = "btn-primary";

/**
 *Me trae los datos generales del manuel de uso 
 *@var $claseBtnEstado -- Es la clase de bootsrap que vamos a verificiar segun el estado y asignar algun color
 */

$sql = "SELECT rd.*, 
(SELECT rede_nombre FROM reportes_documentos_estado WHERE rd.rede_id = rede_id) estado
FROM reportes_documentos rd WHERE redo_id = '$reporte_id'";
$reporteDetalle = mysql_fetch_assoc(mysql_query($sql));

switch ($reporteDetalle["rede_id"]) {
  case 1: //En proceso
    $claseBtnEstado = "btn-warning";
    break;
  case 2:
    $claseBtnEstado = "btn-danger";
    break;
  case 3:
    $claseBtnEstado = "btn-success";
  default:
    break;
}

/**
 * Query que me trae las bitacoras y la ejecucion se guarda en 
 * @var $reporteBitacoras -- Tiene que recorrerse de la siguien manera
 * 
 * while($fila = mysql_fetch_assoc($reporteBitacoras){
 *  --Implementar la logica
 *  --EJMPLO: Para acceder a un indice es de la siguiente manera => $fila["redb_id"]
 * }
 */


$sql = "SELECT *, (SELECT rede_nombre FROM reportes_documentos_estado WHERE rd.rede_id = rede_id) estado 
FROM reportes_documentos_bitacora rd WHERE redo_id = $reporte_id";
$reporteBitacoras = mysql_query($sql);

$redo_id = $reporteDetalle['redo_id'];
$sql = "SELECT rede_id FROM reportes_documentos_bitacora WHERE redo_id = $redo_id ORDER BY redb_id DESC LIMIT 1";
$resultadoConsulta = mysql_query($sql);
// 
if ($resultadoConsulta && mysql_num_rows($resultadoConsulta) > 0) {
  $resultado = mysql_fetch_assoc($resultadoConsulta);
  $estado_last_document = $resultado["rede_id"];
} else {
  // No hay documentos en la bitacora
  $estado_last_document = null;
}

?>



<!--  -->
<!-- Modal subir nuevo documento a la bitacora de documentos -->
<div class="modal fade" id="nuevaBitacora" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class=" p-2 col-12 col-md-8  m-auto">
          <input type="hidden" value="<?php echo $reporte_id ?>" id="h_id">
          <form class="dropzone" id="frm_dropzone">
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal que despliega el comentario de un documento rechazado-->
<div class="modal fade" id="ComentarioModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"> Comentario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>

<!-- Contenido de la tabla de bitacoras -->
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">

        <div class="card-header">
          <div class="d-flex justify-content-between w-100">
            <h3 class="card-title"><?php echo $reporteDetalle["redo_titulo"]; ?></h3>
            <!-- <br>
            <?php if (!empty($reporteDetalle["redo_ref"])) : ?>
              <div>
                <a href="manuales-uso/<?php echo $reporteDetalle["redo_ref"]; ?>"><?php echo $reporteDetalle["redo_ref"]; ?></a>
              </div>
            <?php endif; ?> -->

            <div class="d-flex justify-content-left ">

              <!-- Buton para aceptar un reporte  -->
              <button onclick="ActualizarReporte()" id="aceptar_reporte" type="button" class="btn btn-success mr-2">
                Aceptar Reporte
              </button>
              <!-- Buton para agregar bitacora -->
              <button type="button" class="btn_agregar_documento btn btn-primary btn btn-primary mr-2" data-toggle="modal" data-target="#nuevaBitacora">
                Agregar
              </button>
              <script>
                $("#aceptar_reporte").hide()
              </script>
              <!-- Buton de estado del reporte-->
              <button class="btn <?php echo $claseBtnEstado ?>" value="<?php echo $reporteDetalle["rede_id"] ?>" disabled><?php echo $reporteDetalle["estado"] ?></button>
            </div>
        
          </div>
          <?php if (!empty($reporteDetalle["redo_ref"])) : ?>
              <div>
                <a href="manuales-uso/<?php echo $reporteDetalle["redo_ref"]; ?>" target="_blank"><?php echo $reporteDetalle["redo_ref"]; ?></a>
                 
              </div>
            <?php endif; ?>
        </div>
        
        <!-- tabla de bitacora -->
        <div class="card-body">
          <div class="table-responsive">

            <div class="table-responsive px-3">
              <table class="table table-bordered w-100 table-sm text-center" id="tablaBitacora">
                <thead class="bg-dark">
                  <tr>
                    <th>ID Bitacora</th>


                    <th>Documento</th>

                    <th>Fecha de Creación</th>

                    <th> Estado</th>

                    <th></th>

                  </tr>
                </thead>

                <tbody id="tbody-reporte-bitacora">
                  <tr>
                    <td colspan="5">
                      Cargando...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>
</div>
<!-- /.content-wrapper -->

<!-- Modal que despliega un espacio, para indicar porque se rechaza el documento-->
<div class="modal fade" id="modal-formulario-rechazo" tabindex="-1" aria-labelledby="modal-formulario-rechazo" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Formulario de rechazó</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formulario-rechazo">
          <input type="text" id="estado" name="estado_id">
          <input type="text" id="bitacora" name="bitacora_id">
          <input type="text" id="redo_id" name="redo_id" value="<?php echo $_GET["id"] ?>">
          <div class="form-group">
            <label for="comentario">Motivo del rechazo</label>
            <textarea class="form-control" id="comentario" rows="3" name="comentario"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <span data-dismiss="modal" type="button" class="btn btn-primary control-estado" onclick="enviarRetro()">Enviar retroalimentacion </span>
      </div>
    </div>
  </div>
</div>
</div>

<script>
  // Función que se activa, cuando el usuario rechaza el reporte, (solicita la causa del rechazo)
  $("#modal-formulario-rechazo").on("show.bs.modal", function(event) {
    let btn = $(event.relatedTarget)
    const estado = btn.data("estado")
    const bitacora_id = btn.data("bitacora")

    let modal = $("#formulario-rechazo")
    modal.find("#estado").val(estado)
    modal.find("#bitacora").val(bitacora_id) // Corregido
  })

  // $(document).ready(function() {
  //   $(".control-estado").on("click", function() {
  //     console.log("Hola")
  //     enviarRetro($(this).data('estado'), $(this).data('bitacora'))
  //   })
  // })

  // Funcion que envia el comentario y el estado a la base de datos
  function enviarRetro() {
    /*
     *Enviar la retroalimentacion y el estado de actualizacion, en caso tal de que este aprobada, se va a enviar sin comentario
     */
    const datos = new FormData($("#formulario-rechazo")[0]);
    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "POST",
      contentType: false,
      processData: false,
      data: datos,
      success: function(res) {
        traerBitacoras()
      }
    })
  }
  // Función que trae el comentario y lo muestra al usuario
  function traerRetro(id_bitacora) {
    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "GET",
      data: {
        id_bitacora: id_bitacora
      },
      success: function(res) {
        let comentario = JSON.parse(res).redb_comentario
        console.log(comentario);
        $("#ComentarioModal .modal-body").html(comentario);
        $("#ComentarioModal").modal("show");
      }
    });
  }
  // Función que aprueba un reporte de la bitacora
  function aprobarReporte(estado, bitacora_id, tipo) {
    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "POST",
      data: {
        estado_id: estado,
        bitacora_id: bitacora_id,
        tipo: tipo
      },
      success: function(stmt) {
        // Se ejecuta cuando la petición AJAX es exitosa
        alert("Reporte aceptado");
        traerBitacoras()
      }
    });
  }

  // Función para cargar un nuevo documento en la bitacora
  $("#cargar-archivo").on("click", function() {
    const datos = new FormData($("#NuevaBitacora")[0])

    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "POST",
      data: datos,
      processData: false,
      contentType: false,
      success: res => {
        console.log();
      }
    })
  })
  // Función para subir el reporte, usando Dropzone
  Dropzone.autoDiscover = false;
  $(function() {
    $("#frm_dropzone").dropzone({
      url: "ajax/reportes-detalles-uploader.php?id=" + $('#h_id').val(),
      maxFiles: 100,
      paramName: "file",
      timeout: 300000,
      maxFilesize: 20,
      success: function(file, respuesta) {

        alert("Reporte enviado exitosamente");
        traerBitacoras()
      }
    });
  });


  // Función para subir el ultimo reporte aprobado a la BD, colocarle las firmas y actuzalizar la base de datos.
  function ActualizarReporte() {
    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "PUT",
      contentType: "application/json",
      data: JSON.stringify({
        redo_id: <?php echo $_GET['id'] ?>
      }),
      success: function(stmt) {
        // Se ejecuta cuando la petición AJAX es exitosa
        // alert("Mensaje enviado exitosamente");
        // window.location.reload();
        traerBitacoras()
      },
      error: (xhr, textStatus, errorThrown) => {
        alert(xhr.responseText);
      }
    });
  }
  // Función que carga la información en la tabla bitacora.
  function traerBitacoras() {
    $.ajax({
      url: "ajax/reporte_documento_detalle.php",
      method: "GET",
      data: {
        redo_id: <?php echo $_GET["id"] ?>
      },
      success: res => {
        // console.log(res);
        $("#tbody-reporte-bitacora").html(res)
        // EventListener
        $(".traer-retro").on("click", function() {
          var id_bitacora = $(this).data("id-bitacora");
          traerRetro(id_bitacora);
        });

        $(".aprobar-reporte").click(function() {
          var estado = $(this).data("estado");
          var bitacora_id = $(this).data("bitacora");
          var tipo = $(this).data("tipo");
          aprobarReporte(estado, bitacora_id, tipo);

        });
      }
    })
  }

  traerBitacoras()


  $(function() {
    //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL

    $(".btn_agregar_documento").hide();

    ejecutarPermisos();
  });

  function ejecutarPermisos() {

    <?php echo pantalla_roles("index.php?p=reportes-detalles", $_SESSION["login_user"]) ?>
  }
</script>