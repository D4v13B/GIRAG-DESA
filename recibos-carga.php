<?php
include('funciones_ui.php');

date_default_timezone_set('America/Panama');

$carga_localizciones_bodega = mysql_query("SELECT * FROM carga_localizaciones_bodega");
$shipper_select = mysql_query("SELECT * FROM shipper");
$consignee_select = mysql_query("SELECT * FROM consignee");
$cargadetalle_tipo = mysql_query("SELECT * FROM carga_detalle_tipo");

$sql = "SELECT * FROM transportes";
$transporte = mysql_query($sql);

$sql = "SELECT cati_id, cati_nombre 
FROM carga_tipos 
WHERE cati_id = 1 OR cati_id = 3;";
$carga_tipos = mysql_query($sql);

$sql = "SELECT * FROM carga_tipos";
$carga_tipo = mysql_query($sql);

$sql = "SELECT * FROM vuelos";
$vuelos = mysql_query($sql);

$sql = "SELECT * FROM lineas_aereas";
$lineas_areas = mysql_query($sql);

$sql = "SELECT * FROM aereopuertos_codigos";
$aero_cod = mysql_query($sql);

//Servicios
$sql = "SELECT * FROM carga_servicios ORDER BY case_nombre";
$servicios = mysql_query($sql);

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
$sql = "SELECT usua_nombre, usti_id FROM usuarios WHERE usua_id = $usuaID ";
$usuario_data = mysql_fetch_assoc(mysql_query($sql));
$usti_id_actual = $usuario_data['usti_id'] ?? -1;
$usuarios = $usuario_data;

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

// BUSCAR LOS DATOS DE LA CARGA EXISTENTE

$res["tran_id"] = 0;
if (isset($_GET["carg_id"])) {
   $carg_id = $_GET["carg_id"];
   $sql = "SELECT * FROM carga WHERE carg_id = $carg_id";
   $res = mysql_fetch_assoc(mysql_query($sql));
   // print_r($res);
}


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
//    // Si es diferente de 1, se deja igual (no hacemos nada)
// }




// Obtener el catálogo de estados
$estados = [];
$q = mysql_query("SELECT caes_id, caes_nombre FROM carga_estado");
while ($r = mysql_fetch_assoc($q)) {
   $estados[] = $r;
}
?>
<script>
   const catalogoEstados = <?php echo json_encode($estados); ?>;
</script>





<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div id="facturaContainer">
</div>

<section class="content">
   <div class="card border">
      <div class="card-header">
         <div class="row">
            <div class="col-6">
               <h2><i class="fa-solid fa-truck-ramp-box"></i> RECIBO DE CARGA</h2>
               <?php if (isset($_GET["carg_id"])): ?>
                  <!--<a class="mx-3 btn btn-primary" target="_blank" href="factura_recibo_carga.php?carg_id=<?php echo $carg_id ?>">IMPRIMIR RECIBO</a>-->
               <?php endif ?>
               <button type="button" class="btn btn-secondary" onclick="window.history.back();">
                  <i class="fa fa-arrow-left"></i> Volver
               </button>
               <button type="submit" class="btn btn-info btn_recibir_export" id="btnRecibir"><i class="fa-solid fa-truck-ramp-box"> </i><?php echo isset($_GET["carg_id"]) ? " RECIBIR" : " RECIBIR" ?></button>

            </div>
         </div>
      </div>
      <form class="card-body" id="form-nueva-carga">
         <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"> </i><?php echo isset($_GET["carg_id"]) ? " GUARDAR" : " GUARDAR" ?></button>
         <!-- <button type="reset" class="btn btn-success">ANULAR</button> -->
         <!-- Form Recibos de carga -->
         <div class="form-row">
            <input type="hidden" value="<?php echo isset($_GET["carg_id"]) ? $_GET["carg_id"] : "" ?>" name="carg_id" id="carg_id">
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
            <!-- <div class="form-group col-3">
               <label for="inputGuia">Número de Guía</label>
               <input type="text" id="inputGuia" class="form-control" name="guia" value="<?php echo isset($res["carg_guia"]) ? $res["carg_guia"] : '' ?>">
            </div> -->
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
               <!-- <label for="creado">Dirección</label> -->
               <label for="creado"></label>
               <fieldset class="form-group row">
                  <legend class="col-form-label col-sm-2 float-sm-left pt-0"></legend>
                  <div class="col-sm-10">
                     <?php while ($fila = mysql_fetch_assoc($carga_tipos)) : ?>
                        <?php if ($fila['cati_id'] != 1) continue; // Mostrar solo el ID 3 TRANSF IMPORTACION 
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
                     <td>
                        <div class="form-group">
                           <textarea class="form-control" name="carg_nota" id="carg_nota"><?php echo isset($res["carg_nota"]) ? $res["carg_nota"] : '' ?></textarea>
                        </div>
                     </td>
                  </tr>
               </tbody>
            </table>
      </form>

      <?php if (isset($_GET["carg_id"])): ?>
         <div class="table-responsive px-3" style="height:450px !important">
            <!-- Boton del modal Formulario carga detalle -->
            <table class="table table-bordered w-100 table-sm text-center">
               <thead class="bg-dark">
                  <tr>
                     <th>Item</th>
                     <th>Nro. Guía</th>
                     <th>Piezas</th>
                     <th>Shipper</th>
                     <th>Consignee</th>
                     <th>Peso</th>
                     <th>Cantidad Pallet/Contenedor</th>
                     <th>Descripcion</th>
                     <th>Localización</th>
                     <th>Tipo de carga</th>
                     <th>Estado de la carga</th>
                     <th>Recibo de Carga</th>
                     <th></th>

                  </tr>
               </thead>
               <tbody id="tbody-carga-detalles">
                  <tr>
                     <form id="formulario-nueva-carga-detalle">
                        <td></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_guia_n" data-campo="cade_guia" style="width: 100px" type="text" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_piezas" data-campo="cade_piezas" style="width: 100px" type="text" /></td>
                        <td class="form-group" style="min-width: 200px;">
                           <!-- <div class="select-container">
                              <select class="form-control" style="width: 100%;"  id="ship_id" name="ship_id">
                              </select>
                           </div> -->
                           <?php selectShipper() ?>
                        </td>
                        <td class="form-group">
                           <!-- <div class="select-container">
                              <select class="form-control" style="width: 100%" id="cons_id" name="cons_id">
                              </select>
                           </div> -->
                           <?php selectConsignee() ?>
                        </td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_peso" data-campo="cade_peso" style="width: 100px" type="number" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_cantidad" data-campo="cade_cantidad" style="width: 100px" type="number" /></td>
                        <td><input data-padreid="<?php echo $_GET["carg_id"] ?>" name="cade_desc" data-campo="cade_desc" style="width: 200px" type="text" /></td>
                        <td class="form-group">


                           <?php echo catalogo('carga_localizaciones_bodega', '', 'calb_nombre', 'calb_id', 'calb_id', 'calb_nombre', '0', '1', '200', '', '', '', '', '3'); ?>

                        </td>
                        <td class="form-group" style="min-width: 200px;">
                           <select class="form-control" id="cade_tipo_id" name="cade_tipo_id">
                              <?php while ($fila = mysql_fetch_assoc($cargadetalle_tipo)): ?>
                                 <option value="<?php echo $fila['cade_tipo_id'] ?>"><?php echo $fila["cade_descripcion"] ?></option>
                              <?php endwhile; ?>
                           </select>
                        </td>
                        <td class="form-group">


                           <?php echo catalogo('carga_estado', '', 'caes_nombre', 'caes_id_n', 'caes_id', 'caes_nombre', '0', '0', '200', '', '', '', '', '4'); ?>

                        </td>
                     </form>
                     <td><button class="btn btn-success btn-sm" onclick="registrarCargadetalle()"><i class="fa-solid fa-plus"></i></button></td>
                  </tr>
               </tbody>
            </table>
         </div>
      <?php endif ?>
   </div>
</section>

<!-- Modal for Transportista Details -->
<div class="modal fade" id="modalTransportista" tabindex="-1" role="dialog" aria-labelledby="modalTransportistaLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalTransportistaLabel">Detalles del Transportista</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="formulario-transportista">
               <input type="hidden" id="transportista-cade-id" name="cade_id">

               <!-- Nombre del Transportista -->
               <div class="form-group">
                  <label for="cade_transportista_nombre">Nombre del Transportista</label>
                  <input type="text" class="form-control" id="cade_transportista_nombre" name="cade_transportista_nombre" required>
               </div>

               <!-- Cédula/Identificación -->
               <div class="form-group">
                  <label for="cade_transportista_cedula">Cédula/Identificación</label>
                  <input type="text" class="form-control" id="cade_transportista_cedula" name="cade_transportista_cedula" required>
               </div>

               <!-- Matrícula/Licencia -->
               <div class="form-group">
                  <label for="cade_transportista_matricula">Matrícula/Licencia</label>
                  <input type="text" class="form-control" id="cade_transportista_matricula" name="cade_transportista_matricula" required>
               </div>

               <!-- Tipo de Transporte -->
               <div class="form-group">
                  <label for="transporte_id">Tipo de Transporte</label>
                  <select id="transporte_id" class="form-control custom-select" name="transporte_id" required>
                     <option selected disabled>Seleccione una opción</option>
                     <?php while ($fila = mysql_fetch_assoc($transporte)) : ?>
                        <option <?php if ($fila["tran_id"] == $res["tran_id"]) {
                                    echo "selected";
                                 } ?> value="<?php echo $fila["tran_id"] ?>"><?php echo $fila["tran_nombre"] ?></option>
                     <?php endwhile ?>
                  </select>
               </div>

            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-warning" id="btnImprimirReciboCarga">
               <i class="fas fa-print"></i> Imprimir
            </button>
            <!-- <button type="button" class="btn btn-primary">
               <i class="fas fa-save"></i> Guardar
            </button> -->
            <button type="button" class="btn btn-primary" onclick="guardarTransportista()">Guardar</button>
         </div>
      </div>
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
      z-index: 1050;
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
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="editarModalLabel">Editar Detalle</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="editarForm">
               <input type="hidden" id="detalle_id" name="detalle_id">

               <!-- Guía Input -->
               <div class="form-group">
                  <label for="cade_guia">Guía</label>
                  <input type="text" class="form-control" id="cade_guia" name="cade_guia">
               </div>

               <!-- Tipo de Carga Dropdown -->
               <div class="form-group">
                  <label for="tipo_carga">Tipo de Carga</label>
                  <select id="tipo_carga" class="form-control custom-select" name="tipo_carga">
                     <option selected disabled>Seleccione una opción</option>
                     <?php
                     // Query to fetch the current carga_detalle_tipo and all available options
                     $carg_id = intval($_GET['carg_id']); // Make sure $carg_id is securely passed to the script
                     $query = "
                SELECT 
                  cd.cade_tipo_id AS current_tipo_id, 
                  cdt.cade_tipo_id, 
                  cdt.cade_descripcion 
                FROM carga_detalles cd
                LEFT JOIN carga_detalle_tipo cdt ON cd.cade_tipo_id = cdt.cade_tipo_id
                WHERE cd.carg_id = $carg_id
              ";

                     $result = mysql_query($query);
                     $current_tipo_id = null;

                     // Fetch current selected value
                     while ($row = mysql_fetch_assoc($result)) {
                        if ($row['current_tipo_id']) {
                           $current_tipo_id = $row['current_tipo_id'];
                        }
                     }

                     // Query to fetch all available types of carga
                     $query_tipos = "SELECT cade_tipo_id, cade_descripcion FROM carga_detalle_tipo ORDER BY cade_descripcion";
                     $result_tipos = mysql_query($query_tipos);

                     // Populate the dropdown
                     while ($row_tipos = mysql_fetch_assoc($result_tipos)) {
                        $selected = ($row_tipos['cade_tipo_id'] == $current_tipo_id) ? 'selected' : '';
                     ?>
                        <option value="<?php echo $row_tipos['cade_tipo_id']; ?>" <?php echo $selected; ?>>
                           <?php echo $row_tipos['cade_descripcion']; ?>
                        </option>
                     <?php } ?>
                  </select>
               </div>
            </form>
         </div>

         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" onclick="guardarCambios()">Guardar Cambios</button>
         </div>
      </div>
   </div>
</div>


<!-- Modal Structure -->
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
               <!-- Shipper Fields -->
               <div class="form-group c_shipper" style="display:none;">
                  <label for="snameInput">Nombre</label>
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
                  <label for="nameInput">Nombre</label>
                  <input type="text" class="form-control" id="nameInput" name="i_cons_nombre" required>
               </div>
               <div class="form-group c_consignee" style="display:none;">
                  <label for="ciudadInput">Ciudad</label>
                  <input type="text" class="form-control" id="ciudadInput" name="i_cons_ciudad" required>
               </div>
               <div class="form-group c_consignee" style="display:none;">
                  <label for="emailInput">Email</label>
                  <input type="email" class="form-control" id="emailInput" name="i_cons_email">
               </div>
               <div class="form-group c_consignee" style="display:none;">
                  <label for="telefonoInput">Teléfono</label>
                  <input type="tel" class="form-control" id="telefonoInput" name="i_cons_telefono">
               </div>
               <?php echo autocompletar_filtro('paisinput', 'obtener_paises.php', 'codigo_pais', '3', 'c_pais') ?>
               <input type="hidden" id="c_pais" name="i_pais_id">
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
                     <input type="text" class="disabled-input-fact esconder-fact transporte-update form-control" data-campo="cade_transportista_nombre" data-cadeId="" name="inpNombre" placeholder="Nombre">
                  </div>
                  <div class="form-group col">
                     <input type="text" class="disabled-input-fact transporte-update esconder-fact form-control" data-campo="cade_transportista_cedula" data-cadeId="" name="inpCedula" placeholder="Cédula/RUC">
                  </div>
                  <div class="form-group col">
                     <input type="text" class="disabled-input-fact transporte-update form-control esconder-fact" data-campo="cade_transportista_matricula" data-cadeId="" name="inpMatricula" placeholder="Matrícula">
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
                     <input type="hidden" name="carg_id" value="<?php echo $_GET["carg_id"] ?>">
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
                        <td></td>
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
            <a href="" class="btn btn-warning" target="_blank" id="btnVerfactura">VER FACTURA</a>
            <button type="button" class="btn btn-primary" id='btnFacturar' onclick="facturar()">FACTURAR</button>
         </div>
      </div>
   </div>
</div>

<?php include "./recibos_carga_detalle_detalle_modal.php" ?>

<script>
   $(function() {

      <?php if ($caes_id == 1): ?>

         // Si el estado es 1, mostrar el botón

         $(".btn_guardar_trans_export").show();

      <?php else: ?>

         // Si el estado es distinto de 1, ocultar el botón y aplicar roles

         $(".btn_guardar_trans_export").hide()



         <?php echo pantalla_roles("index.php?p=recibos-carga", $_SESSION["login_user"]) ?>

      <?php endif; ?>

   });


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

   document.addEventListener("DOMContentLoaded", function() {
      let select = document.querySelector("select[name='calb_id']");
      if (select) {
         select.selectedIndex = -1; // Desselecciona cualquier opción
      }
   });


   $(".ocultar").hide()
   $(".disabled").prop("disabled", true)
   $(document).ready(function() {

      $('input[type="radio"][name="direccion"]').change(function() {
         // Si se selecciona la opción Export

         if ($(this).val() !== '1' && $(this).val() !== '2') {
            // Ocultar los campos adicionales
            $('.ocultar').hide();
            $(".disabled").prop("disabled", true)
         } else {
            // Mostrar los campos adicionales para otras opciones
            $('.ocultar').show();
            $(".disabled").prop("disabled", false)
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
      const datos = new FormData($("#form-nueva-carga")[0])

      $.ajax({
         url: "ajax/carga.php",
         method: "POST",
         contentType: false,
         processData: false,
         data: datos,
         success: res => {
            // Aquí se ejecuta si el formulario es válido
            // alert("Mensaje enviado exitosamente");
            // const carg_id = JSON.parse(res).carg_id
            // $("#carg_id").val(carg_id)
            // console.log(carg_id);
            // if (carg_id) {
            //    window.location.href = "index.php?p=recibos-carga&carg_id=" + carg_id
            // }
         }
      })
   }

   function registrarCargadetalle() {
      // Prevent default form submission
      event.preventDefault();

      var inputValues = [];
      $('.ms-drop li.selected').each(function() {
         var inputValue = $(this).find('input').val();
         inputValues.push(inputValue);
      })

      let datos = new FormData($("#formulario-nueva-carga-detalle")[0]);
      datos.append("carg_id", $("#carg_id").val());
      datos.append("calb_id", inputValues.join(","))

      // Log para verificar los datos
      // for (let [key, value] of datos.entries()) {
      // console.log(`${key}: ${value}`);
      // }

      $.ajax({
         url: "ajax/carga-detalles.php",
         method: "POST",
         contentType: false,
         processData: false,
         data: datos,
success: res => {
    console.log("Respuesta cruda:", res);

    // Elimina cualquier cosa antes del primer '{'
    const jsonStart = res.indexOf('{');
    if (jsonStart === -1) {
        alert("Error inesperado en el servidor");
        return;
    }
    res = res.substring(jsonStart);

    let data;
    try {
        data = JSON.parse(res);
    } catch (e) {
        console.error("Respuesta no es JSON válido:", res);
        alert("Error inesperado en el servidor");
        return;
    }

    if (!data.success) {
        alert(data.message || "Ocurrió un error desconocido");
        return;
    }

    // Si todo sale bien:
    $("#formulario-nueva-carga-detalle").trigger("reset");
    $('#cons_id').empty().trigger('change');
    $('#ship_id').empty().trigger('change');
    $('#calb_id').multipleSelect('uncheckAll');
    $('#calb_id').multipleSelect('refresh');
    obtenerDetalles();
},


         error: function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
         }
      });
   }

   function guardarDetalle() {
      const cade_id = $('#cade_id').val();
      const cade_tipo_id = $('#cade_tipo_id').val();

      $.ajax({
         url: 'ajax/carga_detalle_guardar.php',
         method: 'POST',
         data: {
            cade_id: cade_id,
            cade_tipo_id: cade_tipo_id
         },
         success: function(response) {
            const res = JSON.parse(response);
            if (res.success) {
               alert('Los cambios se guardaron correctamente.');
               location.reload(); // Recargar la página o actualizar la tabla
            } else {
               alert('Error: ' + res.error);
            }
         }
      });
   }

   function editarDetalle(element) {
      const cadeId = $(element).data('cadeid');
      const urlParams = new URLSearchParams(window.location.search);
      const padreId = urlParams.get('carg_id');

      $.ajax({
         url: 'ajax/carga_detalle_modificar.php',
         method: 'GET',
         data: {
            carg_id: padreId,
            cade_id: cadeId
         },
         success: function(response) {
            const detalle = JSON.parse(response);

            // console.log('Dropdown Options:', $('#tipo_carga').html()); // Check dropdown options
            // console.log('Received detalle:', detalle); // Check the AJAX response

            // Set form values
            $('#tipo_carga').val(detalle.cade_tipo_id); // Use cade_tipo_id here
            $('#cade_guia').val(detalle.cade_guia);
            $('#editarModal').modal('show');
         },
         error: function(xhr, status, error) {
            console.error('Error:', error);
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
      )
      obtenerDetalles();
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

   function crearSelectEstadoJQuery(selectedId, cade_id, padre_id) {
      console.log('crearSelectEstadoJQuery - selectedId:', selectedId, 'tipo:', typeof selectedId);

      let selectHTML = `<select class="form-control change-update" data-campo="caes_id" data-cadeid="${cade_id}" data-padreid="${padre_id}" name="cade_estado" style="width: 200px;">`;

      catalogoEstados.forEach(estado => {
         const estadoId = String(estado.caes_id);
         const seleccionado = String(selectedId);
         const isSelected = estadoId === seleccionado;

         console.log('Comparando:', estadoId, 'con:', seleccionado, 'isSelected:', isSelected);

         selectHTML += `<option value="${estado.caes_id}" ${isSelected ? 'selected' : ''}>${estado.caes_nombre}</option>`;

         if (isSelected) {
            console.log('¡SELECCIONADO!', estado.caes_nombre);
         }
      });

      selectHTML += '</select>';

      return selectHTML;
   }


   function obtenerDetalles() {
      const carg_id = $("#carg_id").val().trim();
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
               let i = 1;

               $(".remove-update").remove()
               if (!detalles) {
                  const row = `<tr colspan="10"><b>NO HAY DETALLES</b></tr>`;
                  tbody.append(row);
               } else {

                  detalles.forEach(detalle => {
                     // Construir el select del estado, con el valor actual del detalle
                     const selectEstadoHTML = crearSelectEstadoJQuery(detalle.caes_id, detalle.cade_id, detalle.carg_id);
                     const row = `
                            <tr class="remove-update">
                                <td>${i++}</td>
                                <td><input class="change-update cade_guia" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_guia" style="width: 100px" type="text" value="${detalle.cade_guia}" /></td>
                                <td><input class="change-update cade_piezas" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_piezas" style="width: 100px" type="text" value="${detalle.cade_piezas}" /></td>
                                <td><input class="change-update shipper" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="ship_id" style="width: 150px" type="text" value="${detalle.shipper_nombre}" readonly /></td>
                                <td><input class="change-update consignee" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cons_id" style="width: 150px" type="text" value="${detalle.consignee_nombre}" readonly /></td>
                                <td><input class="change-update cade_peso" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_peso" style="width: 100px" type="number" value="${detalle.cade_peso}" /></td>
                                <td><input class="change-update cade_cantidad" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_cantidad" style="width: 100px" type="number" value="${detalle.cade_cantidad}" /></td>

                                <td><input class="change-update cade_desc" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_desc" style="width: 200px" type="text" value="${detalle.cade_desc}" /></td>
                                <td><input class="change-update calb_id" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="nombre_localizacion" style="width: 200px" type="text" value="${detalle.lista_localizaciones}" readonly /></td>
                                <td><input class="change-update cade_tipo" data-padreid="${detalle.carg_id}" data-cadeid="${detalle.cade_id}" data-campo="cade_tipo" style="width: 100px" type="text" value="${detalle.cade_tipo}" readonly /></td>
                                <td>${selectEstadoHTML}</td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick='mostrarModalTransportista(${detalle.cade_id})'>
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-danger btn-borrar btn-sm" onclick="eliminarCargaDetalle(${detalle.cade_id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-borrar btn-sm" data-toggle="modal" data-target="#modalDetalleDetalle" data-cadeid=${detalle.cade_id}>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;

                     tempContainer = document.createElement("tr")
                     tempContainer.classList.add("remove-update")

                     if (detalle.caes_id == 3) {
                        tempContainer.classList.add("bg-danger")
                     }

                     if (detalle.cade_notificada_fecha && detalle.cade_facturada == 0) {
                        const fechaDetalle = new Date(detalle.cade_notificada_fecha);
                        const ahora = new Date();
                        const diferenciaMs = ahora.getTime() - fechaDetalle.getTime();
                        const diferenciaDias = Math.floor(diferenciaMs / (1000 * 60 * 60 * 24));

                        if (diferenciaDias >= 15) {
                           tempContainer.style.backgroundColor = "red";
                           tempContainer.style.color = "white";
                        } else if (diferenciaDias >= 8) {
                           tempContainer.style.backgroundColor = "#FFF011"; // Amarillo
                           tempContainer.style.color = "black"; // Mejor visibilidad
                        }
                     }

                     tempContainer.innerHTML = row.trim()
                     tbody.append(tempContainer);
                  });

                  // Asegurar que los eventos de cambio se asignen después de crear todos los elementos
                  $('.change-update').off('change').on('change', cambioTiempoReal);
               }
            },
            error: function(xhr, status, error) {
               console.error("Error al obtener detalles de carga:", error);
            }
         });
      }
   }

   // Variable global para guardar el ID del recibo actual
   let reciboSeleccionadoId = null;

   function mostrarModalTransportista(cade_id) {
      // Limpiar los campos del modal al inicio para evitar datos anteriores
      $('#transportista-cade-id').val('');
      $('#cade_transportista_nombre').val('');
      $('#cade_transportista_cedula').val('');
      $('#cade_transportista_matricula').val('');
      $('#transporte_id').val('');

      // Almacenar el nuevo ID en la variable global
      reciboSeleccionadoId = cade_id;

      // Realizar la llamada AJAX para obtener los detalles
      $.ajax({
         url: "ajax/carga-detalles.php",
         method: "GET",
         data: {
               cade_id: cade_id
         },
         success: function(response) {
               const detalle = JSON.parse(response);

               // Rellenar los campos del modal
               $('#transportista-cade-id').val(cade_id);
               $('#cade_transportista_nombre').val(detalle.cade_transportista_nombre || '');
               $('#cade_transportista_cedula').val(detalle.cade_transportista_cedula || '');
               $('#cade_transportista_matricula').val(detalle.cade_transportista_matricula || '');
               $('#transporte_id').val(detalle.tran_id || '');

               // Abrir el modal
               $('#modalTransportista').modal('show');
         },
         error: function() {
               console.error("Error fetching cargo detail");
               alert("No se pudieron cargar los detalles del transportista");
         }
      });
   }

   // El evento de clic para el botón de imprimir se define UNA SOLA VEZ
   // fuera de la función mostrarModalTransportista.
   $("#btnImprimirReciboCarga").on("click", function() {
      // Usar la variable global para llamar a la función de impresión
      if (reciboSeleccionadoId !== null) {
         imprimirRecibo(reciboSeleccionadoId);
      } else {
         console.error("No hay un recibo seleccionado para imprimir.");
      }
   });

   function guardarTransportista() {
      const formData = new FormData($("#formulario-transportista")[0]);
      formData.append("a", "transportista");

      $.ajax({
         url: "ajax/carga-detalles.php",
         method: "POST",
         data: formData,
         processData: false, // Evita que jQuery procese el FormData
         contentType: false, // Evita que jQuery establezca un Content-Type incorrecto
         success: function(response) {
            try {
               const result = JSON.parse(response);
               if (result.success) {
                  // $('#modalTransportista').modal('hide');
                  // obtenerDetalles();
               } else {
                  alert("Error al guardar los detalles del transportista");
               }
            } catch (e) {
               console.error("Error parsing response", e);
               console.error("Respuesta recibida:", response);
            }
         },
         error: function(xhr, status, error) {
            console.error("Error al guardar transportista:", error);
            alert("Error al guardar los detalles del transportista");
         }
      });
   }


   $('#modalTransportista').on('shown.bs.modal', function() {

   })

   function imprimirRecibo(cade_id) {
      // Get carg_id from URL
      const urlParams = new URLSearchParams(window.location.search);
      const carg_id = urlParams.get('carg_id');

      // Create URL with both IDs
      const url = `factura_recibo_carga.php?carg_id=${carg_id}${cade_id ? '&cade_id=' + cade_id : ''}`;
      window.open(url, '_blank');
   }


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

   // Agregar esto junto con tus otros scripts existentes
   $(document).ready(function() {
      $('.custom-select:not(#calb_id_detalle_detalle)').each(function() {
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
         $(document).mouseup(function(e) {
            if (!container.is(e.target) && container.has(e.target).length === 0) {
               searchContainer.hide();
            }
         });
      });
   });

   // CAJA----------------------------------------------------
   $("#btnSaveServicio").click(guardarServicios)
   $("#selectCaseId").change(function() {

      // Peticion para buscar la
      $.ajax({

      })
   })

   function mostrarCajaDetalles(id, facturacion, guia) {

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
               // console.log(res.detail_info.cade_transportista_nombre);
               $("input[name='inpNombre']").val(res.detail_info.cade_transportista_nombre)
               $("input[name='inpCedula']").val(res.detail_info.cade_transportista_cedula)
               $("input[name='inpMatricula']").val(res.detail_info.cade_transportista_matricula)

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
      $.post(`./ajax/cargos_servicios.php?ruc=${$("input[name='inpConsRuc']").val()}&tipo=2&dv=${$("input[name='inpConsDv']").val(), $("#i_fopa_codigo").val()}`, {
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
            let error = JSON.parse(jqXHR.responseText).mensaje;
            Swal.fire({
               icon: "error",
               title: error
            });
         })
         .always(() => {
            // console.log("Petición completada.")
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

   
$(document).ready(function() {
    
    // 1. INYECTAR EL ROL DEL USUARIO DESDE PHP
    const userRoleId = <?php echo $usti_id_actual; ?>; 
    // Los roles 0 (Super Usuario) y 5 (Supervisor) están autorizados
    const isUserAllowedToEdit = (userRoleId === 0 || userRoleId === 5);
    
    // Llamada inicial para chequear el estado al cargar la página
    verificarEstadoCarga();
    
    // 2. FUNCIÓN PARA VERIFICAR EL ESTADO DE LA CARGA (usando AJAX)
    function verificarEstadoCarga() {
        // Obtenemos el carg_id si existe
        const cargId = $('#carg_id').val(); 
        
        // 2a. Si no hay carg_id o el usuario está permitido, terminamos.
        if (!cargId || isUserAllowedToEdit) {
            if (isUserAllowedToEdit) {
                console.log("Usuario autorizado (usti_id: " + userRoleId + "). Formulario editable.");
            }
            return; 
        }

        // 2b. Si el usuario NO está permitido, chequeamos el estado de la carga (caes_id)
        $.ajax({
            url: 'ajax/verificar_carga_estado.php', 
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'verificar_estado',
                carg_id: cargId
            },
            success: function(respuesta) {
                // Si la carga está Recibida (estado == 2)
                if (respuesta.success && respuesta.estado == 2) {
                    deshabilitarFormulario();
                    // Opcional: mostrar un mensaje más claro
                    // alert("Esta carga ha sido RECIBIDA y no puedes editarla. Solo Super Usuarios y Supervisores pueden modificarla."); 
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al verificar el estado de la carga: " + error);
            }
        });
    }

    // 3. FUNCIÓN PARA DESHABILITAR EL FORMULARIO (Asegurarse de que deshabilita todo)
    function deshabilitarFormulario() {
        // Deshabilitar inputs y botones del formulario principal
        $('#form-nueva-carga :input, #btnRecibir, .btn.btn-success:contains("GUARDAR")').prop('disabled', true);
        
        // Deshabilitar la tabla de detalles (incluyendo el botón de agregar)
        $('#tbody-carga-detalles :input, #tbody-carga-detalles button').prop('disabled', true);
        
        // Excepción: Permitir la impresión si existe un enlace/botón para ello
        $('a[href*="factura_recibo_carga.php"], #btnImprimirReciboCarga').prop('disabled', false).removeClass('disabled');
        
        // Cambiar estilo visual
        $('#form-nueva-carga :input, #tbody-carga-detalles :input').css({
            'background-color': '#e9f4f7ff',
            'color': '#3f3e3eff'
        });
    }
    
    // 4. BOTÓN RECIBIR
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