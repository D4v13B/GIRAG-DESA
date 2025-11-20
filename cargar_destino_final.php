

<body class="bg-light">
    <div class="container py-5 bg-white min-vh-100 d-flex justify-content-center align-items-start">
        <div class="card shadow-sm border-0 p-4 rounded-4 col-12 col-md-6">
            <h2 class="fw-bold text-center mb-4">Registrar Destino</h2>
            
            <!-- Mensaje informativo compacto -->
            <div class="alert alert-primary border-0 rounded-3 mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-file-excel me-2"></i>Formato del archivo Excel[.xls,.xlsx]:
                </h6>
                <p class="mb-0 small">
                    <strong>Columnas:</strong> PAIS | CODIGO DE AEROPUERTO | NOMBRE DE AEROPUERTO 
                </p>
                <small>-Sin encabezados-</small>
            </div>
            
            <form id="form-archivo" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="excel" class="form-label fw-semibold">Selecciona un archivo Excel</label>
                    <input class="form-control form-control-lg" type="file" id="excel" name="excel" 
                           accept=".xlsx,.xls" required>
                </div>
                <div class="text-center mt-4">
                    <button type="button" id="cargar-archivo" class="btn btn-primary btn-lg px-5 shadow-sm">
                        <i class="fas fa-upload me-2"></i>Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>


<script>
   $("#cargar-archivo").on("click", function() {
      const datos = new FormData($("#form-archivo")[0])
      $.ajax({
         url: "ajax/cargar-destino-final.php",
         method: "POST",
         data: datos,
         processData: false,
         contentType: false,
         success: res => {
            console.log(res);
            // Buscar el JSON al final de la respuesta
            if (res.includes('{"success":true}')) {
               alert("Destinos registrados correctamente");
               // Limpiar el input de archivo
               $("#form-archivo")[0].reset();
            } else {
               alert("Error al registrar destinos, formato Inválido");
            }
         },
         error: function() {
            alert("Error al procesar el archivo");
         },
         complete: function() {
            console.log("lesgoo");
         }
      })
   })
</script>