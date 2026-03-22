<?php

/* session_start(); */
//Se habre la sesion con la intencion de usar la variable.

//En el if se pregunta si la variable $_post viene con valor,de ser asi,se pasa el valor a la variable session
//con la intencion de poder colocarlo en el option del select.
/* if (isset($_POST['selectEmpresa'])) {
        $_SESSION['ultima_empresa'] = $_POST['selectEmpresa'];
} */

class vista

{

        /*Vista temporal para controlar si se muestra el nombre de archivo*/

        public function muestroNombreArchivo()
        {
                $this->leoArchivosBd();
                foreach ($this->nombre_archivo as $archivo) {
                        echo $archivo['NOMBRE_ARCHIVO'];
                }
        }

        /*Ejecuto la selección del talonario*/
        public function seleccionoFactura()
        {
                //echo 'Estoy bien parado';
                //$matriz_nomenclatura = ;
?>
                <select name="selectFactura" id="e1">
                        <option value=""></option>
                        <?php
                        /*Carga el array al ultimo metodo llamado*/
                        $this->selec_factura();
                        //print_r($this->talonario);
                        /*método que busca el talonario configurado*/
                        //$this->devuelvoValorTalonSelec();
                        foreach ($this->factura as $factu) {
                                /*Si no viene el art el genero un valor para que no marque error*/
                                //if(!isset($_POST['selTalonario'])){$_POST['selTalonario'] = 1;}
                                //OR $talonario['NombreBD'] == $this->talon_ped
                                if ($factu['N_COMP'] == $_POST['selectTalonario']) {
                        ?>
                                        <option value="<?php echo $_POST['N_COMP']; ?>" selected="selected"><?php echo $factu['N_COMP']; ?></option>
                                <?php
                                } else {
                                ?>
                                        <option value="<?php echo $factu['N_COMP']; ?>"><?php echo $factu['N_COMP']; ?></option>
                        <?php
                                }
                        }
                        ?>
                </select>
        <?php
        }



        /*Ejecuto la selección del talonario*/
        public function seleccionoEmpresa()
        {
                //echo 'Estoy bien parado';
                //$matriz_nomenclatura = ;
        ?>
                <select name="selectEmpresa" id="e2">
                        <!-- aca se verifica si la variable sesion $_SESSION['ultima_empresa'] tiene un valor.Si es asi 
                         se coloca en el atributo value de la opcion y también se muestra como texto dentro de la
                        opción.Metodo alternativo al sp-->
                        <option value="<?php echo !empty($_SESSION['ultima_empresa']) ? $_SESSION['ultima_empresa'] : ''; ?>">
                                <?php echo !empty($_SESSION['ultima_empresa']) ? $_SESSION['ultima_empresa'] : ''; ?>
                        </option>
                        <?php
                        //Carga el array al ultimo metodo llamado
                        $this->selec_empresa();
                        //print_r($this->talonario);
                        //método que busca el talonario configurado
                        //$this->devuelvoValorTalonSelec();
                        foreach ($this->empresa as $empre) {
                                //Si no viene el art el genero un valor para que no marque error
                                //if(!isset($_POST['selectEmpresa'])){$_POST['selectEmpresa'] = 1;}
                                //OR $talonario['NombreBD'] == $this->talon_ped
                                if ($empre['NombreBD'] == $_POST['selectEmpresa']) {
                        ?>
                                        <option value="<?php echo $empre['NombreBD']; ?>" selected="selected"><?php echo $empre['NombreBD']; ?></option>
                                <?php
                                } else {
                                ?>
                                        <option value="<?php echo $empre['NombreBD']; ?>"><?php echo $empre['NombreBD']; ?></option>
                        <?php
                                }
                        }
                        ?>
                </select>
<?php
        }



}
?>