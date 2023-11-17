<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once 'clases/respuestas.class.php';
require_once 'clases/automovil.class.php';

$_respuestas = new respuestas;
$autmovil = new Automovil;



if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if (isset($_GET["peticion"])) {



        if ($_GET["peticion"] == 'listarVehiculos') {

            $pagina = $_GET["page"];
            $listaClientes['result'] = $autmovil->listaAutmoviles($pagina);
            header("Content-Type: application/json");
            echo json_encode($listaClientes);
            http_response_code(200);
        }

        if ($_GET["peticion"] == 'listarVehiculo') {

            if (!isset($_GET['codigo'])) {
                header('Content-Type: application/json');
                $datosArray = $_respuestas->error_400();
                echo json_encode($datosArray);
            }

            $listaClientes['result'] = $autmovil->obtenerAutomovil($_GET['codigo']);
            header("Content-Type: application/json");
            echo json_encode($listaClientes);
            http_response_code(200);
        }
    }
    //else if(isset($_GET['id'])){
    //     $autmovilID = $_GET['id'];
    //     $datosCliente['result'] = $autmovil->obtenerAutomovil($autmovilID);
    //     header("Content-Type: application/json");
    //     echo json_encode($datosCliente);
    //     http_response_code(200);
    // }

} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //recibimos los datos enviados
    $postBody = file_get_contents("php://input");
    //enviamos los datos al manejador
    $datosArray = $autmovil->post($postBody);
    //delvovemos una respuesta 
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    //recibimos los datos enviados
    $postBody = file_get_contents("php://input");
    //enviamos datos al manejador
    $datosArray = $autmovil->put($postBody);
    //delvovemos una respuesta 
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    $headers = getallheaders();
    if (isset($headers["token"]) && isset($headers["autmovilId"])) {
        //recibimos los datos enviados por el header
        $send = [
            "token" => $headers["token"],
            "pacienteId" => $headers["pacienteId"]
        ];
        $postBody = json_encode($send);
    } else {
        //recibimos los datos enviados

        $postBody = array(
            "id" => $_GET['id']
        );
    }

    // print "Holaaa";
    // print_r($_POST);
    // print_r($postBody);
    http_response_code(200);

    //enviamos datos al manejador
    $datosArray = $autmovil->delete($postBody);
    //delvovemos una respuesta 
    header('Content-Type: application/json');
    exit;
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else {

    print "OUUUU";

    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
