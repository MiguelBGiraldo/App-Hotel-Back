<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Habitacion extends conexion
{

    private $table = "HABITACION";
    private $tableImagen = "IMAGEN_HABITACION";
    private $id = "";
    private $cantidad = 0;
    private $precio = 0;
    private $nivel = 0;
    private $hotel = "";
    private $imagenes = array();
    private $estado = 0;


    public function listaHabitaciones($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDHABITACION,NIVELHABITACION,PRECIOHABITACION,CANTIDADHABITACION,ESTADO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDHABITACION'];
            $salida[$ind]["precio"] = $usuarios['PRECIOHABITACION'];
            $salida[$ind]["nivel"] = $usuarios['NIVELHABITACION'] == '1' ? "Bajo" :($usuarios['NIVELHABITACION'] == '2' ? "Medio" : "Alto");
            $salida[$ind]["cantidad"] = $usuarios['CANTIDADHABITACION'];
            $salida[$ind]['imagenes']= $this->listarImagenesHabitacion($salida[$ind]["id"]);
            $salida[$ind]['hotel'] = $this->obtenerHotel($salida[$ind]['id']);

        }

        return ($salida);
    }

    public function obtenerHabitacion($id)
    {
        $query = "SELECT IDHABITACION,NIVELHABITACION,PRECIOHABITACION,CANTIDADHABITACION,ESTADO FROM " . $this->table . " WHERE IDHABITACION = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDHABITACION'];
            $salida["precio"] = $usuario['PRECIOHABITACION'];
            $salida["nivel"] = $usuario['NIVELHABITACION'] == '1' ? "Bajo" :($usuario['NIVELHABITACION'] == '2' ? "Medio" : "Alto");
            $salida["cantidad"] = $usuario['CANTIDADHABITACION'];
        }
        $this->id = $id;

        $salida['imagenes'] = $this->listarImagenesHabitacion($salida["id"]);
        $salida['hotel'] = $this->obtenerIdHotel($salida['id']);

        return $salida;
    }

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['precio']) || !isset($datos['cantidad']) || !isset($datos['nivel']) || !isset($datos['hotel']) || !isset($datos['imagenes']))
            return $_respuestas->error_400();
        $this->id = $datos['id'];
        $this->precio = $datos['precio'];
        $this->nivel = $datos['nivel'];
        $this->cantidad = $datos['cantidad'];
        $this->imagenes = $datos['imagenes'];
        $this->hotel = $datos['hotel'];
        $resp = $this->insertarHabitacion();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "habitacionId" => $resp,
                "Mensaje" => "Se creó la habitación correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarHabitacion()
    {

        $query = "BEGIN 
        INSERT INTO " . $this->table . " (IDHABITACION,NIVELHABITACION,PRECIOHABITACION,CANTIDADHABITACION,ESTADO)
        values
        ('" . $this->id . "','" . $this->nivel . "','"   . $this->precio . "','" . $this->cantidad . "','"  . '0'  . "');\n";


        foreach ($this->imagenes as $imagen) {
            $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHABITACION)
            values
            ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
        }

        $query.= "INSERT INTO HABITACION_HOTEL (IDHOTEL, IDHABITACION)
            values( '{$this->hotel}', '{$this->id}');\n";

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";
        // print $query . "\n";

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
            $query = "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHABITACION)
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
            if (isset($datos['precio'])) {
                $this->precio = $datos['precio'];
            }
            if (isset($datos['hotel'])) {
                $this->hotel = $datos['hotel'];
            }
            if (isset($datos['cantidad'])) {
                $this->cantidad = $datos['cantidad'];
            }
            if (isset($datos['nivel'])) {
                $this->nivel = $datos['nivel'];
            }
            

            if (isset($datos['imagenes'])) {
                $this->imagenes = $datos['imagenes'];
            }

            $resp = $this->modificarHabitacion();
            if ($resp) {
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "articuloID" => $this->id
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }
    private function modificarHabitacion()
    {

        $query = "BEGIN 
         UPDATE " . $this->table . " SET PRECIOHABITACION = '{$this->precio}', NIVELHABITACION = '{$this->nivel}', CANTIDADHABITACION = '{$this->cantidad}'  WHERE IDHABITACION = '{$this->id}';\n";

        if($this->imagenes){
            $query .= "DELETE FROM {$this->tableImagen} WHERE IDHABITACION = '{$this->id}';\n";

            foreach ($this->imagenes as $imagen) {

                $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDHABITACION) values ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
            }
        }

        $query .= "DELETE FROM HABITACION_HOTEL WHERE IDHABITACION = '{$this->id}';\n";
        
        $query.= "INSERT INTO HABITACION_HOTEL (IDHOTEL, HABITACION_HOTEL) values( '{$this->hotel}', '{$this->id}');\n";


        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        
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
        $resp = $this->eliminarHabitacion();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "InstalacionID" => $this->id
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
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

    private function eliminarHabitacion()
    {
        $query = "BEGIN";

        $query .= "\nDELETE FROM  {$this->tableImagen} WHERE IDHABITACION = '{$this->id}';\nDELETE FROM HABITACION_HOTEL WHERE IDHABITACION = '{$this->id}';\nDELETE FROM {$this->table} WHERE IDHABITACION = '{$this->id}';";

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }

    public function obtenerHotel($id){

      
        $query = "SELECT H.NOMBRE NOMBRE FROM HOTEL H INNER JOIN HABITACION_HOTEL HH ON H.IDHOTEL = HH.IDHOTEL WHERE HH.IDHABITACION = '$id'";
         $query = str_replace('&', '', $query);

        $datos = parent::obtenerDatos($query);

       
        foreach ($datos as $ind => $usuario) {
            $salida["nombre"] = $usuario['NOMBRE'];
        }

        return $salida['nombre'];
    }

    public function obtenerIdHotel($id){

        $query = "SELECT IDHOTEL FROM HABITACION_HOTEL WHERE IDHABITACION = '$id'";
         

        $datos = parent::obtenerDatos($query);

       
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDHOTEL'];
        }

        return $salida['id'];

    }

}