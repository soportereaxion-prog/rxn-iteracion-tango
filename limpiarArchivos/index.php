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
*/

?>
<!doctype html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link href="estilo.css" rel="stylesheet" type="text/css" />
  <title>Re@xion - limpieza de archivos</title>
  <link rel="icon" type="image/x-icon" href="../icon1.ico" />

  <?php
  //include("comboBoxs.php");
  ?>
  <script src="custom.js"></script>
  <SCRIPT LANGUAGE="JavaScript">
    function justNumbers(e) {
      var keynum = window.event ? window.event.keyCode : e.which;
      if ((keynum == 8) || (keynum == 46))
        return true;

      return /\d/.test(String.fromCharCode(keynum));
    }
  </SCRIPT>
  <style>
    input:-moz-read-only {
      /* For Firefox */
      background-color: #E6E6E6;
    }

    input:read-only {
      background-color: #E6E6E6;
    }

    textarea:-moz-read-only {
      /* For Firefox */
      background-color: #E6E6E6;
    }

    textarea:read-only {
      background-color: #E6E6E6;
    }

    #e1,
    #e2 {
      width: 250px;
      height: 20;
    }
  </style>
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
  <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />
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

    .redondear {
      border-radius: 5px;
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

    .cursor {
      color: black;
      text-decoration: none;
    }

    .cursor:hover {
      color: cadetblue;
    }
  </style>


  <style>
    /* ************estilo para ventana************** */
    /* Estilo del fondo oscuro */
    #modalFondo {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    /* Estilo de la ventana */
    #modalVentana {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 24px;
      width: 320px;
      text-align: center;
      position: absolute;
      font-family: Arial, sans-serif;
      color: #333;
      top: 30%;
      left: 50%;
      transform: translate(-50%, -30%);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #modalVentana button {
      margin: 10px 5px;
    }
  </style>

  <script>
    function mostrarModal(accion) {
      document.getElementById("accionElegida").value = accion;
      document.getElementById("modalFondo").style.display = "block";
    }

    function cerrarModal() {
      document.getElementById("modalFondo").style.display = "none";
    }

    function enviarFormulario() {
      const accion = document.getElementById("accionElegida").value;
      const form = document.getElementById("formBorrar");

      const input = document.createElement("input");
      input.type = "hidden";
      input.name = accion;
      input.value = "1";
      form.appendChild(input);

      form.submit();
    }
  </script>




</head>
<!--  onload="nobackbutton();" -->

<body link="#000000" alink="#000000" vlink="#000000" style="background-color: #f8f9fa;">
  <?php
  require_once("controlador.php");
  ?>
  
  <div class="rxn-container">
    <div class="rxn-card">
        <div class="rxn-card-header">
            Limpieza de Archivos
        </div>
        <div class="rxn-card-body">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <p style="margin-top: 0;">Seleccione la opción deseada para eliminar manualmente los comprobantes pendientes de procesar.</p>

                <div class="rxn-action-bar">
                    <button type="button" class="rxn-btn rxn-btn-primary" onclick="mostrarModal('limpiarPendientesReproceso')">Limpiar pendientes de reproceso</button>
                    <button type="button" class="rxn-btn rxn-btn-danger" onclick="mostrarModal('limpiarTodo')">Borrar todos los pendientes</button>
                </div>
            </form>

            <div style="margin-top: 15px; font-weight: 600; color: #198754;">
                <?php 
                if (isset($_POST['limpiarTodo'])) {
                    $modelo->limpiarTodo($modelo->traerBase());
                }
                if (isset($_POST['limpiarPendientesReproceso'])) {
                    $modelo->limpiarPendientesDeReproceso($modelo->traerBase());
                }
                ?>
            </div>
            
            <br>
            <a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver" /></a>
            <p align="right" style="margin-bottom:0;"><a class="cursor" href="../Ayudas/MenuPrincipal.html?ayuda=LimpiarArchivos.html" target="_blank">Ayuda</a></p>
        </div>
    </div>
  </div>

  <!-- Modal -->
  <!-- Modal -->
  <form id="formBorrar" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" id="accionElegida" name="accionElegida" value="">
  </form>

  <div id="modalFondo">
    <div id="modalVentana">
      <p style="margin-bottom: 20px;">¿Estás seguro de que deseas borrar estos registros?</p>
      <button type="button" class="rxn-btn rxn-btn-danger" onclick="enviarFormulario()">Sí, borrar</button>
      <button type="button" class="rxn-btn rxn-btn-secondary" onclick="cerrarModal()">Cancelar</button>
    </div>
  </div>

</body>

</html>