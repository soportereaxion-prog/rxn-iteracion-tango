<?php

class vista {
    /* Vista temporal para controlar si se muestra el nombre de archivo */

    public function muestroNombreArchivo() {
        //$this->leoArchivosBd();
        if ($this->leoArchivosBd() !== null) {
            foreach ($this->nombre_archivo_ini as $archivo) {
                echo $archivo['NOMBRE_ARCHIVO'].'<br>';
            }
        }else{
            echo "No hay archivos para procesar";
        }
    }
    
    public function muestroNombreArchivoReproceso() {
        //$this->leoArchivosBd();
        if ($this->leoArchivosBdReproceso() !== null) {
            foreach ($this->nombre_archivo_ini as $archivo) {
                echo $archivo['NOMBRE_ARCHIVO'].'<br>';
            }
        }else{
            echo "No hay archivos para procesar";
        }
    }

    /* Ejecuto la selección del talonario */

    public function seleccionoTalonario() {

//$matriz_nomenclatura = ;
        ?>
        <select name="selectTalonario" id="e2">
            <option value="ninguno"></option>
            <?php
            $this->selec_talonario();
            /* método que busca el talonario configurado */
            $this->devuelvoValorTalonSelec();
            foreach ($this->talonario as $talonario) {
                /* Si no viene el art el genero un valor para que no marque error */
                if (!isset($_POST['selTalonario'])) {
                    $_POST['selTalonario'] = 1;
                }
                if ($talonario['TALONARIO'] == $_POST['selTalonario'] OR $talonario['TALONARIO'] == $this->talon_ped) {
                    ?>
                    <option value="<?php echo $_POST['selTalonario']; ?>" selected="selected"><?php echo $talonario['TALONARIO'] . ' ' . $talonario['DESCRIP']; ?></option>
                    <?php
                } else {
                    ?>
                    <option value="<?php echo $talonario['TALONARIO']; ?>"><?php echo $talonario['TALONARIO'] . ' ' . $talonario['DESCRIP']; ?></option>
                <?php
            }
        }
        ?>
        </select>
        <?php
    }

}
?>