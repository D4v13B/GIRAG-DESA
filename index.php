<?php 
include('conexion.php');
include('funciones.php');
include('seguridad.php');
$usua_id = $_SESSION["login_user"];

$sql = "SELECT usua_administrador_caso FROM usuarios WHERE usua_id=$usua_id";
$_SESSION["administrador_caso"] = mysql_fetch_assoc(mysql_query($sql))["usua_administrador_caso"]
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Girag Logic | Dashboard</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="plugins/jQuery/jquery-3.4.1.min.js"></script>
	<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
	<link href="plugins/jQueryUI/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php include('index_links.php')?>
  </head>
<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
      <?php include('index_navbar.php')?>
	  <?php include('aside.php');?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper" id="contenedor">
        <!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
					<h1 class="m-0 text-dark">GIRAG</h1>
					</div><!-- /.col -->
					<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
					  <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
						<?php
						//esta parte me permite mostrar donde estoy en el sistema
						//saco el final del url
						$finalurl = array_slice(explode('/', $_SERVER['REQUEST_URI']), -1)[0];
						//lo busco en menu
						$qsql = "select mema_nombre, menu_nombre from menu a, menu_madre b where a.mema_id=b.mema_id and menu_url='$finalurl'";
						$rsnav = mysql_query($qsql);
						$num_nav =mysql_num_rows($rsnav);
						if($num_nav>0)
						{
							$i=0;
							$mema=mysql_result($rsnav, $i, 'mema_nombre');
							$menu=mysql_result($rsnav, $i, 'menu_nombre');
						}
						else
						{
							$mema=$finalurl;
							$menu="";
						}
						?>
						<li class="breadcrumb-item active"><?php echo $mema?></li><li class="active"><?php echo $menu?></li>
					</ol>
					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>

        <!-- Main content -->
        <section class="content">
        <?php 
			$incl = $_GET['p'];
			if(strlen($incl)>0)
			{
			include("$incl" . ".php");
			}else
		   ?>
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <footer class="main-footer">
        <div class="float-right d-none d-sm-inline-block">
          <b>Grupo ITEMU</b>
        </div>
        <strong>
      </footer>

    </div><!-- ./wrapper -->
	<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
	<script>
	  $.widget.bridge('uibutton', $.ui.button)
	</script>
	<!-- Bootstrap 4 -->
	<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Sparkline -->
	<script src="plugins/sparklines/sparkline.js"></script>
	<!-- JQVMap -->
	<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
	<script src="plugins/jqvmap/maps/jquery.vmap.world.js"></script>
	<!-- jQuery Knob Chart -->
	<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
	<!-- daterangepicker -->
	<script src="plugins/moment/moment.min.js"></script>
	<script src="plugins/daterangepicker/daterangepicker.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
	<!-- Summernote -->
	<script src="plugins/summernote/summernote-bs4.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- FastClick -->
	<script src="plugins/fastclick/fastclick.js"></script>
	<!-- AdminLTE App -->
	<script src="dist/js/adminlte.js"></script>
	<!-- Toastr -->
	<link href="jquery/toastr.css" rel="stylesheet"/>
	<script src="jquery/toastr.js"></script>

<div id=sobretodo style="z-index: 1000000;"></div>
<div id=procesando style="z-index: 1000000;"><img src=imagenes/procesando.gif></div>    
  </body>
</html>

<script>
	$("#modal_detail_task")
</script>