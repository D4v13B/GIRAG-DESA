<?php

//Servicios
$sql = "SELECT * FROM carga_servicios ORDER BY case_nombre";
$servicios = mysql_query($sql);

$sql = "SELECT *
FROM forma_pago
ORDER BY
    CASE
        WHEN fopa_nombre = 'EFECTIVO' THEN 0
        WHEN fopa_nombre = 'Transf/Deposito cta. Bancaria' THEN 1
        ELSE 2
        END,
    fopa_nombre";
$fopa_res = mysql_query($sql);

?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div id="facturaContainer"></div>
<!-- Modal para el detalle de caja -->
<div class="modal fade" id="modalCajaDetalles" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog " style="max-width: 90%; width: auto;;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Caja</h5>
        <span class="mx-2"></span>
        <label for="cade_guia_modal_show" class="col-1 text-center">Nro. de Guía</label>
        <input type="text" readonly id="cade_guia_modal_show" name="cade_guia" class="form-control mx-4">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">


        <div class="row esconder-fact">

          <h6>Datos del transportista</h6>

          <div class="d-flex w-100 justify-content-between">
            <div class="form-group col">
              <input type="text" class="disabled-input-fact esconder-fact transporte-update form-control" data-campo="cade_transportista_nombre_retirado" data-cadeId="" name="inpNombre" placeholder="Nombre">
            </div>
            <div class="form-group col">
              <input type="text" class="disabled-input-fact transporte-update esconder-fact form-control" data-campo="cade_transportista_cedula_retirado" data-cadeId="" name="inpCedula" placeholder="Cédula/RUC">
            </div>
            <div class="form-group col">
              <input type="text" class="disabled-input-fact transporte-update form-control esconder-fact" data-campo="cade_transportista_matricula_retirado" data-cadeId="" name="inpMatricula" placeholder="Matrícula">
            </div>
          </div>
        </div>

        <h6>Cargos</h6>
        <div class="d-flex flex-col flex-md-row justify-content-between align-items-start">
          <div class="col-6 flex-col">
            <table class="table table-sm table-bordered text-center">
              <thead class="bg-dark">
                <th>Cargo</th>
                <th>Monto</th>
                <th>ITBMS</th>
                <th>Opc.</th>
              </thead>
              <tbody id="tbody-services">
                <tr>
                  <td>SEREVICIO1</td>
                  <td>4.00$</td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm"">
                              <i class=" fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>SEREVICIO1</td>
                  <td>4.00$</td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm"">
                              <i class=" fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>


          <div class="col esconder-fact">
            <form class="d-flex justify-content-between" id="formNuevoServicio">
              <input type="hidden" name="cade_id">
              <input type="hidden" name="cade_guia">
              <input type="hidden" name="carg_id" id="carg_id_caja_modal" value="<?php echo isset($_GET["carg_id"]) ? $_GET['carg_id'] : '' ?>">
              <div class="input-group mb-3 w-100 w-md-50" style="padding-right: 10px">
                <div class="input-group-prepend">
                  <button type="button" class="btn btn-primary" id="btnSaveServicio">
                    <i class="fa-solid fa-plus"></i>
                  </button>
                </div>
                <!-- <input type="text" class="form-control" placeholder="Servicio" aria-label="Username" aria-describedby="basic-addon1"> -->
                <select name="case_id" id="selectCaseId" class="form-control">
                  <option>Seleccionar un servicio</option>
                  <?php while ($fila = mysql_fetch_assoc($servicios)): ?>
                    <option data-monto="<?php echo $fila["case_monto"] ?>" value="<?php echo $fila["case_id"] ?>"><?php echo $fila["case_nombre"] ?></option>
                  <?php endwhile ?>
                </select>
              </div>

              <script>
                $("#selectCaseId").change(function() {
                  const monto = $(this).find(":selected").data("monto")
                  $("#caca_monto").val(monto)
                })
              </script>

              <div class="form-group mb-3 w-100 w-md-50" style="padding-left: 10px">
                <input type="number" class="form-control" name="caca_monto" id="caca_monto" placeholder="Monto">
              </div>
            </form>

            <div class="form-group">
              <label for="inpRetiro">Forma de retiro</label>
              <input type="text" class="form-control transporte-update" data-campo="cade_forma_retiro" data-cadeId="" id="inpRetiro" placeholder="Forma de retiro de la carga">
            </div>
          </div>


        </div>

        <!-- Datos del consignee -->
        <div class="col-12">
          <table class="table table-bordered table-sm text-center">
            <thead class="bg-dark">
              <th>Consignee</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>RUC</th>
              <th>DV</th>
              <th>Tipo de contribuyente</th>
              <th></th>
              <th></th>
            </thead>
            <tbody>
              <tr>
                <input type="hidden" name="cons_id" id="cons_id_actual">
                <td><input class="cons-update form-control" type="text" data-campo="cons_nombre" name="inpConsNombre" placeholder="Email de contacto" readonly></td>
                <td><input class="cons-update form-control" type="email" data-campo="cons_email" name="inpConsEmail" placeholder="Email de contacto"></td>
                <td><input class="cons-update form-control" type="text" data-campo="cons_telefono" name="inpConsTelefono" placeholder="Teléfono de contacto"></td>
                <td><input class="cons-update form-control" type="text" data-campo="cons_ruc" name="inpConsRuc" placeholder="RUC"></td>
                <td><input class="cons-update form-control" type="text" data-campo="cons_dv" name="inpConsDv" placeholder="DV"></td>
                <td>
                  <div class="form-group">
                    <select class="form-control cons-update" id="sltTipoContribuyente" name="sltTipoConstribuyente" data-campo="cons_tipo_constribuyente">
                      <option value="1">1:NATURAL</option>
                      <option value="2">2:JURÍDICO</option>
                    </select>
                  </div>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button onclick="verificarRuc()" type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="VERIFICAR RUC">
                      VERIFICAR RUC
                    </button>
                  </div>
                </td>
                <td>
                  <div class="btn-group btn-group-sm esconder-fact">
                    <button onclick="enviarEmail()" type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Notificar al correo">
                      <i class=" fa-solid fa-envelope"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Seccion de botones -->
        <div>
          <div class="d-flex flex-column justify-content-end">
            <h4 class="mt-4">Formas de Pago</h4>

            <!-- Contenedor donde se agregarán las formas de pago -->
            <div id="pagos-container" class="payment-container">
              <!-- Las formas de pago se agregarán aquí dinámicamente -->
              <div class="d-flex form-group payment-item">

                <input class="form-control mx-3" type="number" id="pago_monto" name="monto" placeholder="Monto" min="0" step="0.01" required />
                <select class="form-control mx-3" name="forma_pago" id="forma_pago">
                  <option value="">Selecciona forma de pago</option>
                  <?php
                  // Reset del puntero del resultado para volver a usar los datos
                  if (isset($fopa_res) && mysql_num_rows($fopa_res) > 0) {
                    mysql_data_seek($fopa_res, 0);
                    while ($row = mysql_fetch_assoc($fopa_res)): ?>
                      <option value="<?php echo $row["fopa_id"] ?>"><?php echo $row["fopa_nombre"] ?></option>
                  <?php endwhile;
                  }                         ?>
                  <?php  ?>
                </select>
                <button class="btn btn-info" onclick="agregarPago()">AGREGAR</button>
              </div>

              <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle shadow rounded">
                  <thead class="table-primary text-center">
                    <tr>
                      <th style="width: 40%">💰 Monto</th>
                      <th style="width: 60%">💳 Forma de pago</th>
                      <th style="width: 60%"></th>
                    </tr>
                  </thead>
                  <tbody id="forma-pago-result">
                    <!-- Contenido dinámico aquí -->
                  </tbody>
                </table>
              </div>

            </div>

            <!-- <button class="btn btn-primary" onclick="agregarPago()">Agregar forma de pago</button> -->
            <br><br>

            <script>
              // Contador para los índices de los elementos
              let pagoIndex = 0;

              const pagos = [];

              // Función para agregar una nueva forma de pago
              function agregarPago() {
                const formData = new FormData()
                formData.append("monto", $("#pago_monto").val())
                formData.append("forma_pago", $("#forma_pago").val())
                formData.append("cade_guia", $("#formNuevoServicio input[name=cade_guia]").val())

                $.ajax({
                  url: "./ajax/caja_facturar.php",
                  method: "POST",
                  processData: false,
                  contentType: false,
                  data: formData,
                  success: res => {
                    // console.log(res)
                    mostrarFormasPagos()
                  },
                  error: (xhr, status, error) => {
                    const msgerr = JSON.parse(xhr.responseText).msg || JSON.parse(xhr.responseText).mensaje
                    // console.error("Error AJAX:", error)
                    // console.log("XHR:", xhr.responseText)

                    Swal.fire({
                      title: msgerr,
                      icon: "question"
                    });
                  }
                })

              }

              function mostrarFormasPagos() {
                const cade_guia = $("#formNuevoServicio input[name='cade_guia']").val();

                $.get("./ajax/caja_facturar.php", {
                  cade_guia
                }, res => {
                  res = JSON.parse(res);

                  const data = res.data || []
                  const monto_pendiente = res.monto_pendiente || 0
                  const cargos_sin_facturar = res.cargos_sin_facturar || 0
                  let html = "";

                  $("#pago_monto").val(monto_pendiente)

                  if (monto_pendiente == 0 && cargos_sin_facturar != 0) { //Entonces vamos a facturar == 0, no hay cargos pendientes
                    //Vamos a deshabilitar el boton de facturar
                    $("#btnFacturar").prop("disabled", false)
                    // $("#btnFacturar").hide()
                  } else {
                    $("#btnFacturar").show()
                    $("#btnFacturar").prop("disabled", true)
                  }

                  data.forEach(e => {
                    html += `
                              <tr>
                                 <td>${e.fapa_monto}</td>
                                 <td>${e.fopa_nombre}</td>
                                 <td>
                                    ${e.fapa_facturado == 0 
                                    ? `<span onclick="eliminarPago(${e.fopa_id})" class="btn btn-danger">Eliminar</span>` 
                                    : ''}
                                 </td>
                                 </td>
                              </tr>`;
                  });

                  // Por si no hay resultados
                  if (data.length === 0) {
                    html = `<tr><td colspan="2" class="text-center">No hay formas de pago registradas</td></tr>`;
                  }

                  $("#forma-pago-result").html(html);
                });
              }


              // Función para eliminar una forma de pago
              function eliminarPago(fopa_id) {

                $.ajax({
                  url: "./ajax/caja_facturar.php",
                  method: "DELETE",
                  contentType: "application/json",
                  data: JSON.stringify({
                    fopa_id
                  }),
                  success: res => {
                    // res = JSON.parse(res)

                    // console.log(res)
                    mostrarFormasPagos()

                  }
                })
              }

              // Función para procesar todos los pagos
              function procesarPagos() {

                // Recopilamos los datos de todas las formas de pago
                document.querySelectorAll('#pagos-container .form-group').forEach(item => {
                  const monto = item.querySelector('input[name="monto"]').value;
                  const formaPago = item.querySelector('select[name="forma_pago"]').value;

                  // Si ya existe el monto y la forma de pago, dale continue

                  if (monto && formaPago) {
                    const montoNum = parseFloat(monto);

                    // Verificamos si ya existe un objeto con el mismo monto y forma_pago
                    const yaExiste = pagos.some(p.forma_pago === formaPago);

                    if (!yaExiste) {
                      pagos.push({
                        monto: montoNum,
                        forma_pago: formaPago
                      });
                    }
                  }
                });

                // Validación
                if (pagos.length === 0 && pagoIndex != 0) {
                  alert("Por favor agrega al menos una forma de pago válida.");
                  return;
                }


                // Aquí puedes hacer lo que necesites con los datos recopilados
                // console.log("Pagos a procesar:", pagos);
              }
            </script>

          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <!-- <a href="" class="btn btn-warning" target="_blank" id="btnVerfactura">VER FACTURA</a> -->
        <button type="button" class="btn btn-primary" id='btnFacturar' onclick="facturar()">FACTURAR</button>
      </div>
    </div>
  </div>
</div>

<script>
  function imprimirRecibo(cade_id) {
    // Get carg_id from URL
    const urlParams = new URLSearchParams(window.location.search);
    const carg_id = urlParams.get('carg_id');

    // Create URL with both IDs
    const url = `factura_recibo_carga.php?carg_id=${carg_id}${cade_id ? '&cade_id=' + cade_id : ''}`;
    window.open(url, '_blank');
  }

  $("#modalCajaDetalles").on("show.bs.modal", function(e) {
    let button = $(e.relatedTarget)
    let title = button.data("title")

    if (title == 'SERVICIOS') {
      // Quitar boton de FACTURAR
      $("#btnFacturar").hide()
    } else {
      $("#btnFacturar").show()
    }

    let modal = $(this)
    modal.find(".modal-title").text(title)
  })

  $("#btnSaveServicio").click(guardarServicios)
  $("#selectCaseId").change(function() {

    // Peticion para buscar la
    $.ajax({

    })
  })

  function mostrarCajaDetalles(id, facturacion, guia, carg_id) {

    if (facturacion == 1) {
      // console.log(facturacion);
      // Deshabilitar los inputs y ocultar elementos
      $(".disabled-input-fact").prop("disabled", true) // Usar "disabled" en lugar de "readonly"
      $(".esconder-fact").hide()
    } else {
      // Habilitar los inputs y mostrar elementos
      $(".disabled-input-fact").prop("disabled", false)
      $(".esconder-fact").show()
    }

    let action
    let html
    let tbody = $("#tbody-services")
    $("input[name='cade_id']").val(id)
    if (guia) {
      $("input[name='cade_guia']").val(guia)
    }

    if (carg_id) {
      $("#carg_id_caja_modal").val(carg_id)
    }

    // Asignar el id de cadeDetalle a los campos de trnasportista
    $(".transporte-update").data("cadeId", id)

    action = facturacion == 1 ? "facturacion" : "detalles";

    tbody.html("")
    // console.log(id)
    $.ajax({
      url: "./ajax/carga-detalles.php",
      method: "GET",
      data: {
        a: 'caja-detalles',
        action,
        id,
        guia: $("#formNuevoServicio input[name='cade_guia']").val()
      },
      success: res => {
        res = JSON.parse(res)

        if (res.length == 0) {
          html += `
               <tr>
                  <td colspan=3>
                     No hay servicios registrados
                     </td>
                     </tr>`
        } else {
          // console.log(res.detail_info.cade_transportista_nombre_retirado);
          $("input[name='inpNombre']").val(res.detail_info.cade_transportista_nombre_retirado)
          $("input[name='inpCedula']").val(res.detail_info.cade_transportista_cedula_retirado)
          $("input[name='inpMatricula']").val(res.detail_info.cade_transportista_matricula_retirado)

          $("input[name='inpConsNombre']").val(res.detail_info.cons_nombre)
          $("input[name='inpConsEmail'").val(res.detail_info.cons_email)
          $("input[name='inpConsTelefono'").val(res.detail_info.cons_telefono)
          $("input[name='inpConsRuc'").val(res.detail_info.cons_ruc)
          $("input[name='inpConsDv'").val(res.detail_info.cons_dv)
          $("#inpRetiro").val(res.detail_info.cade_forma_retiro)
          $("#sltTipoContribuyente").val(res.detail_info.cons_tipo_constribuyente)


          if (res.detail_info.cade_facturada == 1) {
            // $("#btnFacturar").hide()
            // $("#btnFacturar").prop("disabled", true)
          }
          mostrarFormasPagos()

          if (res.detail_info.fact_url != '0') {
            // Vamos a activar el boton para ver la factura y asignar la propiedad
            $("#btnVerfactura").show()
            $("#btnVerfactura").attr("href", res.detail_info.fact_url)
          } else {
            $("#btnVerfactura").hide()
          }

          // Asignar el ID del consignee al input hidden del id del consignee
          $("#cons_id_actual").val(res.detail_info.cons_id)

          // Mostrar la forma de pago

          if (res.detalles[0].length == 0) {

            html += `<tr>
                  <td colspan=3>
                     No hay servicios registrados
                     </td>
                     </tr>`

          } else {

            let total = 0
            let totalITBMS = 0

            res.detalles.forEach(e => {
              total += parseFloat(e.caca_monto)
              totalITBMS += parseFloat(e.caca_itbms)
              html += `
                     <tr ${e.caca_facturado == 1 ? `class='bg-success'` : ''}>
                     <td>${e.case_nombre}</td>
                     <td>${e.caca_monto}</td>
                     <td>${e.caca_itbms}</td>
                     <td class="p-0">
                     ${e.caca_facturado == 0 
                     ? `<div class="btn btn-group btn-group-sm">
                           <button class="btn btn-danger" onclick="eliminarServicio(${e.caca_id}, ${id})"><i class="fas fa-trash"></i></button>
                        </div>` 
                        : ''}
                        </td>
                     </tr>`

              // if (e.caca_facturado == 0) {
              //    $("#btnFacturar").prop("disabled", false) //Si no esta facturado, habilita para presionar
              // }else{
              //    $("#btnFacturar").prop("disabled", true) // Si ya esta factura, deshabilita
              // }
            })

            // $("#pago_monto").val((total + totalITBMS).toFixed(2))

            html += `<tr>
                  <td></td>
                  <td>${total}</td>
                  <td>${totalITBMS}</td>
                  <td  class="text-bold">
                  Total: ${(total + totalITBMS).toFixed(2)}
                  </td>
                  </tr>`
          }
        }
        tbody.html(html)
      }
    })
  }

  function guardarServicios() {
    const data = new FormData($("#formNuevoServicio")[0])
    data.append('cons_id', $("#cons_id_actual").val())

    $.ajax({
      url: "./ajax/cargos_servicios.php",
      method: "POST",
      processData: false,
      contentType: false,
      data,
      success: res => {
        // console.log(res)
        let id = JSON.parse(res).id
        mostrarCajaDetalles(id)
      }
    })
  }

  function actualizarCampos() {

    const data = {
      cade_id: $(this).data("cadeId"),
      campo: $(this).data("campo"),
      valor: $(this).val(),
      a: "actualizarCampos"
    }

    // console.log(data)

    $.post("ajax/cargos_servicios.php",
      data
    )
  }

  function eliminarServicio(id, idDetalle) {

    // Swal.fire({
    //    text: "Se ha eliminado correctamente el servicio",
    //    icon: "error"
    // })

    $.ajax({
      url: "ajax/cargos_servicios.php",
      method: "DELETE",
      processData: false,
      contentType: 'application/json',
      data: JSON.stringify({
        id
      }),
      success: res => {
        Swal.fire({
          text: "Se ha eliminado correctamente el servicio",
          icon: "success"
        })
        mostrarCajaDetalles(idDetalle)
      }
    })
  }

  function actualizarCons() {
    const data = {
      cons_id: $("#cons_id_actual").val(),
      campo: $(this).data("campo"),
      valor: $(this).val(),
      a: "actualizarCampos"
    }

    // console.log(data)

    $.post("ajax/consignee.php",
      data,
      res => {
        // console.log(res);
      }
    )
  }

  // Decodificar la base64 a binario
  function convertBase64ToArrayBuffer(base64) {
    const binaryString = atob(base64.split(',')[1]);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
      bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
  }

  function facturar() {
    $.post(`./ajax/cargos_servicios.php?ruc=${$("input[name='inpConsRuc']").val()}&tipo=2&dv=${$("input[name='inpConsDv']")}`, {
        cade_id: $("input[name='cade_id']").val(),
        a: "facturar"
      })
      .done(res => {
        let facturaInfo
        res = JSON.parse(res)

        facturaInfo = res.resFacturaInfo

        htmlContent = res.html

        // Si hay factura info, vamos a mostrar el boton de factura y la href
        if (facturaInfo) {
          // Vamos a activar el boton para ver la factura y asignar la propiedad
          // $("#btnFacturar").hide()
          $("#btnVerfactura").show()
          $("#btnVerfactura").attr("href", facturaInfo.fact_url)
        } else {
          $("#btnVerfactura").hide()
          $("#btnFacturar").show()
        }

        const container = document.getElementById("facturaContainer")

        container.innerHTML = htmlContent

        if (container) {

          html2canvas(container).then(function(canvas) {
            // Convertir el canvas a imagen JPG (base64)
            const imgData = canvas.toDataURL('image/jpeg');
            const byteArray = convertBase64ToArrayBuffer(imgData);

            const blob = new Blob([byteArray], {
              type: 'application/octet-stream'
            });

            // Enviar la imagen al servidor usando fetch
            fetch('http://localhost:5000/print', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/octet-stream'
                },
                body: blob,
              })
              .then((response) => response.text())
              .then((data) => {
                console.log('Imagen enviada correctamente:', data);
                Swal.fire({
                  icon: 'success',
                  title: 'La imagen se ha enviado correctamente.',
                })
              })
              .catch((error) => {
                console.error(error);
                Swal.fire({
                  icon: 'error',
                  title: "Impresora no conectada",
                })
              })
          })

        } else {
          Swal.fire({
            icon: "error",
            title: "Error en la carga del contenedor de factura"
          });
        }

        container.innerHTML = ""

        //if (res.msg == "done") {
        //   window.open("./ajax/factura.pdf", "_blank");
        //}
      })
      .fail((jqXHR, textStatus, errorThrown) => {
        let error = JSON.parse(jqXHR.responseText).mensaje || JSON.parse(jqXHR.responseText).msg;
        Swal.fire({
          icon: "error",
          title: error
        });
      })
      .always(() => {
        // console.log("Petición completada.")
        document.getElementById("facturaContainer").innerHTML = ""
        mostrarFormasPagos()
      });
  }


  function verificarRuc() {
    $.get(`./ajax/cargos_servicios.php?ruc=${$("input[name='inpConsRuc']").val()}&tipo=${$("#sltTipoContribuyente").val()}&dv=${$("input[name='inpConsDv']").val()}`, {},
        res => {
          res = JSON.parse(res)
          const inpDv = $("input[name='inpConsDv']")
          const inpConsignee = $("input[name='inpConsNombre']")

          inpDv.val(res.datos.dv)
          inpConsignee.val(res.datos.razonSocial)

          $(".cons-update").trigger('change')

          Swal.fire({
            icon: "success",
            title: `Razón Social: ${res.datos.razonSocial}`,
            text: "Consignee actualizado correctamente"
          })
        })
      .fail((jqXHR, textStatus, errorThrown) => {
        // Captura errores de AJAX
        let error = JSON.parse(jqXHR.responseText).err
        Swal.fire({
          icon: "error",
          title: error
        })
        // alert(`Error en la solicitud: ${error}`);
        // console.error("Error en facturar:", );
      })
  }

  function enviarEmail() {
    const data = {
      cons_email: $("input[name='inpConsEmail']").val(),
      cons_nombre: $("input[name='inpConsNombre']").val(),
      cade_id: $("input[name='cade_id']").val(),
      a: "enviarEmail"
    }

    $.post("./ajax/cargos_servicios.php", data)
  }

  $('.transporte-update').on("change", actualizarCampos)
  $(".cons-update").on("change", actualizarCons)
</script>