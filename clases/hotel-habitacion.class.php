<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Hotel_Habitacion extends conexion
{

    private $tableHotel = "HOTEL";
    private $tableHabitacion = "HABITACION";
     private $tableImagen = "IMAGEN_HABITACION";
    private $idHotel = "";
    private $idHabitaciones = [];
    private $imagenes = array();
    private $estado = 0;


    public function listaHabitacionesHotel($id)
    {

        $this->idHotel = $id;

        $query = "SELECT H.IDHABITACION,H.NIVELHABITACION,H.PRECIOHABITACION,H.CANTIDADHABITACION 
        FROM  {$this->tableHabitacion} H
        INNER JOIN HABITACION_HOTEL HH ON H.IDHABITACION = HH.IDHABITACION
        WHERE HH.IDHOTEL = '{$this->idHotel}'";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDHABITACION'];
            $salida[$ind]["nivel"] = $usuarios['NIVELHABITACION'] == 1 ? 'bajo' : ($usuarios['NIVELHABITACION'] == 2 ? 'medio' : 'alto');
            $salida[$ind]['precio'] = $usuarios['PRECIOHABITACION'];
            $salida[$ind]['cantidad'] = $usuarios['CANTIDADHABITACION'];
            $salida[$ind]['hotel'] = $this->idHotel;
            $salida[$ind]['imagenes']= $this->listarImagenesHabitacion($salida[$ind]["id"]);

            

        }
        return ($salida);
    }

    public function listarImagenesHabitacion($id){

        $query = "SELECT I.RUTA, I.ID
            FROM {$this->tableImagen} IA
            INNER JOIN IMAGEN I
            ON IA.IDIMAGEN = I.ID 
            WHERE IA.IDHABITACION = '{$id}'";

        // print $query . "\n";

       $datos = parent::obtenerDatos($query);

    //    print_r($query);

       return $datos;
    } 

   
}