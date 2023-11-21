<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once 'clases/respuestas.class.php';
require_once 'clases/paquetetour.class.php';

$_respuestas = new respuestas;
$_paquetes = new Paquete_Tour;

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if (isset($_GET["peticion"])) {

        if ($_GET["peticion"] == 'listarPaquetes') {

            $pagina = $_GET["page"];
            $listaPaquetes['result'] = $_paquetes->listaPaquetes($pagina);
            header("Content-Type: application/json");
            echo json_encode($listaPaquetes);
            http_response_code(200);
        }

        if ($_GET["peticion"] == 'listarPaquete') {

            if (!isset($_GET['codigo'])) {
                header('Content-Type: application/json');
                $datosArray = $_respuestas->error_400();
                echo json_encode($datosArray);
            }

            $listaPaquetes['result'] = $_paquetes->obtenerPaquete($_GET['codigo']);
            header("Content-Type: application/json");
            echo json_encode($listaPaquetes);
            http_response_code(200);
        }
    }
}
