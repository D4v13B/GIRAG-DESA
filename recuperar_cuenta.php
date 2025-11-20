<?php 

include "conexion.php";
include "funciones.php";

switch($_SERVER["REQUEST_METHOD"]){
   case "POST":
      $error = "";
      $token = $_GET["token"];
      $password_1 = $_POST["password_1"];
      $password_2 = $_POST["password_2"];
      
      $res = mysql_fetch_assoc(mysql_query("SELECT usua_id, usua_administrador_caso, usua_nombre FROM usuarios WHERE usua_token = '$token'"));
      
      if(!empty($res['usua_id']) and $password_1 == $password_2){//Verificamos si las contraseñas son iguales
         //Hacemos la actualizacion de la contraseña
         $usua_id = $res["usua_id"];
         $usuario = $res["usua_nombre"];
         $administrador_casos = $res["usua_administrador_caso"];
         mysql_query("UPDATE usuarios SET usua_password = '$password_2', usua_verificado = 1, usua_token = NULL WHERE usua_id = $usua_id");

         if(!mysql_error()){
            session_start();
            $_SESSION['login_user'] = $usua_id;
            $_SESSION['administrador_caso'] = $administrador_casos;
            mysql_query("update usuarios set usua_ultima=now() where usua_id=$usua_id");
            // login_bitacora(7, 48, $usuario); //7-operaciones, 48-Overseas
            header("location: index.php?p=home");
         }
      }else{
         $error .= "EL USUARIO NO EXISTE O LAS CONTRASEÑAS NO SON IGUALES";
      }
      break;
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Recover Password</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <img src="https://giraglogic.girag.aero/img/Girag.png" alt="girag" width="200px">
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Esto es un sistema de seguridad para confirmar y cambiar tu contraseña</p>

      <form action="<?php echo $_SERVER["PHP_SELF"]?>?token=<?php echo $_GET["token"]?>" method="post">
        <div class="input-group mb-3">
          <input name="password_1" type="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input name="password_2" type="password" class="form-control" placeholder="Confirm Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Cambiar contraseña</button>
          </div>
          <?php if(!empty($error)):?>
            <p class="text-danger text-center px-3"><?php echo $error?></p>
          <?php endif?>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
</body>
</html>
