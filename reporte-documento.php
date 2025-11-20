<?php

$sql = "SELECT * FROM departamentos";
$idDepartamento = mysql_query($sql);

$sql = "SELECT * FROM
reportes_documentos_estado p, reportes_documentos a
WHERE p.rede_id = a.rede_id";
$sql = "SELECT * FROM reportes_documentos_estado";

$reporte_id = mysql_query($sql);

$sql = "SELECT * FROM usuarios where usti_id = 8 ";
$usuario_id = mysql_query($sql);
?>
<div class="conatainer">
    <section class="card d-flex justify-content-center col-6 m-auto">
        <div class="card border">

            <form class="card-body" id="form-nuevo-reporte">

                <h2> Reporte de Documento</h2>

                <span class="btn btn-success" onclick="registrarReporte()">RECIBIR</span>
                <button type="reset" class="btn btn-success">ANULAR</button>

                <!-- Columna 1 -->
                <!-- Form registro  reporte documento  -->
                <!-- Titulo -->
                <div class="form-group">
                    <label for="redo_titulo"> Titulo</label>
                    <input type="text" id="redo_titulo" class="form-control" name="redo_titulo">
                </div>
                <!-- Descripción -->
                <div class="form-group">
                    <label for="redo_descripcion"> Descripción</label>
                    <input type="text" id="redo_descripcion" class="form-control" name="redo_descripcion">
                </div>
                <!-- ID gerente -->
                <div class="form-group">
                    <label for="usua_id_gerente">Gerente</label>
                    <select id="<?php echo $fila["usua_id"] ?>" class="form-control custom-select" name="usua_id_gerente" value="<?php echo $fila["usua_id"] ?>">
                        <option selected disabled>Seleccione una opción</option>
                        <?php while ($fila = mysql_fetch_assoc($usuario_id)) : ?>
                            <option value="<?php echo $fila["usua_id"] ?>"><?php echo $fila["usua_nombre"] ?></option>
                        <?php endwhile ?>

                    </select>
                </div>

                <!-- Id del detalle -->
                <div class="form-group">
                    <label for="rede_id"> Estado</label>

                    <select id="<?php echo $fila["rede_id"] ?>" class="form-control custom-select" name="rede_id" value="<?php echo $fila["rede_id"] ?>">
                        <option selected disabled>Seleccione una opción</option>
                        <?php while ($fila = mysql_fetch_assoc($reporte_id)) : ?>
                            <option value="<?php echo $fila["rede_id"] ?>"><?php echo $fila["rede_nombre"] ?></option>
                        <?php endwhile ?>

                    </select>
                </div>

                <!-- Id del departamento -->
                <div class="form-group">
                    <label for="depa_id"> Departamento</label>

                    <select id="<?php echo $fila["depa_id"] ?>" class="form-control custom-select" name="depa_id" value="<?php echo $fila["depa_id"] ?>">
                        <option selected disabled>Seleccione una opción</option>
                        <?php while ($fila = mysql_fetch_assoc($idDepartamento)) : ?>
                            <option value="<?php echo $fila["depa_id"] ?>"><?php echo $fila["depa_nombre"] ?></option>
                        <?php endwhile ?>

                    </select>

                </div>


                <!-- Comentario-->
                <div class="form-group">
                    <label for="comentario"> Comentario</label>
                    <input type="text" id="comentario" class="form-control" name="comentario">

                </div>
            </form>
        </div>
    </section>
</div>


<script>
    function registrarReporte() {
        const datos = new FormData($("#form-nuevo-reporte")[0])

        $.ajax({
            url: "ajax/reportes-documentos.php",
            method: "POST",
            contentType: false,
            processData: false,
            data: datos,
            success: res => {
                // Aquí se ejecuta si el formulario es válido
                alert("Mensaje enviado exitosamente");

            }
        })
    }

    $("document").ready(function() {

        $("#form-nuevo-reporte").validate({

            rules: {

                redo_titulo: "required",
                redo_descripcion: "required",
                usua_id_gerente: "required",
                rede_id: "required",
                depa_id: "required",
                comentario: "required"


            },
            messages: {
                redo_titulo: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
                redo_descripcion: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
                usua_id_gerente: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
                rede_id: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
                depa_id: "<span style='color: red; font-size: smaller;'>Complete este campo</span>",
                comentario: "<span style='color: red; font-size: smaller;'>Complete este campo</span>"


            },
            submitHandler: function(form) {

                registrarReporte();
            }

        });

    });
</script>