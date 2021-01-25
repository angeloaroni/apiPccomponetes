<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<body>
<div class="container my-5">
        <h3>Subir una factura</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="formulario" method="POST">
            <?php 
            $agenda = array();
            if(isset($_POST['agregar']))
            {
                if (isset($_POST["array"]) && $_POST["array"]) {
                    //echo $_POST["array"];
                    $agenda = unserialize(stripslashes($_POST["array"]));
                   // var_dump ($agenda);
                }
                if (!empty($_POST['numPedido']) && !empty($_POST['numFactura'])) {                    
                    $agenda [$_POST['numPedido']] = $_POST['numFactura'];                               
                }
            }    
            ?>
            <!--campo oculto para guardar los datos el array que reenviaremos a esta misma pagina cuando enviemos-->
            <input type="hidden" name="array" value='<?php echo serialize($agenda);?>'>
                       <br>
            <div class="col-md-6">
                <input type="text" name="numPedido" placeholder="Numero pedido" class="form-control my-3">            
                <input type="file" name="numFactura" class="form-control my-3">

                <button class="btn btn-success" type="submit" name="enviar">Enviar Factura</button>
                <button class="btn btn-primary" type="submit" name="agregar">Agregar para enviar</button>
            </div>
        </form>

</div>
<div class="container my-5">    
<h3>Subir varias factura</h3> <br>
    <div class="col-md-8">
        <table class="table">
        <thead>
            <tr>            
            <th scope="col">Pedido</th>
            <th scope="col">Factura</th>        
            <th scope="col"></th>        
            </tr>
        </thead>
        <tbody>
        <?php
       foreach($agenda as $key => $value)
          {   
            ?>
        <tr>  
            <td>
                <?php echo $key; ?>
            </td>
                     
            <td>
                <?php echo $value;?>
            </td>  
            <td><input type="button" id="borrar" class="btn btn-outline-danger" value="Eliminar" /></td>                  
        </tr>
        <?php
        }
       ?>
              
        </tbody>
        </table>
        <?php 
            //var_dump($agenda);
            echo '<br>';
        ?>
    <form method="POST">
        <input type="hidden" name="arrayB" value='<?php echo serialize($agenda);?>'>        
        <button type="submit" class="btn btn-success"  name="enviarFacturas">Enviar Facturas</button>
        <button type="submit" class="btn btn-danger"  name="cancelar">Cancelar</button>
    </form>   
    </div>
</div>
</body>
<script type="text/javascript">
$(document).on('click', '#borrar', function (event) {
    event.preventDefault();
    $(this).closest('tr').remove();
});
</script>
</html>


<?php
//funciona para usar el API
function sendPost($ped, $fac){   
    /*
    echo $ped .' '. $fac; 
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_VERBOSE        => 1,   
    CURLOPT_URL => "https://pccomponentes-prod.mirakl.net/api/orders/".$ped."/documents",
    CURLOPT_RETURNTRANSFER => true,   
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array('files'=> new CURLFILE($fac),'order_documents' => '<body> <order_documents> <order_document> <file_name>'.$fac.'</file_name> <type_code>CUSTOMER_INVOICE</type_code> </order_document></order_documents></body>'),
    CURLOPT_HTTPHEADER => array(
        "Authorization: 7a154367-e7a1-44fb-8936-87328676f474",
        "Accept: application/json",
        "Content-Type: multipart/form-data"
    ),
    ));
    //ejecuto sesion
    $response = curl_exec($curl);
    //manejo de errores           
        
    $res = json_decode($response);
    print_r($res).'<br>';
    
    
    if( ! $result = curl_exec($curl))
    {
        trigger_error(curl_error($curl));
    }


    //cierro sesion
    curl_close($curl);
    //echo $response .'<br>';
*/

$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://pccomponentes-prod.mirakl.net/api/orders/".$ped."/documents');
$request->setRequestMethod('POST');
$body = new http\Message\Body;
$body->addForm(array(
  'order_documents' => '<body> <order_documents> <order_document> <file_name>'.$fac.'</file_name> <type_code>CUSTOMER_INVOICE</type_code> </order_document></order_documents></body>'
), array(
    array('name' => 'files', 'type' => '<Content-type header>', 'file' => '$fac', 'data' => null)
));
$request->setBody($body);
$request->setOptions(array());

$client->enqueue($request)->send();
$response = $client->getResponse();
echo $response->getBody();






    //paso a objeto para ver codigos devueltos
    $translateObject= json_decode($response);
    var_dump ($translateObject);
    foreach($translateObject as $clave => $valor){
        if($valor == 404){
            ?>
            <div class="alert alert-danger" role="alert">
                <p>Fallo en la subida de factura</p>                                         
            </div>  
            <?php   
            header("Refresh:7");
        }
        elseif($valor == 200){
            ?>
                <div class="alert alert-primary" role="alert">
                    <p>Datos subidos correctamente</p>            
                </div>  
                <?php
           header("Refresh:7");  
           break;    
        }            
        elseif($valor == ''){
            ?>
                <div class="alert alert-primary" role="alert">
                    <p>Datos subidos correctamente</p>            
                </div>  
                <?php
           header("Refresh:7");  
           break;
        }            
    }

    return $response;
    
}

//accion del boton enviar 1 factura
if(isset($_POST['enviar'])){
    $pedido = $_POST['numPedido'];
    $factura = $_POST['numFactura'];
    if($pedido === '' || $factura=== ''){ 
        ?>  
        <div class="alert alert-danger" role="alert">
            <p>Se necesita el numero de pedido y la factura.</p>            
        </div>     
        <?php
        header("Refresh:3");
        //echo json_encode('Igresar pedido y factura');
    }else{
        //llamamos a la funcion pasamos parametros
        sendPost($pedido,$factura);
        echo json_encode('<br>Datos ingresados Pedido:'.$pedido.' Factura:'.$factura.'<br>');
    }         
}

//accion del boton enviar varias facturas
if(isset($_POST['enviarFacturas'])){        
    $agenda = array();
    $agenda = unserialize(stripslashes($_POST["arrayB"]));
    //var_dump($agenda);
    foreach($agenda as $key => $value){
        //llamamos a la funcion de la API
        sendPost($key,$value);
    }                
}

//para el boton cancelar
if(isset($_POST['cancelar'])){
    header("Refresh:0");
}	

?>
