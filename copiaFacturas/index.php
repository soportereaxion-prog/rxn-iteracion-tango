<?php
require_once __DIR__ . '/../auth/guard.php';
/*
  --------------------------------------------------------------------------------------------
  |                          Ch4rl1X Desarrollo de aplicaciones web y móviles                |
  |                                                                                          |
  |                                  correo: charly@charlesweb.com.ar                        |
  |                                     web: www.charlesweb.com.ar                           |
  |                                                                                          |
  | Este material es apto para ser difundido y compartido. Utilizalo bajo tu responsabilidad.|
  --------------------------------------------------------------------------------------------
 * 
 */
?>
<?php


include('controlador.php');


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Re@xion - Copia factura</title>
  <link href="styles.css" rel="stylesheet" type="text/css" />
  <link rel="icon" type="image/x-icon" href="../icon1.ico" />

  <?php
  // Definir la función de manejo de errores personalizada
  function miManejadorDeErrores($nivel, $mensaje, $archivo, $linea)
  {
    // Puedes personalizar la forma en que manejas los errores aquí
    echo "<br>Ha ocurrido un error: <font color=red>$mensaje</font> en <font color=red>$archivo</font> en la línea <font color=red>$linea</font><br>";
  }


  // Registrar el manejador de errores personalizado
  set_error_handler('miManejadorDeErrores');
  ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <link href="select2/dist/css/select2.css" rel="stylesheet" />
  <script src="select2/dist/js/select2.js"></script>
  <script>
    $(document).ready(function() {
      $("#e1").select2();
    });
    $(document).ready(function() {
      $("#e2").select2();
    });
    $(document).ready(function() {
      $("#e3").select2();
    });
    $(document).ready(function() {
      $("#e4").select2();
    });
  </script>


  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <style>
    .bloque-formulario {
      border: 1px solid #000;
      padding: 30px 20px 20px 20px;
      margin: 30px 0;
      border-radius: 5px;
      position: relative;
    }

    .cursor {
      color: black;
      text-decoration: none;
    }

    .cursor:hover {
      color: cadetblue;
    }


    .linea-con-texto {
      all: unset;
      /* Resetea todos los estilos heredados */
      font-size: 14px;
      font-weight: normal;
      position: absolute;
      top: -10px;
      left: 20px;
      background-color: white;
      padding: 0 10px;
    }


    .selector {

      width: 180px;
    }
  </style>
  <!--****************Codigo necesario para generar el calendario  *****************  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="icon" type="image/x-icon" href="../icon1.ico" />

  <style>
    .flatpickr-calendar {
      z-index: 99999 !important;
    }

    .texto {
      text-align: center;
      width: 100px;
    }

    .botonFecha {
      width: 50px;
      height: 25px;
    }
  </style>

  <!-- ******************************************************************************* -->


</head>

<body link="#000000" alink="#000000" vlink="#000000">
  <div class="bloque-formulario">
    <div class="linea-con-texto">Lectura de Facturas</div>

    <form class="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      <table border="1">
        <tr>
          <td colspan="6" align="center" style="padding: 10px; font-size: 17px; background-color: #f8f9fa;">
            Parámetros para la copia de Facturas
          </td>
        </tr>

        <!-- Fila de los titulos -->
        <tr>
          <th>Empresa origen</th>
             <!-- se muestra el titulo de fecha  solo si se dan las condiciones  -->
          <?php if (!empty($_POST['selectEmpresa'])) { ?>
            <th>Fecha desde</th>
            <th></th> <!-- Boton calendario desde -->
            <th>Fecha hasta</th>
            <th></th> <!-- Boton calendario hasta -->
          <?php } ?>
            <!-- se muestra el titulo de Factura a copiar  solo si se dan las condiciones  -->
          <?php if (!empty($_POST['selectEmpresa']) && !empty($_POST['fech1']) && !empty($_POST['fech2'])) { ?>
            <th>Factura a copiar</th>
          <?php } ?>
        </tr>

        <!-- Fila de los inputs -->
        <tr>
          <td align="center"><?php $modelo->seleccionoEmpresa(); ?></td>
            <!-- muestra los inputs de fecha si empresa no esta vacia -->
          <?php if (!empty($_POST['selectEmpresa'])) { ?>
            <td>
              <input class="texto" type="text" name="fech1" id="fechaTexto1"
                value="<?php echo isset($_POST['fech1']) ? $_POST['fech1'] : ''; ?>" />
            </td>
            <td>
              <input class="botonFecha" type="button" id="botonCalendario1" value="..." title="Abrir calendario" />
            </td>
            <td>
              <input class="texto" type="text" name="fech2" id="fechaTexto2"
                value="<?php echo isset($_POST['fech2']) ? $_POST['fech2'] : ''; ?>" />
            </td>
            <td>
              <input class="botonFecha" type="button" id="botonCalendario2" value="..." title="Abrir calendario" />
            </td>
          <?php } ?>
               <!-- se muestra el selector de facturas solo si se dan las condiciones para eso -->
          <?php if (!empty($_POST['selectEmpresa']) && !empty($_POST['fech1']) && !empty($_POST['fech2'])) { ?>
            <td align="center"><?php $modelo->seleccionoFactura(); ?></td>
          <?php } ?>
        </tr>
        <tr>
          <td colspan="6" align="center">
            <?php
            //Con la funcion empty verifico si la variable $_COOKIE no existe o esta vacia y lo mismo con $_POST['selectEmpresa']
            if (empty($_COOKIE['ultima_empresa']) && empty($_POST['selectEmpresa'])) {
              echo '<div class="alert alert-warning" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>Seleccione una empresa y haga click en siguiente. 
                  </div>';
            } else {
              if (isset($_POST['Copiar'])) {
                if (($_POST['selectEmpresa'] != $_COOKIE['ultima_empresa']) || empty($_POST['fech1']) && empty($_POST['fech2'])) {
                  echo '<div class="alert alert-warning" role="alert">
                      <i class="bi bi-exclamation-triangle me-2"></i>Seleccione el rango de fecha de las facturas (desde y hasta).
                      </div>';
                } else if (empty($_POST['selectFactura']) || ($_POST['selectEmpresa'] != $_COOKIE['ultima_empresa'])) {

                  echo '<div class="alert alert-warning" role="alert">
                      <i class="bi bi-exclamation-triangle me-2"></i>Seleccione una Factura y haga click en Siguiente.
                      </div>';
                } else if ($modelo->existeFactura($_POST['selectFactura'])) {

                  echo '<div class="alert alert-danger" role="alert">
                      <i class="bi bi-exclamation-circle me-2"></i>Ya existe esa factura en la empresa destino.
                      </div>';
                } else {

                  echo '<div class="alert alert-info" role="alert">
                      <i class="bi bi-check-circle me-2"></i>La factura fue copiada con éxito.
                      </div>';

                  $modelo->ingreso_factura($_POST['selectEmpresa'], $_POST['selectFactura']);
                }
              }
            }
            ?>
          </td>
        </tr>
      </table>



      <tr>
        <input type="submit" value="Siguiente" name="Copiar">
    </form>

    <br>
    <tr>
      <td scope="col"><a href="index.php"><img src="../imagenes/Facturas.png" width="80" height="80" border="0" alt="Rxn-NomenclaturaAClientes" /></a></td>
      <th scope="col" colspan="3">
      <th scope="col" colspan="3">Re@xion - COPIA DE FACTURAS</th>
      </th>
    </tr>
    <div align="right">
      <a href="https://reaxion.com.ar/" style="color: black; text-decoration: none;" target="_blank">
        Re@xion ---> desarrollo de soluciones para Tango <---
          </a>
          <br>
          <br>
          <a class="cursor" href="../Ayudas/MenuPrincipal.html?ayuda=CopiarFacturas.html" target="_blank">
            Ayuda
          </a>
          <a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver" /></a>

    </div>

    <br />
  </div>
  <!--****************Codigo necesario para generar el calendario  *****************  -->

<!-- biblioteca FLATPICKR para agreagr calendarios personalizados -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <script>
    // Inicializar el primer calendario
    const fp1 = flatpickr("#fechaTexto1", {
      dateFormat: "d/m/Y",   // Formato: día/mes/año
      allowInput: true,       // Permite escribir la fecha manualmente
      clickOpens: false,  // El calendario no se abre automáticamente al hacer clic en el input
      appendTo: document.body,   // Dónde se mostrará el calendario en el DOM
      position: "auto",  // Flatpickr decide la mejor posición del calendario automáticamente
      onChange: function(selectedDates) {
        // Actualizar minDate del segundo calendario(hace posible que en el input de la fecha2 no se elija una fecha anterior a la elegida en el primer input de fecha)
        fp2.set("minDate", selectedDates[0]);
      }
    });

    document.getElementById("botonCalendario1").addEventListener("click", () => { // Botón para abrir el calendario del primer campo
      fp1.open();
    });

    // Inicializar el segundo calendario
    const fp2 = flatpickr("#fechaTexto2", {
      dateFormat: "d/m/Y",
      allowInput: true,
      clickOpens: false,
      appendTo: document.body,
      position: "auto"
    });

    document.getElementById("botonCalendario2").addEventListener("click", () => {  // Botón para abrir el calendario del segundo campo
      fp2.open();
    });
  </script>


  <!-- ******************************************************************************* -->
</body>

</html>