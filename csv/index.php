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

//error_reporting(0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Re@xion - Proceso CSV API</title>

  <!--<link href="styles.css" rel="stylesheet" type="text/css" /> <meta http-equiv="refresh" content="5"/>-->
  <?php
  $modelo->leo_ingreso_directorio_csv();
  include("comboBoxs.php");
  if (isset($_POST['fecha'])) {
    $modelo->fechaFac($_POST['fecha']);
  }

  // Definir la función de manejo de errores personalizada
  function miManejadorDeErrores($nivel, $mensaje, $archivo, $linea)
  {
    // Puedes personalizar la forma en que manejas los errores aquí
    echo "<br>Ha ocurrido un error: <font color=red>$mensaje</font> en <font color=red>$archivo</font> en la línea <font color=red>$linea</font><br>";
  }


  // Registrar el manejador de errores personalizado
  set_error_handler('miManejadorDeErrores');



  ?>
  <!--****************Codigo necesario para generar el calendario  *****************  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/x-icon" href="../icon1.ico" />

  <style>
    .flatpickr-calendar {
      z-index: 99999 !important;
    }
  </style>

  <!-- ******************************************************************************* -->
  <link href="importa.css" rel="stylesheet" type="text/css" />
  <link href="../rxn-ui.css" rel="stylesheet" type="text/css" />
<style>
     .cursor {
      color: black !important;
      text-decoration: none;
      font-size: 16px !important;
    }

    .cursor:hover {
      color: cadetblue !important;
    }
</style>
</head>

<body link="#000000" alink="#000000" vlink="#000000" style="background-color: #f8f9fa;">
  <div class="rxn-container">
    <div class="rxn-card">
      <div class="rxn-card-header">
        Lectura de CSV
      </div>
      <div class="rxn-card-body">
        <form action="procesar.php" method="post" target="visor_consola" id="form-procesar" onsubmit="return mostrarProcesando();">
          <p style="margin-top: 0;">Este proceso ejecuta las instrucciones que realizan el ingreso automático de los archivos leídos a Tango, a continuación se visualiza el listado de archivos a procesar, clic en procesar para ejecutar los procesos.</p>
          
          <div class="rxn-action-bar">
            <input class="rxn-input" style="max-width:200px;" type="text" name="fecha" id="fechaTexto" value="<?php if (isset($_POST['fecha'])) echo $_POST['fecha']; ?>" />
            <input type="button" class="rxn-btn rxn-btn-secondary" id="botonCalendario" value="..." title="Abrir calendario" />
            <input type="submit" class="rxn-btn rxn-btn-primary" id="btn-procesar" value="Procesar" name="Procesar" accesskey="P" />
          </div>
          
          <div style="max-height: 150px; overflow-y: auto; background: #fdfdfd; border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace;">
            <?php
            //echo $si;
            $modelo->muestroNombreArchivo();
            ?>
          </div>
          
          <p>Los informes Live te permiten visualizar si existe algún inconveniente con el proceso.</p>

          <a href="../index.php" title="Click para volver" align="left"><img src="../imagenes/paginaAtras.png" align="left" width="80" height="70" border="0" alt="Volver" title="Volver" /></a>
        </form>

        <div id="estadoProceso" style="display:none; margin-top:10px; padding:12px; background:#2a2a2a; color:#fff; border-left:4px solid #4caf50; border-radius:4px; font-family:Arial, sans-serif;">
            <strong>Procesando comprobantes...</strong><br>
            No cerrar ni recargar esta pantalla.
        </div>

        <div style="margin-top: 15px;">
            <iframe name="visor_consola" id="visor_consola" onload="finalizarProcesamiento()" style="width: 100%; height: 400px; border-radius: 4px; background-color: #1e1e1e; border: 1px solid #999;"></iframe>
        </div>
        <div style="text-align: right; margin-top: 5px;">
            <button type="button" class="rxn-btn rxn-btn-secondary" onclick="window.location.reload();">Actualizar listado de archivos post-proceso</button>
        </div>
        <p align="right" style="margin-bottom:0;"><a class="cursor" href="../Ayudas/MenuPrincipal.html?ayuda=ProcesarDatoss.html" target="_blank">Ayuda</a></p>
      </div>
    </div>
  </div>
  <!--****************Codigo necesario para generar el calendario  *****************  -->

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    const fp = flatpickr("#fechaTexto", {
      dateFormat: "d/m/Y",
      allowInput: true,
      clickOpens: false,
      appendTo: document.body,
      position: "auto"
    });

    document.getElementById("botonCalendario").addEventListener("click", () => {
      fp.open();
    });
  </script>

  <script>
    let procesoIniciado = false;

    function mostrarProcesando() {
      const btn = document.getElementById("btn-procesar");
      const estado = document.getElementById("estadoProceso");
      
      btn.value = "Procesando...";
      btn.style.pointerEvents = "none";
      btn.style.opacity = "0.6";
      
      estado.innerHTML = "<strong>Procesando comprobantes...</strong><br>No cerrar ni recargar esta pantalla.";
      estado.style.borderLeftColor = "#ffc107"; // Amarillo
      estado.style.display = "block";
      
      procesoIniciado = true;
      return true;
    }

    function finalizarProcesamiento() {
      if (!procesoIniciado) return; // Ignorar carga inicial vacía del iframe
      
      const btn = document.getElementById("btn-procesar");
      const estado = document.getElementById("estadoProceso");
      const iframe = document.getElementById("visor_consola");
      
      // Restaurar Botón
      btn.value = "Procesar";
      btn.style.pointerEvents = "auto";
      btn.style.opacity = "1";
      
      // Control de estado de éxito o error leyendo el contenido del iframe
      try {
        const iframeContent = iframe.contentWindow.document.body.innerText || "";
        const contenidoMin = iframeContent.toLowerCase();
        
        if (contenidoMin.includes("error") || contenidoMin.includes("fatal") || contenidoMin.includes("exception")) {
            estado.innerHTML = "<strong>Proceso finalizado con advertencias/errores</strong><br>Revise los resultados y mensajes en la consola inferior.";
            estado.style.borderLeftColor = "#dc3545"; // Rojo
        } else {
            estado.innerHTML = "<strong>Comprobantes procesados correctamente</strong><br>El proceso finalizó con éxito. Puede revisar la consola.";
            estado.style.borderLeftColor = "#4caf50"; // Verde
        }
      } catch (e) {
        // Fallback seguro por si bloquea CORS o no hay body (no debería pasar siendo mismo server)
        estado.innerHTML = "<strong>Comprobantes procesados</strong><br>El proceso remitió respuesta. Revise la consola inferior.";
        estado.style.borderLeftColor = "#4caf50"; // Verde
      }
      
      procesoIniciado = false;
    }
  </script>

<!-- ******************************************************************************* -->

</body>

</html>