<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
        integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>

<body>
    <div class="container-fluid">

        <h3 class="text-center"> <span class="badge badge-secondary">Subir facturas a PCcomponentes</span></h3>

        <div class="row">
            <div class="col-sm-6">
                <div class="card text-center">
                    <div class="card-header">
                        <h4 class="card-title">Subir varias facturas</h4>
                    </div>
                    <div class="card-body">
                        <p>(El nombre de la factura tiene que contener el nombre del pedido. Ejemplo
                            Factura_00000000_00000-A.pdf)</p>
                        <form method="post" action="" enctype="multipart/form-data">
                            <label for="archivo"><strong>Selecionar facturas</strong></label><br />
                            <small id="fileHelp" class="form-text text-muted" class="form-control my-1">Archivos
                                permitidos
                                (.pdf)</small>
                            <input name="upload[]" type="file" multiple="multiple" class="form-control my-2" />
                            <!-- <input type="file" class="form-control-file" id="archvio" aria-describedby="fileHelp" name="archivo"> -->

                            <!-- <button type="submit" class="btn btn-primary" name="boton">Subir archivo al servidor</button> -->
                            <button type="submit" class="btn btn-success" name="mandar" class="form-control my-3">Subir
                                Facturas</button>
                            <button type="submit" class="btn btn-danger" name="cancelar">Cancelar</button>
                        </form>


                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card text-center">
                    <div class="card-header">
                        <h4 class="card-title">Subir una factura</h4>

                    </div>
                    <div class="card-body">

                        <form action="" id="formulario" method="POST" enctype="multipart/form-data">
                            <input type="text" name="numPedido" id="numPedido" placeholder="Numero pedido"
                                class="form-control my-3">
                            <input type="file" name="numFactura" id="numFactura" class="form-control my-3">

                            <button class="btn btn-success" type="submit" id="enviar" name="enviar">Enviar
                                Factura</button>
                            <!-- <button class="btn btn-primary" type="submit" name="agregar">Agregar para enviar</button> -->
                            <button type="submit" class="btn btn-danger" name="cancelar">Cancelar</button>
                        </form>


                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

<?php

//funcion para recorrer el array bidirecciona y separar en array individidual
function reArrayFiles($file_post)
{
    $isMulti    = is_array($file_post['name']); //comprueba si es array valido
    $file_count    = $isMulti ? count($file_post['name']) : 1;
    $file_keys    = array_keys($file_post);

    $file_ary    = [];    //la matriz resultante
    for ($i = 0; $i < $file_count; $i++)
        foreach ($file_keys as $key)
            if ($isMulti)
                $file_ary[$i][$key] = $file_post[$key][$i];
            else
                $file_ary[$i][$key]    = $file_post[$key];

    return $file_ary;
}

if (isset($_POST['mandar'])) {
    //var_dump ($file_ary);
    $file_ary = reArrayFiles($_FILES['upload']);

    if (!empty($file_ary)) {
        foreach ($file_ary as $file) {
            if (($file['name']) != '') {
                if (($file['type']) == 'application/pdf') {
                    // print 'File Name: ' . $file['name'];
                    // print 'File Type: ' . $file['type'];
                    // print 'File Size: ' . $file['size'];                    
                    //saco el numero del pedido desde el nombre del fichero
                    $numPedido = substr($file['name'], (strrpos($file['name'], '_') + 1), -4);
                    // echo '<br/>';
                    // echo 'Nº Pedido ' . $numPedido . ' ';
                    // echo '<br/>';
                    sendPost($numPedido, $file);
                }else{
                    print 'Fichero: ' . $file['name'];
                    ?>
                    <div class="alert alert-danger" role="alert">
                        <p>Tipo de archivo no permitido.</p>
                    </div>
                    <?php
                    header("Refresh:5");
                }
            } else {
                ?>

                <div class="alert alert-danger" role="alert">
                    <p>Se necesita al menos una factura.</p>
                </div>
                <?php
                header("Refresh:4");
            }
        }
    }
}






//funciona para usar el API
function sendPost($ped, $fac)
{
    // $ped = htmlspecialchars ($_POST['numPedido']);    
    // $fac = $_FILES['numFactura']; 
    if (empty($fac)) {
        echo 'sin factura';
    }

    //echo '</br>----'.$ped.' '.$fac.'-----<br/>';
    //var_dump($fac);

    $result = "";
    $curl = curl_init();

    $orders = str_replace(" ", "", '<body> <order_documents> <order_document> <file_name>' . $fac["name"] . '</file_name> <type_code>CUSTOMER_INVOICE</type_code> </order_document></order_documents></body>');
    $curl_file = new CURLFile($fac["tmp_name"], $fac["type"], $fac["name"]);

    //Genera las Variables POST para enviar la Peticion CURL
    $file = array('files' => $curl_file, 'order_documents' => $orders);


    curl_setopt_array($curl, array(
        //CURLOPT_SSL_VERIFYPEER => false,
        //CURLOPT_SSL_VERIFYHOST => false,
        //CURLOPT_HTTPPROXYTUNNEL	=> TRUE,
        CURLOPT_URL => "https://pccomponentes-prod.mirakl.net/api/orders/" . $ped . "/documents",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $file,
        // CURLOPT_POSTFIELDS => array('files'=> new CURLFILE($fac),'order_documents' => '<body> <order_documents> <order_document> <file_name>'.$fac.'</file_name> <type_code>CUSTOMER_INVOICE</type_code> </order_document></order_documents></body>'),
        CURLOPT_HTTPHEADER => array(
            "Authorization: 7a154367-e7a1-44fb-8936-87328676f474",
            "Accept: application/json",
            "Content-Type: multipart/form-data"
        ),
    ));

    //ejecuto sesion
    $curlexce = curl_exec($curl);

    // info
    $statusinfo = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    //cierro sesion
    curl_close($curl);

    //Muestra el codigo de informacion de la subida
    //print_r($statusinfo);
    switch ($statusinfo) {
        case 404:
            //$result = "Fallo en la subida de factura";
            //echo $result;
            ?>
<div class="alert alert-danger" role="alert">
    <p>Fallo en la subida de factura</p>
</div>
<?php
            header("Refresh:5");
            break;
        case 400:
            //$result = "Pedido o factura incorrecta Factura incorrecta";
            //echo $result;
        ?>
<div class="alert alert-danger" role="alert">
    <p>Pedido o factura incorrecta</p>
</div>
<?php
            header("Refresh:5");  
            break;
        case 200:
            //$result = "Datos subidos correctamente";
            //echo $result;
        ?>
<div class="alert alert-primary" role="alert">
    <p>Datos subidos correctamente</p>
</div>
<?php
            header("Refresh:6");
            break;
        default:
            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);
                $result = "Error ({$errno}): {$error_message}";
            } else
                $result = "Datos subidos correctamente";
            break;
    }

    return $result;
    //print_r($result);

}


//accion del boton enviar 1 factura
if (isset($_POST['enviar'])) {
    $ped = htmlspecialchars($_POST['numPedido']);
    $fac = $_FILES['numFactura'];

        
    if ((empty($fac)) or $ped == '') {
        ?>
<div class="alert alert-danger" role="alert">
    <p>Se necesita el numero de pedido y la factura.</p>
</div>
<?php
        header("Refresh:4");
        //echo json_encode('Igresar pedido y factura');
    } else {
        //llamamos a la funcion pasamos parametros
        //echo '<br>Datos ingresados Pedido:'.$ped.' Factura:'.$fac.'<br>';
        if (($fac['type']) == 'application/pdf') {
            sendPost($ped, $fac);

        }else{
            print 'Fichero: ' . $fac['name'];
            ?>
            <div class="alert alert-danger" role="alert">
                <p>Tipo de archivo no permitido.</p>
            </div>
            <?php
            header("Refresh:5");
    
        }        
    }
}

//para el boton cancelar
if (isset($_POST['cancelar'])) {
    header("Refresh:0");
}

?>