<?php
session_start();
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
include('conexion.php');
include('funciones.php');
include('login_bitacora.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // username and password sent from Form
  $usuario = addslashes($_POST['i_usuario']);
  $pass = addslashes($_POST['i_pass']);
  $sql = "SELECT usua_id, usua_administrador_caso, usua_verificado, usua_token, usua_nombre_completo  FROM usuarios WHERE usua_nombre='$usuario' and usua_password='$pass' and usua_activo=1";
  //echo $sql;
  $result = mysql_query($sql);
  $num_us = mysql_num_rows($result);
  $i = 0;
  //echo $num_us;
  if ($num_us == 1) {
    //session_register("login_user");
    session_start();
    $id_us = mysql_result($result, $i, 'usua_id');
    $administrador_caso = mysql_result($result, $i, 'usua_administrador_caso');
    $usua_verificado = mysql_result($result, $i, 'usua_verificado');
    $nombre_us = mysql_result($result, $i, 'usua_nombre_completo');

    if ($usua_verificado == 0) {
      $usua_token = mysql_result($result, $i, "usua_token");
      header("Location: recuperar_cuenta.php?token=$usua_token");
      exit;
    }

    $_SESSION['login_user'] = $id_us;
    $_SESSION['administrador_caso'] = $administrador_caso;
    $_SESSION['usua_nombre_completo'] = $nombre_us;
    //guardo la �ltima vez que se loguio
    mysql_query("update usuarios set usua_ultima=now() where usua_id=$id_us");
    login_bitacora(7, 48, $usuario); //7-operaciones, 48-Overseas
    header("location: index.php?p=home");
  } else {
    $error = "Error en login";
  }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
  <link rel="stylesheet" href="login_style.css?nch=2co<?php echo date('Ymd'); ?>" type="text/css" media="screen" />
  <link href="jquery/jquery-ui.css" rel="stylesheet" type="text/css" />
  <script src="jquery/jquery-1.7.min.js"></script>
  <script src="jquery/jquery-ui.min.js"></script>
  <script type="text/javascript" src="jquery/jquery.numeric.js"></script>
  <script src="modal.js"></script>
  <script src="funciones.js"></script>
</head>

<body style="background-image:url('https://giraglogic.girag.aero/imagenes/bg.png?nch=2co<?php echo date('Ymd'); ?>'); background-size: 100%;-webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;">
  <table width="100%" height="100%">
    <tr>
      <td valign="middle" align="center">
        <form name="Login" id="Login" method="post" action="">
          <div id="contenedor">
            <div id="contenedor_logo"></div>
            <style>
              #contenedor_logo img {
                width: 200px;
              }
            </style>
            <div id="contenedor_Login">
              <span class="LabelText">Nombre de usuario:</span><br>
              <input type="text" name="i_usuario" id="i_usuario" class="TextLogin"><br>
              <!-- onFocus="LimpiarValor(this);" onBlur="ResetValor(this, 'Usuario');" value="Usuario"-->
              <span class="LabelText">Contrase&ntilde;a:</span><br>
              <!-- onFocus="replaceText(this);" onBlur="ResetValor(this, 'Contrase&ntilde;a');" value="Contrase�a" -->
              <input type="password" name="i_pass" id="i_pass" class="TextLogin"><br>
              <span></span>
              <table width="200px" border="0" style="margin-top:10px;float:right">
                <tr>
                  <td align="right">
                    <input type="submit" name="Entrar" value="Entrar" class="Buttom">
                  </td>
                </tr>
                <?php if (isset($_GET['err']) && $_GET['err'] != '') { ?>
                  <tr>
                    <td align="center">
                      <div class="error">
                        <?php
                        if ($error == "01") {
                          echo "Error en la autenticaci&oacute;n";
                        } elseif ($error == "02") {
                          echo "Error, los campos estan vacios";
                        } else {
                          echo "Error, reporte al administrador";
                        }
                        ?>
                      </div>
            </div>
      </td>
    </tr>
  <?php } ?>
  </table>
  </div>
  </form>
  <br><br>
  <div id="Pie">
    <?php include("footer.php"); ?>
  </div>
  </td>
  </tr>
  </table>
</body>

</html>