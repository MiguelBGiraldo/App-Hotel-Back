<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once 'clases/respuestas.class.php';
require_once 'clases/hotel.class.php';

$_respuestas = new respuestas;
$hotel = new Hotel;

    if ($_SERVER['REQUEST_METHOD'] == "GET") {

        if($_GET['peticion'] == 'listarClientesReserva');

        $postBody = file_get_contents("php://input");

        print_r ($postBody['reserva']);


        header('Content-Type: application/json');

    }