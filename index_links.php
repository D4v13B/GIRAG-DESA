<!--E-INTEGRACION AJUSTES CSS Y SCRIPTS-->
<link rel="stylesheet" href="tablas.css?nock=<?php echo date('Ymdst')?>" type="text/css" media="screen" />
<link rel="stylesheet" href="style.css?nock=<?php echo date('Ymdst')?>" type="text/css" media="screen" />
<link rel="stylesheet" href="interface.css?nock=<?php echo date('Ymdst')?>" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="jquery/multiple-select.css" />
<script type="text/javascript" src="jquery/multiple-select.js"></script>
<script type="text/javascript" src="jquery/jquery.table2excel.js"></script>
<script src="modal.js"></script>
<script src="funciones.js"></script>
<script src="jquery/pdfobject/pdfobject.js"></script>
<script src="jquery/jquery.redirect.js"></script>
<script src="jquery/jquery.timeentry.pack.js"></script>
<script src="jquery/ckeditor4.9.2/ckeditor.js"></script>
<script src="jquery/ckeditor4.9.2/adapters/jquery.js"></script>
<script src="jquery/uploader/jquery.form.min.js" type="text/javascript"></script>
<script src="jquery/jquery.timepicker.js" type="text/javascript"></script>
<script src="plugins/jquery-validation-1.19.5/dist/jquery.validate.min.js"></script>
<script src="plugins/jquery-validation-1.19.5/dist/additional-methods.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Tempusdominus Bbootstrap 4 -->
<link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<!-- JQVMap -->
<link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="dist/css/adminlte.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Daterange picker -->
<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
<!-- summernote -->
<link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<link rel="stylesheet" href="plugins/croppie/croppie.css">
<script src="plugins/croppie/croppie.min.js"></script>
<script>
   $(document).ready(function () {
    $('input[type=file]').on('change', function() {
      var fileName = $(this).val().split('\\').pop();
      // console.log("Hola", fileName);
      $(this).next('.custom-file-label').html(fileName);
    });
  });
</script>