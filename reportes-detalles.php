<script src="jquery/dropzone-5.5.0/dist/dropzone.js?nochk=<?php echo date('Ymdst'); ?>"></script>
<link href="jquery/dropzone-5.5.0/dist/dropzone.css?nochk=<?php echo date('Ymdst'); ?>" rel="stylesheet" />
<?php
$user_id = $_SESSION["login_user"];

$reporte_id = $_GET["id"];
// Consulta para obtener el ID del gerente SMS basado en el reporte
$sql = "SELECT u.usua_nombre FROM reportes_documentos rd JOIN usuarios u ON rd.usua_id_gerente_sms = u.usua_id WHERE rd.redo_id = $reporte_id";
$usuario = mysql_fetch_assoc(mysql_query($sql));
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

// Supongamos que $user_id es el ID del usuario en sesión y $reporte_id es el ID del reporte actual.

// Consulta para obtener el ID del gerente del departamento
$sql = "SELECT usua_id_gerente_departamento 
        FROM reportes_documentos 
        WHERE redo_id = $reporte_id";
$result = mysql_query($sql);
$gerente_row = mysql_fetch_assoc($result);
$id_gerente_departamento = $gerente_row['usua_id_gerente_departamento'];

// Consulta para obtener el ID del presidente
$sql = "SELECT usua_id 
        FROM usuarios 
        WHERE usti_id = '13'";
$result = mysql_query($sql);
$presidente_row = mysql_fetch_assoc($result);
$presidente_id = $presidente_row['usua_id'];

// LA QUE EN REALIDAD DEBEMOS UTILIZAR
// $sql = "SELECT usua_id 
//         FROM usuarios 
//         WHERE usca_id = '27'";
// $result = mysql_query($sql);
// $presidente_row = mysql_fetch_assoc($result);
// $presidente_id = $presidente_row['usua_id'];

// Nos trae todos los usuarios que tienen parte en nuestro proyecto
$sql = "
SELECT 
    rd.redo_id, 
    rd.redt_id,
    rd.usua_id_gerente_sms, 
    rd.usua_id_gerente_departamento, 
    rd.usuario_encargado_aprobacion,
    u1.usua_nombre AS nombre_gerente_sms,
    u2.usua_nombre AS nombre_gerente_departamento,
    u3.usua_nombre AS nombre_encargado_aprobacion
FROM 
    reportes_documentos rd
LEFT JOIN 
    usuarios u1 ON rd.usua_id_gerente_sms = u1.usua_id
LEFT JOIN 
    usuarios u2 ON rd.usua_id_gerente_departamento = u2.usua_id
LEFT JOIN 
    usuarios u3 ON rd.usuario_encargado_aprobacion = u3.usua_id
WHERE 
    rd.redo_id = $reporte_id
";

$usuarios = mysql_query($sql);

// Inicializar variables para verificar
$usuario_sms = null;              // Gerente SMS
$usuario_revision = null;         // Gerente del departamento
$usuario_aprobacion = null;       // Usuario encargado de aprobación

if ($usuarios) {
  // Obtener los resultados
  $fila = mysql_fetch_assoc($usuarios);

  // Extraer los IDs para la verificación
  $usuario_sms = $fila['usua_id_gerente_sms'];
  $usuario_revision = $fila['usua_id_gerente_departamento'];
  $usuario_aprobacion = $fila['usuario_encargado_aprobacion'];
}

// Verificar si el usuario en sesión es el presidente, el gerente del departamento, 
// el gerente SMS o el usuario encargado de aprobación
// Verificar si el usuario en sesión es el presidente, el gerente del departamento,
// el gerente SMS o el usuario encargado de aprobación, con la condición adicional
// de que si redt_id == 6, solo se muestra al presidente.
$mostrar_boton_revision = (
  ($user_id == $usuario_revision || $user_id == $usuario_aprobacion) || 
  ($fila["redt_id"] == 6 && $user_id == $presidente_id)
);

// Verificar si el usuario en sesión es el gerente SMS
$mostrar_boton_sms = (
  $user_id == $usuario_sms
);

// Mostrar botones según la verificación
if ($mostrar_boton_revision) {

  echo '<script>console.log("Botón de revisión visible");</script>';
}

if ($mostrar_boton_sms) {

  echo '<script>console.log("Botón de SMS visible");</script>';
}

if (!$mostrar_boton_revision && !$mostrar_boton_sms) {
  echo '<script>console.log("Botón oculto");</script>';
}



?>

<!--  -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<!-- Modal -->
<div class="modal fade" id="pendientesModal" tabindex="-1" role="dialog" aria-labelledby="pendientesModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendientesModalLabel">Gerentes pendientes por aceptar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="lista-pendientes">
        <!-- Aquí se insertan los nombres -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


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

          <div class="container text-start p-3">
            <button class="btn btn-secondary btn-sm" onclick="irAtras()" title="Regresar">
              <i class="bi bi-arrow-left"></i>
            </button>
          </div>
          <div class="d-flex justify-content-between w-100">

            <h3 class="card-title"><?php echo $reporteDetalle["redo_titulo"]; ?></h3>

            <!-- <br>

            <?php if (!empty($reporteDetalle["redo_ref"])) : ?>

              <div>
                <a href="manuales-uso/<?php echo $reporteDetalle["redo_ref"]; ?>"><?php echo $reporteDetalle["redo_ref"]; ?></a>
              </div>
            <?php endif; ?> -->
            <div class="d-flex justify-content-left ">
              <!-- <?php if ($mostrar_boton_sms) : ?>
                <button onclick="AprobarFirma()" id="aprobar_firma" type="button" class="btn btn-success mr-2 aprobar_firma">
                  Aprobar Firma del documento
                </button>
              <?php endif; ?> -->


              
              <!-- Botón para aceptar un reporte -->
              <?php if ($mostrar_boton_revision) : ?>
                <button onclick="ActualizarReporte()" id="aceptar_reporte" type="button" class="btn btn-success mr-2 aceptar_reporte">
                  Firmar Documento
                </button>
                <?php endif; ?>
                <!-- Contenedor donde se insertará dinámicamente el estado de la politica -->
              <div id="boton-pendientes"></div>




              <!-- Buton para agregar bitacora -->

              <button type="button" class="btn_agregar_documento btn btn-primary btn btn-primary mr-2" data-toggle="modal" data-target="#nuevaBitacora">

                Agregar

              </button>

              <!-- <script>

                $("#aceptar_reporte").hide()

              </script> -->

              <!-- Buton de estado del reporte-->

              <button class="btn <?php echo $claseBtnEstado ?>" value="<?php echo $reporteDetalle["rede_id"] ?>" disabled><?php echo $reporteDetalle["estado"] ?></button>

            </div>



          </div>

          <?php if (!empty($reporteDetalle["redo_ref"])) : ?>

            <div>

              <a href="https://view.officeapps.live.com/op/embed.aspx?wdPrint=0&src=https://giraglogicdesa.girag.aero/manuales-uso/<?php echo $reporteDetalle['redo_ref']; ?>" target="_blank">
                <?php echo $reporteDetalle['redo_ref']; ?>
              </a>



            </div>

          <?php endif; ?>
          <p>Subido por: <?php echo htmlspecialchars($usuario['usua_nombre']); ?></p>
        </div>




        <!-- tabla de bitacora -->

        <div class="card-body">

          <div class="table-responsive">



            <div class="table-responsive px-3">

              <table class="table table-bordered w-100 table-sm text-center" id="tablaBitacora">

                <thead class="bg-dark">

                  <tr>

                    <th style="display: none;">ID Bitacora</th>





                    <th>Documento</th>



                    <th>Fecha de Creación</th>



                    <th> Estado</th>
                    <th> Firmado</th>
                    <th>Procesado Por</th>



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

          <input type="hidden" id="estado" name="estado_id">

          <input type="hidden" id="bitacora" name="bitacora_id">

          <input type="hidden" id="redo_id" name="redo_id" value="<?php echo $_GET["id"] ?>">

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
let nombresPendientes = [];

function cargarPendientes(reporte_id) {
  $.ajax({
    url: "ajax/politicas_gerentes_firmas.php",
    method: "GET",
    data: {
      reporte_id: reporte_id
    },
    success: function (res) {
      let data = JSON.parse(res);
      let contenedor = $("#boton-pendientes");
      contenedor.html(""); // Limpiar contenido previo

      if (data.pendientes > 0) {
        nombresPendientes = data.nombres; // Guarda globalmente

        contenedor.html(`
          <button type="button" class="btn btn-warning mr-2" style="position: relative;" onclick="mostrarPendientes()">
              Pendientes por aceptar
              <span class="badge badge-light" style="
                  position: absolute;
                  top: -10px;
                  right: -10px;
                  background-color: red;
                  color: white;
                  border-radius: 50%;
                  width: 24px;
                  height: 24px;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  font-size: 12px;
              ">${data.pendientes}</span>
          </button>
        `);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error cargando pendientes:", error);
    }
  });
}

function mostrarPendientes() {
  let listaHtml = '<ul>';
  nombresPendientes.forEach(nombre => {
    listaHtml += `<li>${nombre}</li>`;
  });
  listaHtml += '</ul>';
  $("#lista-pendientes").html(listaHtml);
  $("#pendientesModal").modal("show");
}

  // Ejecutar cuando el DOM esté completamente cargado
  $(document).ready(function() {
    cargarPendientes(<?php echo $reporte_id; ?>);
  });


  function resetForm() {

    $('#formulario-rechazo').trigger('reset')

  }

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
        resetForm()

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

        alert("Documento aceptado");

        traerBitacoras();
        cargarPendientes(<?php echo $reporte_id; ?>);

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



        alert("Documento enviado exitosamente");

        traerBitacoras();
        cargarPendientes(<?php echo $reporte_id; ?>);

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
        alert("Documento Firmado Exitosamente");
        traerBitacoras();
        cargarPendientes(<?php echo $reporte_id; ?>);
      },
      error: function(xhr) {
        // Intenta leer el mensaje de error devuelto por el servidor en formato JSON
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.error) {
            alert(response.error); // Mostrar el mensaje del servidor
          } else {
            alert("Ocurrió un error inesperado.");
          }
        } catch (e) {
          alert("Error al procesar la respuesta del servidor.");
        }
      }
    });
  }

  // Función que envia la notificación a los gerentes, para que sepan que ya pueden firmar el documento
  function AprobarFirma() {

    $.ajax({
      url: "ajax/aprobar_firma.php?id=" + <?php echo isset($_GET['id']) ? $_GET['id'] : 'null'; ?>,
      method: "POST",
      dataType: "json",
      data: JSON.stringify({
        reporte_id: <?php echo isset($_GET['id']) ? $_GET['id'] : 'null'; ?>
      }),
      success: function(result) {
        if (result.success) {
          alert("Aprobación de firma enviada correctamente");
          console.log(result.data);
        } else {
          alert("Error: " + result.message);
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        console.error("Server Response:", xhr.responseText);
        console.error("Status:", textStatus);
        console.error("Error:", errorThrown);
        alert("No se pudo completar la solicitud. Inténtalo nuevamente.");
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
          cargarPendientes(<?php echo $reporte_id; ?>);

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


  cargarPendientes(<?php echo $reporte_id; ?>);


  // $(function() {

  //   //DESHABILITO LOS CONTROLES QUE SON EXCLUSIVOS POR ROL



  //   $(".btn_agregar_documento").hide();



  //   ejecutarPermisos();

  // });



  // function ejecutarPermisos() {



  //   <?php echo pantalla_roles("index.php?p=reportes-detalles", $_SESSION["login_user"]) ?>

  // }

  function irAtras() {
    window.history.back(); // Navega a la página anterior
  }
</script>