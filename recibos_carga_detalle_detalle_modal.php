<?php

$carga_detalle_tipo_detalle = mysql_query("SELECT * FROM carga_detalle_tipo");

?>



<!-- Modal -->
<div class="modal fade" id="modalDetalleDetalle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar Detalle de carga</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">

          <table class="table table-bordered w-100 table-sm text-center">
            <thead>
              <td>Peso</td>
              <td>Piezas</td>
              <td>Descripcion</td>
              <td>Localizacion</td>
              <td>Tipo de carga</td>
              <td></td>
            </thead>
            <tbody id="tbody-detalle-detalle">
              <form id="form_nuevo_detalle_detalle">
                <tr>
                  <input type="hidden" id="cade_id" name="cade_id">
                  <td><input type="number" name="cadd_peso_n" id="cadd_peso_n"></td>
                  <td><input type="number" name="cadd_piezas_n" id="cadd_piezas_n"></td>
                  <td><input type="text" name="cadd_descripcion_n" id="cadd_descripcion_n"></td>
                  <td class="form-group z-1">
                    <?php echo catalogo('carga_localizaciones_bodega', '', 'calb_nombre', 'calb_id_detalle_detalle', 'calb_id', 'calb_nombre', '0', '1', '200', '', '', '', '', '4'); ?>
                  </td>
                  <td class="form-group" style="min-width: 200px;">
                    <select class="form-control" id="cade_tipo_id_detalle_detalle" name="cade_tipo_id">
                      <?php while ($fila = mysql_fetch_assoc($carga_detalle_tipo_detalle)): ?>
                        <option value="<?php echo $fila['cade_tipo_id'] ?>"><?php echo $fila["cade_descripcion"] ?></option>
                      <?php endwhile; ?>
                    </select>
                  </td>
                  <td>
                    <span class="btn btn-success" onclick="guardarDetalleDetalle()">
                      <i class="fas fa-plus"></i>
                    </span>
                  </td>
                </tr>
              </form>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
      </div>
    </div>
  </div>
</div>
<script>
  const inp_cade_id = $("#modalDetalleDetalle #cade_id")

  $("#modalDetalleDetalle").on("show.bs.modal", function(e) {
    var button = $(e.relatedTarget)
    const cadeId = button.data("cadeid")

    var modal = $(this)

    inp_cade_id.val(cadeId)

    traerDetalleDetalles();
    
  })

  function guardarDetalleDetalle() {
    const data = new FormData($("#form_nuevo_detalle_detalle")[0])

    var inputValues = [];
    $('#tbody-detalle-detalle .ms-drop li.selected').each(function() {
      var inputValue = $(this).find('input').val();
      inputValues.push(inputValue);
    })

    data.append("calb_id", inputValues.join(","))

    $.ajax({
      url: "./ajax/carga_detalle_detalle.php",
      method: "POST",
      contentType: false,
      processData: false,
      data,
      success: res => {
        console.log(res)
      },
      complete: res => {
        traerDetalleDetalles();
        limpiarform();
        obtenerDetalles();
        
        
      }
    })
  }

  function limpiarform(){
    $("#form_nuevo_detalle_detalle")[0].reset();
    $('#tbody-detalle-detalle .ms-drop li-selected').removeClass('selected').find('input').prop('checked',false);

  }
 
  function traerDetalleDetalles() {
    const cade_id = inp_cade_id.val()

    let html = ""

    $(".remove-update-detalle-detalle").remove()

    $.ajax({
      url: `./ajax/carga_detalle_detalle.php?cade_id=${cade_id}`,
      success: res => {
        res = JSON.parse(res)

        res.forEach(e => {
          html += `
            <tr class="remove-update-detalle-detalle">
            <td>${e.cadd_peso}</td>
            <td>${e.cadd_piezas}</td>
            <td>${e.cadd_descripcion}</td>
            <td>${e.localizaciones}</td>
            <td>${e.cade_descripcion}</td>
            </tr>`
        });

        $("#tbody-detalle-detalle").append(html)
      }
    })
  }
</script>