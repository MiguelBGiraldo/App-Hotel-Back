<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Hotel extends conexion
{

    private $table = "HOTEL";
    private $tableImagen = "IMAGEN_HOTEL";
    private $id = "";
    private $nombre = "";
    private $telefono = 0;
    private $precio = "";
    private $imagenes = array();
    private $estado = 0;


    public function listaHoteles($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT NOMBRE,TELEFONO,PRECIONOCHE,IDHOTEL,ESTADO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDHOTEL'];
            $salida[$ind]["nombre"] = $usuarios['NOMBRE'];
            $salida[$ind]['precio'] = $usuarios['PRECIONOCHE'];
            $salida[$ind]['telefono'] = $usuarios['TELEFONO'];
            $salida[$ind]['estado'] = $usuarios['ESTADO'];

            $salida[$ind]['imagenes']= $this->listarImagenesHotel($salida[$ind]["id"]);

            

        }

        return ($salida);
    }

    public function listaAllHoteles()
    {

        
        $query = "SELECT NOMBRE,IDHOTEL FROM " . $this->table . " ";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDHOTEL'];
            $salida[$ind]["nombre"] = $usuarios['NOMBRE'];            
        }

        return ($salida);
    }

    public function obtenerHotel($id)
    {
        $query = "SELECT NOMBRE,TELEFONO,PRECIONOCHE,IDHOTEL,ESTADO FROM " . $this->table . " WHERE IDHOTEL = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDHOTEL'];
            $salida["nombre"] = $usuario['NOMBRE'];
            $salida['precio'] = $usuario['PRECIONOCHE'];
            $salida['telefono'] = $usuario['TELEFONO'];
            $salida['estado'] = $usuario['ESTADO'];
        }
        $this->id = $id;

        $salida['imagenes'] = $this->listarImagenesHotel($salida["id"]);

        return $salida;
    }

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['nombre']) || !isset($datos['precio']) || !isset($datos['telefono']))
            return $_respuestas->error_400();
        $this->id = $datos['id'];
        $this->nombre = $datos['nombre'];
        $this->telefono = $datos['telefono'];
        $this->imagenes = $datos['imagenes'];
        $this->precio = $datos['precio'];
        $resp = $this->insertarHotel();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "hotelId" => $resp,
                "Mensaje" => "Se creó el hotel correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarHotel()
    {

        $query = " BEGIN 
        INSERT INTO " . $this->table . " (IDHOTEL,NOMBRE,PRECIONOCHE,TELEFONO,ESTADO)
        values
        ('" . $this->id . "','" . $this->nombre . "','" . $this->precio . "','" . $this->telefono . "','"  .  '0'  . "'); \n";


        foreach ($this->imagenes as $imagen) {
            $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHOTEL)
            values
            ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
        }

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        $query = str_replace('&', '', $query);

        $resp = parent::nonQueryId($query);
        if ($resp) {
            return $this->id;
        } else {
            return 0;
        }

        file_put_contents('../pruebaConsulta.txt',$query);
    }

    private function insertarImagen()
    {

        $huboError = false;
        foreach ($this->imagenes as $imagen) {
            $query = "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHOTEL)
            values
            ('" . $imagen . "', '" . $this->id  . "')";
            $resp = parent::nonQueryId($query);
            if (!$resp){
                $huboError = true;
                break;
            }
        }

        return !$huboError;
    }

    public function put($json)
    {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);



        if (!isset($datos['id'])) {
            return $_respuestas->error_400();
        } else {
            $this->id = $datos['id'];
            if (isset($datos['nombre'])) {
                $this->nombre = $datos['nombre'];
            }
            if (isset($datos['detalle'])) {
                $this->telefono = $datos['telefono'];
            }
            if (isset($datos['precio'])) {
                $this->precio = $datos['precio'];
            }

            if (isset($datos['imagenes'])) {
                $this->imagenes = $datos['imagenes'];
            }

            $resp = $this->modificarHotel();
            if ($resp) {
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "hotelID" => $this->id
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }
    private function modificarHotel()
    {

        $query = "BEGIN 
         UPDATE " . $this->table . " SET NOMBRE = '" . $this->nombre . "', TELEFONO = '" . $this->telefono . "', PRECIONOCHE = '" . $this->precio .
            "' WHERE IDHOTEL = '" . $this->id . "'; \n";

        if($this->imagenes){
            $query .= "DELETE FROM {$this->tableImagen} WHERE IDHOTEL = '{$this->id}';\n";

            foreach ($this->imagenes as $imagen) {

                $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHOTEL)
                values
                ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
            }
        }

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        // print_r($query);
        
        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }


    public function delete($datos)
    {
        $_respuestas = new respuestas;

        if (!isset($datos['id']))
            return $_respuestas->error_400();

        $this->id = $datos['id'];
        $resp = $this->eliminarhotel();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "hotelID" => $this->id
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }

    public function listarImagenesHotel($id){

        $query = "SELECT I.RUTA, I.ID
            FROM {$this->tableImagen} IA
            INNER JOIN IMAGEN I
            ON IA.IDIMAGEN = I.ID 
            WHERE IA.IDHOTEL = '{$id}'";

        // print $query . "\n";

       $datos = parent::obtenerDatos($query);

    //    print_r($query);

       return $datos;
    } 

    private function eliminarhotel()
    {
        $query = "DELETE FROM " . $this->table . " WHERE IDHOTEL = '" . $this->id . "'";
        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }
}