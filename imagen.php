<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once 'clases/respuestas.class.php';
require_once 'clases/imagen.class.php';

$_respuestas = new respuestas;
$_imagenes = new imagen;
$ruta = 'C:/xampp/htdocs/PROYECTO-BD2/imagenes/';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //recibimos los datos enviados

    if (isset($_GET['tipo'])) {
        $ruta .= "{$_GET['tipo']}/"; // Ruta donde se guardarán las imágenes

        if (!is_dir($ruta))
            mkdir($ruta);


        $archivo_subido = $ruta . basename($_FILES['file']['name']);
        if(file_exists($archivo_subido)){
    
            $respuesta["result"] = array(

                "RUTA" => "http://localhost/PROYECTO-BD2/imagenes/{$_GET['tipo']}/" . basename($_FILES['file']['name']),
                "ID" =>  $_imagenes->obtenerIdByRuta("http://localhost/PROYECTO-BD2/imagenes/{$_GET['tipo']}/" . basename($_FILES['file']['name']))
            );
            http_response_code(200);
            echo json_encode($respuesta);
            exit;
            
        }

        header('Content-Type: application/json');
        if (move_uploaded_file($_FILES['file']['tmp_name'], $archivo_subido)) {

            $rutaRetornar = "http://localhost/PROYECTO-BD2/imagenes/{$_GET['tipo']}/" . basename($_FILES['file']['name']);
            $guardadoBD = $_imagenes->post($rutaRetornar);
            if ($guardadoBD) {
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "RUTA" => $rutaRetornar,
                    "ID" => $guardadoBD['ID']
                );
                http_response_code(200);
            }
            else{
                $respuesta = $_respuestas->error_500("No se pudo guardar en la base de datos");
                http_response_code($respuesta['result']['error_id']);
            }



            
        } else {
            $respuesta = $_respuestas->error_500();
            http_response_code($respuesta['result']['error_id']);
        }

        echo json_encode($respuesta);
    }
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    // $headers = getallheaders();
    // if(isset($headers["token"]) && isset($headers["pacienteId"])){
    //     //recibimos los datos enviados por el header
    //     $send = [
    //         "token" => $headers["token"],
    //         "pacienteId" =>$headers["pacienteId"]
    //     ];
    //     $postBody = json_encode($send);
    // }else{
    //     //recibimos los datos enviados

    //     $postBody = array(
    //         "cedula" => $_GET['id']
    //     );

    // }
    // http_response_code(200);
    // //enviamos datos al manejador
    // $datosArray = $_clientes->delete($postBody);
    // //delvovemos una respuesta 
    // header('Content-Type: application/json');
    // exit;
    // if(isset($datosArray["result"]["error_id"])){
    //     $responseCode = $datosArray["result"]["error_id"];
    //     http_response_code($responseCode);
    // }else{
    //     http_response_code(200);
    // }
    // echo json_encode($datosArray);


} else {

    print "OUUUU";

    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
