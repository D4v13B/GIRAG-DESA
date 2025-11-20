<?php
include('funciones_ui.php');
date_default_timezone_set('America/Panama');
$carga_localizciones_bodega = mysql_query("SELECT * FROM carga_localizaciones_bodega");
$shipper_select = mysql_query("SELECT * FROM shipper");
$consignee_select = mysql_query("SELECT * FROM consignee");
$sql = "SELECT * FROM transportes";
$transporte = mysql_query($sql);
$sql = "SELECT cati_id, cati_nombre 
FROM carga_tipos 
WHERE cati_id = 2 OR cati_id = 4;";
$carga_tipos = mysql_query($sql);
$sql = "SELECT * FROM carga_tipos";
$carga_tipo = mysql_query($sql);
$sql = "SELECT * FROM vuelos";
$vuelos = mysql_query($sql);
$sql = "SELECT * FROM lineas_aereas";
$lineas_areas = mysql_query($sql);
$sql = "SELECT * FROM aereopuertos_codigos";
$aero_cod = mysql_query($sql);
$sql = "SELECT
vuelos.vuel_id,
vuelos.liae_id,
lineas_aereas.liae_nombre,
paises_origen.pais_bandera AS pais_origen,
paises_destino.pais_bandera AS pais_destino,
vuelos.vuel_fecha,
vuelos.vuel_codigo
FROM vuelos
INNER JOIN lineas_aereas ON vuelos.liae_id = lineas_aereas.liae_id
INNER JOIN paises AS paises_origen ON vuelos.aeco_id_origen = paises_origen.pais_id
INNER JOIN paises AS paises_destino ON vuelos.aeco_id_destino = paises_destino.pais_id";
$vuelo = mysql_query($sql);
$sql = "SELECT * FROM
         paises p, aereopuertos_codigos a
         WHERE p.`pais_id` = a.`pais_id`";
$paises = mysql_query($sql);
$sql = "SELECT * FROM
         paises p, aereopuertos_codigos a
         WHERE p.`pais_id` = a.`pais_id`";
$paises2 = mysql_query($sql);
$sql = "SELECT * FROM
         paises p, lineas_aereas a
         WHERE p.`pais_id` = a.`pais_id`";
$lineas_aereas = mysql_query($sql);
$sql = "SELECT * FROM carga_estado";
$carga_estado = mysql_query($sql);
$usuaID = $_SESSION['login_user'];
$sql = "SELECT usua_nombre FROM usuarios WHERE usua_id = $usuaID ";
$usuarios = mysql_fetch_assoc(mysql_query($sql));
$sql = "SELECT * FROM codigo_interlineal";
$codigoint = mysql_query($sql);
$sql = "SELECT * FROM carga";
$cargas_id = mysql_query($sql);
$sql = "SELECT * FROM consignee";
$consignee_con = mysql_query($sql);
$usuarioID = $_SESSION['login_user'];
$sql = "SELECT usua_nombre  FROM usuarios WHERE usua_id = $usuarioID ";
$usuario = mysql_query($sql);
$sql = "SELECT * FROM carga a, carga_detalles b WHERE a.carg_id = b.carg_id";
$cargadetalle = mysql_query($sql);
$labelestado = "";
// BUSCAR LOS DATOS DE LA CARGA EXISTENTE
$res["tran_id"] = 0;
if (isset($_GET["carg_id"])) {
   $carg_id = $_GET["carg_id"];
   $sql = "SELECT * FROM carga WHERE carg_id = $carg_id";
   $res = mysql_fetch_assoc(mysql_query($sql));
   // print_r($res);
}
$ship_id = $res["ship_id"];
$ship_nombre = obtener_valor("SELECT ship_nombre FROM shipper WHERE ship_id = $ship_id", "ship_nombre");
$cons_id = $res["cons_id"];
$cons_nombre = obtener_valor("SELECT cons_nombre FROM consignee WHERE cons_id = $cons_id", "cons_nombre");
?>
<?php
// ACTUALIZAR DE BORRADOR A RECIBIDO
$carg_id = $_GET["carg_id"];
// Consulta para obtener el caes_id
$SQL = "SELECT caes_id FROM carga WHERE carg_id = $carg_id";
$ESTADO_RESULT = mysql_query($SQL);
$caes_id = 0;
// // Obtener el valor de caes_id
// if ($row = mysql_fetch_assoc($ESTADO_RESULT)) {
//    $caes_id = $row["caes_id"];
//    // Evaluar el estado de caes_id
//    if ($caes_id == 1) {
//       // Si es 1, cambiarlo a 2
//       $caes_id = 2;
//       // Actualizar el valor en la base de datos
//       $UPDATE_SQL = "UPDATE carga SET caes_id = $caes_id WHERE carg_id = $carg_id";
//       mysql_query($UPDATE_SQL);
//    }
//    // Si es diferente de 1, se deja igual (no hacemos nada)
// }
?>
<section class="content">
   <div class="card border">
      <div class="card-header">
         <div class="row">
            <div class="col-6">
               <h2><i class="fa-solid fa-truck-ramp-box"></i> RECIBO DE CARGA</h2>
               <?php if (isset($_GET["carg_id"])): ?>
                  <a class="mx-3 btn btn-primary" target="_blank" href="factura_recibo_carga_export.php?carg_id=<?php echo $carg_id ?>">IMPRIMIR RECIBO</a>
                  
               <?php endif ?>
               <button type="submit" class="btn btn-info btn_recibir_export" id="btnRecibir"><i class="fa-solid fa-truck-ramp-box"> </i><?php echo isset($_GET["carg_id"]) ? " RECIBIR" : " RECIBIR" ?></button>

            </div>
         </div>
      </div>
      <form class="card-body" id="form-nueva-carga">
         <button type="submit" class="btn btn-success btn_guardar_trans_export"><i class="fa-solid fa-floppy-disk"> </i><?php echo isset($_GET["carg_id"]) ? " GUARDAR" : " GUARDAR" ?></button>
         <!-- INICIO DE ROLES, SI EL ESTADO ES BORRADOR, SE MUESTRA EL BOTON GUARDAR, SI ES DIFERENTE SE OCULTA, Y SOLO SE MOSTRARA A TRAVES DE LOS ROLES. -->
         <?php
         $carg_id = $_GET["carg_id"];
         $SQL = "SELECT caes_id FROM carga WHERE carg_id = $carg_id";
         $ESTADO_RESULT = mysql_query($SQL);
         $caes_id = 0;
         if ($row = mysql_fetch_assoc($ESTADO_RESULT)) {
            $caes_id = $row["caes_id"];
         }
         ?>
         <!-- <script>
            $(function() {
               <?php if ($caes_id == 1): ?>
                  // Si el estado es 1, mostrar el botón
                  $(".btn_guardar_trans_export").show();
               <?php else: ?>
                  // Si el estado es distinto de 1, ocultar el botón y aplicar roles
                  $(".btn_guardar_trans_export").hide()
                  <?php echo pantalla_roles("index.php?p=recibos-carga-transf-export", $_SESSION["login_user"]) ?>
               <?php endif; ?>
            });
         </script> -->
         <!-- FIN DE GUARDAR POR ROLES -->
         <!-- <button type="reset" class="btn btn-success">ANULAR</button> -->
         <!-- Form Recibos de carga -->
         <div class="form-row">
            <input type="hidden"
               value="<?php echo isset($_GET["carg_id"]) ? intval($_GET["carg_id"]) : "" ?>"
               name="carg_id"
               id="carg_id">
            <!-- Estado -->
            <!-- Estado -->

            <div class="form-group col-3">

               <label for="inputVuelo">Estado</label>

               <select id="estadoCarga" class="form-control custom-select" name="caes_id">
                  <option disabled>Seleccione una opción</option>
                  <?php while ($fila = mysql_fetch_assoc($carga_estado)) : ?>
                     <option
                        <?php if (isset($res['caes_id']) && $fila["caes_id"] == $res["caes_id"]) echo 'selected'; ?>
                        value="<?php echo $fila["caes_id"] ?>">
                        <?php echo $fila["caes_nombre"] ?>
                     </option>

                  <?php endwhile ?>
               </select>


            </div>
            <!-- Número de guía -->
            <div class="form-group col-3">
               <label for="inputGuia">Número de Guía</label>
               <input type="text" id="inputGuia" class="form-control" name="guia" value="<?php echo isset($res["carg_guia"]) ? $res["carg_guia"] : '' ?>">
            </div>
            <!-- Numero de recibo -->
            <div class="form-group col-3">
               <label for="inputName">Número <span id="nombre-carga"></span></label>
               <!-- <input type="text" id="inputName" class="form-control" name="no_recibo" value="<?php echo isset($res["carg_no_recibo"]) ? $res["carg_no_recibo"] : '' ?>" readonly> -->
               <input type="text" id="inputName" class="form-control" name="no_recibo" value="<?php echo isset($res["carg_id"]) ? "WHR-" . $res["carg_id"] : '' ?>" readonly>
            </div>
            <!-- Agencia -->
             <div class="form-group col-3">
               <label for="inputAgencia">Agencia</label>
               <select id="inputAgencia" class="form-control custom-select" name="agencia">
                  <option value="" disabled <?php echo (!isset($res['liae_id']) || empty($res['liae_id'])) ? 'selected' : ''; ?>>
                     Seleccione una opción
                  </option>
                  <?php
                  mysql_data_seek($lineas_aereas, 0); // Asegura que el puntero esté al inicio
                  while ($fila = mysql_fetch_assoc($lineas_aereas)) : ?>
                     <option
                        value="<?php echo $fila["liae_id"]; ?>"
                        <?php echo (isset($res['liae_id']) && $fila["liae_id"] == $res["liae_id"]) ? 'selected' : ''; ?>>
                        <?php echo $fila["pais_nombre"] . "/" . $fila["liae_nombre"]; ?>
                     </option>
                  <?php endwhile; ?>
               </select>
            </div>
            <!-- Escoja el vuelo-->
             <div class="form-group col-3">
               <label for="inputVuelo">Vuelo</label>
               <select id="inputVuelo" class="form-control custom-select" name="vuelo">
                  <option value="" disabled <?php echo (!isset($res['vuel_id']) || empty($res['vuel_id'])) ? 'selected' : ''; ?>>Seleccione una opción</option>
                  <?php
                  mysql_data_seek($vuelo, 0);
                  while ($fila = mysql_fetch_assoc($vuelo)) : ?>
                     <option value="<?php echo $fila["vuel_id"]; ?>" <?php echo (isset($res['vuel_id']) && $fila["vuel_id"] == $res["vuel_id"]) ? 'selected' : ''; ?>>
                        <?php echo $fila["liae_nombre"] . "/" . $fila["pais_origen"] . "-" . $fila["pais_destino"] . "/" . $fila["vuel_codigo"]; ?>
                     </option>
                  <?php endwhile; ?>
               </select>
            </div>
            <!-- Destino Final-->
           <div class="form-group col-3">
               <label for="inputDestinoF">Destino Final</label>
               <select id="inputDestinoF" class="form-control custom-select" name="destino_final">
                  <option value="" disabled <?php echo (!isset($res['aeco_id_destino_final']) || empty($res['aeco_id_destino_final'])) ? 'selected' : ''; ?>>Seleccione una opción</option>
                  <?php
                  mysql_data_seek($paises2, 0);
                  while ($fila = mysql_fetch_assoc($paises2)) : ?>
                     <option value="<?php echo $fila["aeco_id"]; ?>" <?php echo (isset($res['aeco_id_destino_final']) && $fila["aeco_id"] == $res["aeco_id_destino_final"]) ? 'selected' : ''; ?>>
                        <?php echo $fila["pais_nombre"] . "/" . $fila["aeco_codigo"]; ?>
                     </option>
                  <?php endwhile; ?>
               </select>

            </div>
            <!-- Shipper -->
            <div class="form-group col-3">
               <label for="ship_id">Shipper</label>
               <div class="select-container">
                  <?php selectShipper($ship_id, $ship_nombre) ?>
               </div>
            </div>
            <!-- Consignee -->
            <div class="form-group col-3">
               <label for="cons_id">Consignee</label>
               <div class="select-container">
                  <?php selectConsignee($cons_id, $cons_nombre) ?>
               </div>
            </div>
            <style>
               .select-container {
                  position: relative;
               }

               .select-search {
                  position: absolute;
                  top: 100%;
                  left: 0;
                  right: 0;
                  z-index: 1000;
                  display: none;
                  padding: 10px;
                  background: white;
                  border: 1px solid #ced4da;
                  border-radius: 4px;
                  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
               }

               .select-search input {
                  width: 100%;
                  padding: 8px;
                  border: 1px solid #ced4da;
                  border-radius: 4px;
               }

               .select-options {
                  max-height: 200px;
                  overflow-y: auto;
                  margin-top: 10px;
               }

               .select-option {
                  padding: 8px;
                  cursor: pointer;
               }

               .select-option:hover {
                  background-color: #f8f9fa;
               }
            </style>
            <!-- Recepción Real de Carga-->
            <div class="form-group col-3">
               <label for="InputRecepcion">Recepción Real de Carga</label>
               <input type="datetime-local" id="InputRecepcion" class="form-control" name="recepcion_real" value="<?php echo isset($res["carg_recepcion_real"]) ? $res["carg_recepcion_real"] : ''; ?>">
            </div>
            <!-- Creado Por -->
            <div class="form-group col-3">
               <?php while ($fila = mysql_fetch_assoc($usuario)) : ?>
                  <label for="creador">Creado por </label>
                  <input type="" id="creador" class="form-control" value="<?php echo $fila["usua_nombre"]; ?>" readonly>
               <?php endwhile ?>
            </div>
            <!-- Creado El -->
            <div class="form-group col-3">
               <label for="creado">Creado El </label>
               <input type="datetime-local" id="creado" readonly class="form-control" name="fecha_creacion" value="<?php echo isset($res["carg_fecha_registro"]) ? $res["carg_fecha_registro"] :
                                                                                                                        date('Y-m-d\TH:i'); ?>">
            </div>
            <!-- Dirección o tipo de carga-->
            <div class="form-group col-3">
               <label for="creado">Dirección</label>
               <fieldset class="form-group row">
                  <legend class="col-form-label col-sm-2 float-sm-left pt-0"></legend>
                  <div class="col-sm-10">
                     <?php while ($fila = mysql_fetch_assoc($carga_tipos)) : ?>
                        <?php if ($fila['cati_id'] != 4) continue; // Mostrar solo el ID 2 
                        ?>
                        <div class="form-check">
                           <input
                              class="form-check-input"
                              type="radio"
                              name="direccion"
                              id="<?php echo $fila["cati_nombre"] ?>"
                              value="<?php echo $fila["cati_id"] ?>"
                              <?php if (
                                 (isset($_GET['direccion']) && $_GET['direccion'] == $fila['cati_id']) ||
                                 (isset($res['cati_id']) && $res['cati_id'] == $fila['cati_id'])
                              ) {
                                 echo 'checked=checked';
                              } ?>>
                           <label class="form-check-label" for="<?php echo $fila["cati_nombre"] ?>">
                              <?php echo $fila["cati_nombre"] ?>
                           </label>
                        </div>
                     <?php endwhile ?>
                     <div id="direccion-error"></div>
                  </div>
               </fieldset>
            </div>
            <!-- Transportista -->
            <div id="mostrar">
               <div class="form-row">
                  <div class="form-group col-3">
                     <label for="InputTransport">Transportista</label>
                     <input type="text" id="InputTransport" class="form-control disabled" name="trans_nombre" value="<?php echo isset($res["carg_transportista"]) ? $res["carg_transportista"] : '' ?>">
                  </div>
                  <!-- Cédula -->
                  <div class="form-group col-3">
                     <label for="InputCedula">Cédula </label>
                     <input type="text" id="InputCedula" class="form-control disabled" name="trans_cedula" value="<?php echo isset($res["carg_transportista_cedula"]) ? $res["carg_transportista_cedula"] : '' ?>">
                  </div>
                  <!-- Matricula-->
                  <div class="form-group col-3">
                     <label for="InputMatricula">Matrícula </label>
                     <input type="text" id="InputMatricula" class="form-control disabled" name="trans_matricula" value="<?php echo isset($res["carg_transportista_matricula"]) ? $res["carg_transportista_matricula"] : '' ?>">
                  </div>
                  <!-- Tipo de transporte-->
                  <div class="form-group col-3">
                     <label for="transporte_id">Transporte</label>
                     <select id="transporte_id" class="form-control custom-select" name="transporte_id">
                        <option selected disabled>Seleccione una opción</option>
                        <?php while ($fila = mysql_fetch_assoc($transporte)) : ?>
                           <option <?php if ($fila["tran_id"] == $res["tran_id"]) {
                                       echo "selected";
                                    } ?> value="<?php echo $fila["tran_id"] ?>"><?php echo $fila["tran_nombre"] ?></option>
                        <?php endwhile ?>
                     </select>
                  </div>
               </div>
            </div>
         </div>
         <!------ ------>
         <div class="form-group col-3">
            <button type="button" class="btn btn-primary btn-sm w-100 mt-3" id="addConsigneeBtn"><i class="fa-solid fa-floppy-disk"> </i> Crear Consignee</button>
         </div>
         <div class="form-group col-3">
            <button type="button" class="btn btn-primary btn-sm w-100 mt-3" id="addShipperBtn"><i class="fa-solid fa-floppy-disk"> </i> Crear Shipper</button>
         </div>
         <table class="table table-bordered table-sm col-12">
            <thead class="bg-dark">
               <tr>
                  <th>Peso(KG)</th>
                  <th>Código interlineal</th>
                  <th> Nota de carga</th>
                  <th> Descripción </th>
                  <th></th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td class="form-group">
                     <input class="form-control" type="text" name="carg_peso" value="<?php echo isset($res["carg_peso"]) ? $res["carg_peso"] : '' ?>">
                  </td>
                  <td class="form-group">
                     <!-- <input type="text" name="coin_id" id=""> -->
                     <select id="coin_id" class="form-control custom-select" name="coin_id" data-ignore-custom>
                        <option selected>Seleccione una opción</option>
                        <?php while ($fila = mysql_fetch_assoc($codigoint)) : ?>
                           <option <?php if (isset($res["coin_id"]) and $res["coin_id"] == $fila["coin_id"]) {
                                       echo "selected";
                                    } ?> value="<?php echo $fila["coin_id"] ?>" <?php echo $fila["coin_codigo"] ?>><?php echo $fila["coin_codigo"] ?></option>
                        <?php endwhile ?>
                     </select>
                  </td>
                  <!--                  <td><input type="text" name="carg_nota" value="--><?php //echo isset($res["carg_nota"]) ? $res["carg_nota"] : '' 
                                                                                          ?><!--"></td>-->
                  <td>
                     <div class="form-group">
                        <textarea class="form-control" name="carg_nota" id="carg_nota"><?php echo isset($res["carg_nota"]) ? $res["carg_nota"] : '' ?></textarea>
                     </div>
                  </td>
                  <td>
                     <div class="form-group">
                        <textarea class="form-control" name="carg_desc" id="carg_desc"><?php echo isset($res["carg_desc"]) ? $res["carg_desc"] : '' ?></textarea>
                     </div>
                  </td>
               </tr>
            </tbody>
         </table>
      </form>
      <?php if (isset($_GET["carg_id"])): ?>
         <div class="table-responsive px-3">
            <!-- Boton del modal Formulario carga detalle -->
            <table class="table table-bordered w-100 table-sm text-center">
               <thead class="bg-dark">
                  <tr>
                     <th>Item</th>
                     <th>Piezas</th>
                     <th>Peso</th>
                     <th>Largo</th>
                     <th>Ancho</th>
                     <th>Alto</th>
                     <th>Volumen</th>
                     <th> </th>
                  </tr>
               </thead>
               <tbody id="tbody-carga-detalles">
                  <tr>
                     <form id="formulario-nueva-carga-detalle">
                        <td></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_piezas" data-campo="cade_piezas" style="width: 100px" type="text" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_peso" data-campo="cade_peso" style="width: 100px" type="number" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_largo" data-campo="cade_largo" style="width: 100px" type="number" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_ancho" data-campo="cade_ancho" style="width: 100px" type="number" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_alto" data-campo="cade_alto" style="width: 100px" type="number" /></td>
                        <td>0</td>
                     </form>
                     <td><button class="btn btn-success btn-sm" onclick="registrarCargadetalle()"><i class="fa-solid fa-plus"></i></button></td>
                  </tr>
               </tbody>
            </table>
         </div>
      <?php endif ?>
   </div>
</section>
<!-- Modal Structure -->
<div id="addModal" class="modal fade" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Crear nuevo Shipper/Consignee</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="addForm">
               <!-- Common Name Input -->
               <!-- Shipper Fields -->
               <div class="form-group c_shipper" style="display:none;">
                  <label for=" snameInput">Nombre</label>
                  <input type="text" class="form-control" id="snameInput" name="i_ship_nombre" required>
               </div>
               <div class="form-group c_shipper" style="display:none;">
                  <label for="sciudadinput">Ciudad</label>
                  <input type="text" class="form-control" id="sciudadinput" name="i_ship_ciudad" required>
               </div>
               <div class="form-group c_shipper" style="display:none;">
                  <label for="saddressInput">Dirección</label>
                  <input type="text" class="form-control" id="saddressInput" name="i_ship_direccion" required>
               </div>
               <!-- Consignee Fields -->
               <div class="form-group c_consignee" style="display:none;">
                  <label for=" nameInput">Nombre</label>
                  <input type="text" class="form-control" id="nameInput" name="i_cons_nombre" required>
               </div>
               <div class="form-group c_consignee" style="display:none;">
                  <label for="ciudadinput">Ciudad</label>
                  <input type="text" class="form-control" id="ciudadinput" name="i_cons_ciudad" required>
               </div>
               <?php echo autocompletar_filtro('paisinput', 'obtener_paises.php', 'codigo_pais', '3', 'c_pais') ?>
               <input type=hidden id=c_pais name="i_pais_id">
               <div class="form-group">
                  <label for="paisinput">País</label>
                  <input type="text" class="form-control" id="paisinput" required>
               </div>
               <div class="form-group c_consignee" style="display:none;">
                  <label for="addressInput">Dirección</label>
                  <input type="text" class="form-control" id="addressInput" name="i_cons_direccion" required>
               </div>
               <input type="hidden" id="entityType" name="type">
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
         </div>
      </div>
   </div>
</div>
<script>
   $(".ocultar").hide()
   $(document).ready(function() {
      $('input[type="radio"][name="direccion"]').change(function() {
         // Si se selecciona la opción Export
         if ($(this).val() !== '1' && $(this).val() !== '2') {
            // Ocultar los campos adicionales
         } else {
            // Mostrar los campos adicionales para otras opciones
            $('.ocultar').show();
         }
      })
      obtenerDetalles()
      <?php if (isset($_GET["carg_id"])): ?>
         $("#shipper").val(<?php echo $res["ship_id"] ?>)
         $("#consignee").val(<?php echo $res["cons_id"] ?>)
      <?php endif ?>
   });

   function eliminarCargaDetalle(id) {
      $.ajax({
         method: "DELETE",
         contentType: "application/json",
         url: "ajax/carga-detalles.php",
         data: JSON.stringify({
            cade_id: id
         }),
         success: res => {
            res = JSON.parse(res)
            if (res.success) {
               obtenerDetalles()
               Swal.fire({
                  icon: "success",
                  title: "Eliminado correctamente"
               })
            }
         }
      })
   }

   function registrarCarga() {
      const form = $("#form-nueva-carga")[0];
      const datos = new FormData(form);
      // Debug - check if carg_id is present
      console.log("carg_id:", datos.get("carg_id"));
      $.ajax({
         url: "ajax/carga.php",
         method: "POST",
         contentType: false,
         processData: false,
         data: datos,
         success: res => {
            // Add error handling
            try {
               const response = JSON.parse(res);
               if (response.error) {
                  alert("Error: " + response.error);
                  return;
               }
               alert("Mensaje enviado exitosamente");
               const carg_id = response.carg_id;
               $("#carg_id").val(carg_id);
               if (carg_id) {
                  window.location.href = "index.php?p=recibos-carga-export&carg_id=" + carg_id;
               }
            } catch (e) {
               console.error("Error parsing response:", e);
               alert("Error en la respuesta del servidor");
            }
         },
         error: (xhr, status, error) => {
            console.error("Ajax error:", error);
            alert("Error en la comunicación con el servidor");
         }
      });
   }

   function registrarCargadetalle() {
      let datos = new FormData($("#formulario-nueva-carga-detalle")[0]);
      datos.append("carg_id", $("#carg_id").val());
      // Log para verificar los datos
      for (let [key, value] of datos.entries()) {
         console.log(`${key}: ${value}`);
      }
      $.ajax({
         url: "ajax/carga-detalles.php",
         method: "POST",
         contentType: false,
         processData: false,
         data: datos,
         success: res => {
            $("#formulario-nueva-carga-detalle").trigger("reset");
            obtenerDetalles();
         },
         error: function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
         }
      });
   }
   //Funcion que me actualiza los campos de manera independiente
   function cambioTiempoReal() {
      //Aqui vamos a calcular el volumen en tiempo real
      // cade_largo * cade_ancho * cade_alto) / 6000 * cade_piezas
      const parentRow = $(this).parent().parent()
      let cade_largo = parentRow.find("input.cade_largo").val()
      let cade_ancho = parentRow.find("input.cade_ancho").val()
      let cade_alto = parentRow.find("input.cade_alto").val()
      let cade_piezas = parentRow.find("input.cade_piezas").val()
      parentRow.find(".volumen").text(((cade_largo * cade_ancho * cade_alto) / 6000 * cade_piezas).toFixed(2))
      const data = {
         cade_id: $(this).data("cadeid"),
         campo: $(this).data("campo"),
         valor: $(this).val(),
         padre_id: $(this).data("padreid"),
         individual: true
      }
      $.post("ajax/carga-detalles.php",
         data,
         res => {
            console.log(res);
         }
      )
   }
   $(document).ready(function() {
      // Open modal with "Add Shipper" button
      $('#addShipperBtn').click(function() {
         $('#modalTitle').text('Añadir Nuevo Shipper');
         $('#entityType').val('shipper');
         $('.c_consignee').hide();
         $('.c_shipper').show(); // Hide Shipper fields
         $('#addModal').modal('show');
      });
      // Open modal with "Add Consignee" button
      $('#addConsigneeBtn').click(function() {
         $('#modalTitle').text('Añadir Nuevo Consignee');
         $('#entityType').val('consignee');
         $('.c_consignee').show();
         $('.c_shipper').hide(); // Hide Shipper fields
         $('#addModal').modal('show');
      });
      // Handle save button click
      $('#saveBtn').click(function() {
         let formData = $('#addForm').serialize();
         // AJAX request to save the new entity
         $.ajax({
            url: $('#entityType').val() === 'shipper' ? 'ship_shipper_crear.php' : 'cons_consignee_crear.php',
            method: 'POST',
            data: formData,
            success: function(response) {
               alert('Registro Creado');
               $('#addModal').modal('hide');
               limpiarFormulario();
               // location.reload(); // Refresh the page to show the new entry
            },
            error: function() {
               alert('Error in saving.');
            }
         });
      });
   });

   function limpiarFormulario() {
      $('#addForm')[0].reset();
      // También puedes ocultar todos los campos si es necesario
      $('.c_consignee, .c_shipper').hide();
   }

   // Después de agregar un nuevo detalle de carga exitosamente, llamar a esta función
   function obtenerDetalles() {
      const carg_id = $("#carg_id").val().trim();
      // $(".remove-update").remove()
      // Verificar si el ID de carga no está vacío
      if (carg_id !== "") {
         $.ajax({
            url: "ajax/carga.php",
            method: "GET",
            data: {
               carg_id: carg_id
            },
            success: function(response) {
               const detalles = JSON.parse(response);
               const tbody = $("#tbody-carga-detalles");
               // tbody.empty();
               let i = 1;
               $(".remove-update").remove()
               if (!detalles) {
                  const row = `<tr colspan="10"><b>NO HAY DETALLES</b></tr>`;
                  tbody.append(row);
               } else {
                  // Construir las filas de la tabla con los detalles de carga
                  detalles.forEach(detalle => {
                     let tempContainer
                     const row = `
<td>${i++}</td>
                     <td><input class="change-update cade_piezas" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_piezas" style="width: 100px" type="text" value="${detalle.cade_piezas}" /></td>
                     <td><input class="change-update cade_peso" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_peso" style="width: 100px" type="number" value="${detalle.cade_peso}" /></td>
                     <td><input class="change-update cade_largo" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_largo" style="width: 100px" type="number" value="${detalle.cade_largo}" /></td>
                     <td><input class="change-update cade_ancho" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_ancho" style="width: 100px" type="number" value="${detalle.cade_ancho}" /></td>
                     <td><input class="change-update cade_alto" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_alto" style="width: 100px" type="number" value="${detalle.cade_alto}" /></td>
                     <td class="volumen">${((detalle.cade_largo * detalle.cade_ancho * detalle.cade_alto) / 6000 * (detalle.cade_piezas)).toFixed(2)}</td>
                     <td>
                          <button type="button" class="btn btn-danger btn-borrar btn-sm" onclick="eliminarCargaDetalle(${detalle.cade_id})"><i class="fas fa-trash"></i></button>
                      </td>
                  `;
                     tempContainer = document.createElement("tr")
                     tempContainer.classList.add("remove-update")
                     tempContainer.innerHTML = row.trim()
                     tempContainer.querySelector(".select-locacilzacion")
                     tbody.append(tempContainer);
                  });
                  $('.change-update').on("change", cambioTiempoReal)
               }
            },
            error: function(xhr, status, error) {
               console.error("Error al obtener detalles de carga:", error);
            }
         });
      }
   }
   // function editarCarga(cade_id) {
   //    if (cade_id != 0) {
   //       $("#cade_id").val(cade_id)
   //       $.ajax({
   //          url: "ajax/carga-detalles.php",
   //          type: "GET",
   //          data: {
   //             cade_id: cade_id
   //          },
   //          success: res => {
   //             res = JSON.parse(res)
   //             // console.log(res);
   //             $("#cade_desc").val(res.cade_desc)
   //             $("#cade_peso").val(res.cade_peso)
   //             $("#cade_largo").val(res.cade_largo)
   //             $("#cade_piezas").val(res.cade_piezas)
   //             $("#cade_recibidas").val(res.cade_recibidas)
   //             $("#cade_salida").val(res.cade_salida)
   //             $("#coin_id").val(res.coin_id)
   //             $("#cade_ancho").val(res.cade_ancho)
   //             $("#cade_alto").val(res.cade_alto)
   //             $("#cade_localizacion").val(res.cade_localizacion)
   //             $("#cade_notas").val(res.cade_notas)
   //          }
   //       })
   //    } else {
   //       $("#cade_id").val("")
   //       $("#formulariocargadetalle").trigger("reset")
   //    }
   // }
   $("document").ready(function() {
      $("#form-nueva-carga").validate({
         rules: {
            // caes_id: "required",
            // no_recibo: "required",
            // guia: "required",
            // //shipper:"required",
            // //consignee:"required",
            // agencia: "required",
            // vuelo: "required",
            // destino_final: "required",
            // //recepcion_real: "required",
            // fecha_creacion: "required",
            // direccion: "required",
            // trans_nombre: "required",
            // trans_cedula: "required",
            // trans_matricula: "required",
            // transporte_id: "required"
         },
         messages: {
            // caes_id: "<span style='color: red; font-size: smaller;'>Ingrese el estado</span>",
            // no_recibo: "<span style='color: red; font-size: smaller;'>Ingrese el número de recibo</span>",
            // guia: "<span style='color: red; font-size: smaller;'>Ingrese el número de guía</span>",
            // //shipper:"<span style='color: red; font-size: smaller;'>Ingrese el shipper</span>",
            // //consignee:"<span style='color: red; font-size: smaller;'>Ingrese el consignee</span>",
            // agencia: "<span style='color: red; font-size: smaller;'>Ingrese la agencia</span>",
            // vuelo: "<span style='color: red; font-size: smaller;'>Ingrese el vuelo</span>",
            // destino_final: "<span style='color: red; font-size: smaller;'>Ingrese el destino final</span>",
            // //recepcion_real: "<span style='color: red; font-size: smaller;'>Ingrese la recepción real</span>",
            // fecha_creacion: "<span style='color: red; font-size: smaller;'>Ingrese la fecha de creación</span>",
            // direccion: "<span style='color: red; font-size: smaller;'>Ingrese la dirección</span>",
            // trans_nombre: "<span style='color: red; font-size: smaller;'>Ingrese el nombre</span>",
            // trans_cedula: "<span style='color: red; font-size: smaller;'>Ingrese la cédula</span>",
            // trans_matricula: "<span style='color: red; font-size: smaller;'>Ingrese la matricula</span>",
            // transporte_id: "<span style='color: red; font-size: smaller;'>Seleccione el transporte</span>"
         },
         errorPlacement: function(error, element) {
            if (element.attr("name") == "direccion") {
               error.appendTo("#direccion-error");
            } else {
               error.insertAfter(element);
            }
         },
         submitHandler: function(form) {
            // Envía el formulario después de mostrar el mensaje
            registrarCarga();
         }
      });
   });
   // Obtener referencia al radio button
   var radioButtons = document.querySelectorAll('input[name="direccion"]');
   // Agregar evento change a cada radio button
   radioButtons.forEach(function(radioButton) {
      radioButton.addEventListener('change', function() {
         // Obtener el valor seleccionado
         var valor = this.value;
         // Determinar el nombre correspondiente según el valor seleccionado
         var nombre = "";
         if (valor == 1) {
            nombre = "de Entrega";
         } else if (valor == 2) {
            nombre = "de Recibo";
         } else if (valor == 3) {
            nombre = "de Retiro";
         } else {
            nombre = "de Entrega";
         }
         // 
         document.getElementById('nombre-carga').textContent = nombre;
         //document.getElementById('inputName').setAttribute('placeholder', 'Número ' + nombre);
      });
   });
   $(document).ready(function() {
      $('.custom-select').each(function() {
         var select = $(this);
         if (select.is('[data-ignore-custom]')) {
            return;
         }
         var container = select.parent();
         // Crear el contenedor de búsqueda
         var searchContainer = $('<div class="select-search">' +
            '<input type="text" placeholder="Buscar..." class="form-control">' +
            '<div class="select-options"></div>' +
            '</div>');
         container.append(searchContainer);
         // Manejar clic en el select
         select.on('mousedown', function(e) {
            e.preventDefault(); // Previene el dropdown nativo
            searchContainer.show();
            searchContainer.find('input').focus();
            // Cargar todas las opciones
            var options = '';
            select.find('option').each(function() {
               if ($(this).val() && !$(this).prop('disabled')) {
                  options += '<div class="select-option" data-value="' + $(this).val() + '">' +
                     $(this).text() + '</div>';
               }
            });
            searchContainer.find('.select-options').html(options);
         });
         // Manejar búsqueda
         searchContainer.find('input').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            searchContainer.find('.select-option').each(function() {
               var text = $(this).text().toLowerCase();
               $(this).toggle(text.indexOf(searchText) > -1);
            });
         });
         // Manejar selección de opción
         searchContainer.on('click', '.select-option', function() {
            var value = $(this).data('value');
            select.val(value).trigger('change');
            searchContainer.hide();
         });
         // Cerrar al hacer clic fuera
         $(document).on('click', function(e) {
            if (!container.is(e.target) && container.has(e.target).length === 0) {
               searchContainer.hide();
            }
         });
      });
   });

   
$(document).ready(function() {
    
    // 1. VERIFICAR ESTADO AL CARGAR LA PÁGINA
    verificarEstadoCarga();
    
    // 2. FUNCIÓN PARA VERIFICAR SI LA CARGA YA ESTÁ RECIBIDA
    function verificarEstadoCarga() {
        $.ajax({
            url: 'ajax/verificar_carga_estado.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'verificar_estado',
                carg_id: <?php echo isset($_GET["carg_id"]) ? intval($_GET["carg_id"]) : 'null'; ?>
            },
            success: function(respuesta) {
                if (respuesta.success && respuesta.estado == 2) {
                    // La carga ya está recibida, deshabilitar todo
                    deshabilitarFormulario();
                }
            }
        });
    }
    
    // 3. FUNCIÓN PARA DESHABILITAR EL FORMULARIO
    function deshabilitarFormulario() {
        // Deshabilitar todos los elementos
        $('input, select, textarea, button').prop('disabled', true);
        
        // Cambiar estilo visual
        $('input, select, textarea').css({
            'background-color': '#e9f4f7ff',
            'color': '#3f3e3eff'
        });
        
        // Mostrar mensaje
      //   if ($('#carga-recibida').length === 0) {
      //       $('body').prepend('<div id="carga-recibida" style="background: #d4edda; color: #155724; padding: 10px; text-align: center; font-weight: bold;">✓ Carga Recibida - Formulario Bloqueado</div>');
      //   }
        
        // Actualizar select si existe
        $('#estadoCarga').val(2);
    }
    
    // 4. BOTÓN RECIBIR (tu código original)
    $('#btnRecibir').on('click', function() {
        $.ajax({
            url: 'ajax/recibir_carga.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'recibir_carga',
                carg_id: <?php echo isset($_GET["carg_id"]) ? intval($_GET["carg_id"]) : 'null'; ?>
            },
            success: function(respuesta) {
                if (respuesta.success) {
                    alert(respuesta.message);
                    // Usar la función para deshabilitar
                    deshabilitarFormulario();
                } else {
                    alert('Error: ' + respuesta.message);
                }
            },
            error: function() {
                alert('Ocurrió un error al recibir la carga');
            }
        });
    });
});
// Después de hacer clic en RECIBIR, cambiar el select
$('#btnRecibir').click(function() {
    setTimeout(function() {
        $('#estadoCarga').val(2); // Cambia 2 por el ID del estado RECIBIDO
    }, 500);
});
</script>