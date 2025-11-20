<?php 

switch ($_SERVER["REQUEST_METHOD"]) {
  case "PUT":
    require "conexion.php";
    require "funciones.php";
    session_start();
    $body = "";
    $login_user = $_SESSION["login_user"];
    //Determinar las notificaciones y la cantidad de notificaciones que hay
    $sql = "SELECT COUNT(usno_id) as total_notificaciones FROM usuarios_notificaciones WHERE usua_id = $login_user";
    $total_notificaciones = mysql_fetch_assoc(mysql_query($sql))["total_notificaciones"];

    $sql = "SELECT * FROM usuarios_notificaciones WHERE usua_id = $login_user";
    $notificaciones = mysql_query($sql);

    while ($fila = mysql_fetch_assoc($notificaciones)){
      $body .= '
      <div class="dropdown-divider"></div>
      
      <span class="d-flex align-items-center">
      <a href="'.$fila["usno_ref"].'" class="dropdown-item">
      <i class="fa-solid fa-circle-exclamation"></i> '.$fila['usno_mensaje'] .'
      <span class="float-right text-muted text-sm"></span>
      </a>
      <button class="btn btn-sm text-danger" onclick="borrarNotificacion('.$fila['usno_id'].')"><i class="fa-solid fa-trash"></i></button>
      </span>
      ';
    }

    echo json_encode([
      "notificaciones_total" => $total_notificaciones,
      "notificaciones_cuerpo" => $body
    ]);

    die();
    break;
    case "DELETE":
      require "conexion.php";
      require "funciones.php";
      session_start();
      $msg = "";
      $_DELETE = json_decode(file_get_contents("php://input"), true);

      $stmt = "DELETE FROM usuarios_notificaciones WHERE usno_id = " . $_DELETE["usno_id"];
      mysql_query($stmt);

      if(!mysql_error()){
        $msg = true;
      }else{
        $msg = false;
      }

      echo json_encode(["ok" => $msg]);
      die();
      break;
}




?>


<script>
  function busqueda_general()

  {

    $.redirect("index.php?p=busqueda_general", {
      busqueda: $('#i_busqueda_general').val()
    }, "POST");

  }

  function buscarNotificaciones() {
    $.ajax({
      url: "index_navbar.php",
      method: "PUT",
      success: res => {
        res = JSON.parse(res)
        $(".notificaciones_contador").html(res.notificaciones_total)
        $("#notificaciones_cuerpo").html(res.notificaciones_cuerpo)
      }
    })
  }

  function borrarNotificacion(id){
    $.ajax({
      url: "index_navbar.php",
      contentType: "application/json",
      method: "DELETE",
      data: JSON.stringify({
        usno_id: id
      }),
      success: res => {
        res = JSON.parse(res)
        console.log(res);
        if(res.ok){
          buscarNotificaciones()
        }
      }
    })
  }

  $(document).ready(function(){
    buscarNotificaciones()
  })
</script>

<!-- Navbar -->

<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">

  <!-- Left navbar links -->

  <ul class="navbar-nav">

    <li class="nav-item">

      <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>

    </li>

    <li class="nav-item d-none d-sm-inline-block">

      <a href="index.php" class="nav-link">Inicio</a>

    </li>

    <!--

      <li class="nav-item d-none d-sm-inline-block">

        <a href="#" class="nav-link">Contact</a>

      </li>

	  -->

  </ul>



  <!-- SEARCH FORM -->

  <form class="form-inline ml-3">

    <div class="input-group input-group-sm">

      <!-- <input class="form-control form-control-navbar" type="search" placeholder="Buscar" aria-label="Search" id=i_busqueda_general> -->

      <div class="input-group-append">

        <!-- <button class="btn btn-navbar" type="button"> -->

          <!-- <a href="javascript:busqueda_general();"><i class="fas fa-search"></i></a> -->

        </button>

      </div>

    </div>

  </form>



  <!-- Right navbar links -->

  <ul class="navbar-nav ml-auto">

    <!-- Notifications Dropdown Menu -->



    <li class="nav-item dropdown">

      <a class="nav-link" data-toggle="dropdown" href="#">

        <i class="far fa-bell"></i>

        <span class="badge badge-warning navbar-badge"><span class="notificaciones_contador"></span></span>

      </a>

      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        <span class="dropdown-item dropdown-header"><span class="notificaciones_contador"></span> Notificaciones</span>

        <div id="notificaciones_cuerpo">
          
        </div>

      </div>

    </li>

    <li class="nav-item">

      <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">

        <i class="fas fa-th-large"></i>

      </a>

    </li>

  </ul>

</nav>