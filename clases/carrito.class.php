<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Carrito extends conexion
{

    private $table = "CARRITO";

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['marca']) || !isset($datos['tipo']) || !isset($datos['gama']) || !isset($datos['color']) || !isset($datos['precio']) || !isset($datos['imagenes']))
            return $_respuestas->error_400();
       
        $resp = $this->insertarAutomovil();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "pacienteId" => $resp,
                "Mensaje" => "Se creó el automovil correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarAutomovil()
    {

        $query = " BEGIN ";





        

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        $query = str_replace('&', '', $query);

        
        $resp = parent::nonQueryId($query);
        if ($resp) {
            return true;
        } else {
            return 0;
        }

        file_put_contents('../pruebaConsulta.txt',$query);
    }
}