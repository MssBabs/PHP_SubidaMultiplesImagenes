<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ejer_SubidaArchivos_BVN</title>
    </head>


    <?php 
        /* VARIABLES */
        $directorioSubida;
        $codigoDeErrores=[
            0=>'UPLOAD_ERR_OK, Subida correcta',
            1=>'UPLOAD_ERR_NO_FILE, No se seleccionó ningún archivo para ser subido',
            2=>'ERROR, El numero de imegenes seleccionadas supera el numero maximo posible',
            3=>'ERROR, La imagen supera los 200Kb',
            4=>'ERROR, Las imagenes superan los 300Kb',
            5=>'ERROR, Nombre de la imagen ya existente',
            6=>'ERROR, Tipo de imagen no valido',
        ];
        $errorNumMinImgs= false;
        $errorNumMaxImgs= false;
        $errorNombImg= false;
        $errorTipeImg= false;
        $errorSizeImg= false;
        $errorSizeSumImg= false;
        $mnj= "";


        $nombUsu= obtenerNombUsu();

        $mnj=infoImg($codigoDeErrores);



        /* FUNCIONES */
        function obtenerNombUsu(){
            $nombUsu="";
            if(isset($_POST["nombUsu"]) && !empty($_POST["nombUsu"])){
                $nombUsu=$_POST["nombUsu"];
            }

            return $nombUsu;
        }

        function infoImg($codigoDeErrores){
            $sizeSumImg=0;
            $mnj="";
            for($i=0; $i< count($_FILES['archivos']['name']); $i++){
                $nombImg= $_FILES['archivos']['name'][$i];
                $errorNombImg= nombImg($nombImg, $codigoDeErrores);

                $tipeImg= $_FILES['archivos']['type'][$i];
                $errorTipeImg= tipeImg($tipeImg, $codigoDeErrores);

                $sizeImg= $_FILES['archivos']['size'][$i];
                $errorSizeImg= sizeImg($sizeImg, $codigoDeErrores);

                $sizeSumImg+=$sizeImg;
                $errorSizeSumImg= sizeSumImg($sizeSumImg, $codigoDeErrores);

                $rutaTemporal = $_FILES['archivos']['tmp_name'][$i];

                $errorImg= $_FILES['archivos']['error'][$i];
                $erroresDeImagen = comprobarErrorImagen($errorImg);
                
               
                $mnj .= "- Nombre: $nombImg" . ' <br />';
                $mnj .= '- Tamaño: ' . number_format(($sizeImg / 1000), 1, ',', '.'). ' KB <br />';
                $mnj .= "- Tipo: $tipeImg" . ' <br />' ;
                $mnj .= "- Nombre archivo temporal: $rutaTemporal" . ' <br />';
                $mnj .= "- Código de estado: $errorImg" . ' <br />';

                if(!$errorNombImg && !$errorTipeImg  && !$errorSizeImg && !$errorSizeSumImg && !$erroresDeImagen){
                    $mnj .= subidaDIrectorioTemporal($rutaTemporal, $nombImg, "C:/imgusers", $mnj);
                }
               
            }
            return $mnj;
        }

        function  comprobarErrorImagen($errorImg){
            switch($errorImg){
                case 0:
                    return false;
                case 1:
                    echo "El tamaño excede el permitido por el servidor"."<br>";
                    return true;
                case 2:
                    echo "El tamaño excede el permitido por el cliente"."<br>";
                    return true;
                case 3:
                    echo "El tamaño se ha subido de forma parcial"."<br>";
                    return true;
                case 4:
                    echo "El tamaño no se ha subido"."<br>";
                    return true;
                case 6:
                    echo "No hay archivo temporal"."<br>";
                    return true;
                case 7:
                    echo "Fallo al intentar guardar el fichero en el entorno temporal"."<br>";
                    return true;
                    
            }
        }

        function numMinImgs($codigoDeErrores){
            if(count($_FILES['archivos']['name']) > 0){
                echo $codigoDeErrores[1]."<br>";
                return false;
            }else{
                return true;
            }
        }
        
        function numMaxImgs($codigoDeErrores){
            if(count($_FILES['archivos']['name']) <= 3){
                echo $codigoDeErrores[2]."<br>";
                return false;
            }else{
                return true;
            }
        }

        function sizeImg($sizeImg, $codigoDeErrores){
            if($sizeImg > 200000){
                echo $codigoDeErrores[3]."<br>"; 
                return true;
            }else{
                return false;
            }
        }

        function sizeSumImg($sizeSumImg, $codigoDeErrores){

            if($sizeSumImg > 300000){
                echo $codigoDeErrores[4]."<br>";
                return true;
            }else{
                return false;
            }
        }

        function nombImg($nombImg, $codigoDeErrores){
            $count=0;
            for($i=0; $i< count($_FILES['archivos']['name']); $i++){
                if($nombImg == ($_FILES['archivos']['name'][$i])){
                    $count++;
                }
            }
            if($count>1){
                echo $count;
                echo $codigoDeErrores[5]."<br>";
                return true;
            }
            return false;
        }

        function tipeImg($tipeImg, $codigoDeErrores){
         
            if(($tipeImg == "image/png") || ($tipeImg == "image/jpeg")){
                return false;
            }else{
                echo $codigoDeErrores[6]."<br>";
                return true;
            }
        } 

        
        function subidaDIrectorioTemporal($archivoTemporal, $nombreFichero, $directorioSubida, $mnj){
            if ( is_dir($directorioSubida) && is_writable ($directorioSubida)) { 
                //Intento mover el archivo temporal al directorio indicado
                return subidaDirectorioFinal($mnj, $archivoTemporal, $directorioSubida, $nombreFichero);
            } else {
                return 'ERROR: No es un directorio correcto o no se tiene permiso de escritura <br />'."<br>";
            }
        }

        function subidaDirectorioFinal($mnj, $temporalFichero, $directorioSubida, $nombreFichero){
            if (move_uploaded_file($temporalFichero,  $directorioSubida .'/'. $nombreFichero) == true) {
                return 'Archivo guardado en: ' . $directorioSubida .'/'. $nombreFichero . ' <br />'."<br>";
            } else {
                return 'ERROR: Archivo no guardado correctamente <br />'."<br>";
            }
        }

    ?>


    <body>
        <?= (isset($_REQUEST['nombUsu']))?" Bienvenido ".$_REQUEST['nombUsu']:""?><br><br>

        <?= $mnj; ?><br/>

	    <a href="EjerSubidaArchivos_BVN.html">Volver a la página de subida</a>
    </body>
</html>