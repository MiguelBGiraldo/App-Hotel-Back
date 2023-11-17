<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Automovil extends conexion
{

    private $table = "AUTOMOVIL";
    private $tableImagen = "IMAGEN_AUTOMOVIL";
    private $id = "";
    private $marca = "";
    private $tipo = "";
    private $gama = "";
    private $color = "";
    private $imagenes = array();
    private $precio = 0;
    private $estado = 0;
    // private $fechaNacimiento = "0000-00-00";
    // private $correo = "";
    // private $token = "";
    //912bc00f049ac8464472020c5cd06759


    public function listaAutmoviles($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDAUTOMOVIL,MARCAAUTOMOVIL,TIPOAUTOMOVIL,GAMAAUTOMOVIL,COLORAUTOMOVIL,PRECIO,ESTADO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDAUTOMOVIL'];
            $salida[$ind]["marca"] = $usuarios['MARCAAUTOMOVIL'];
            $salida[$ind]['tipo'] = $usuarios['TIPOAUTOMOVIL'];
            $salida[$ind]['gama'] = $usuarios['GAMAAUTOMOVIL'];
            $salida[$ind]['color'] = $usuarios['COLORAUTOMOVIL'];
            $salida[$ind]['precio'] = $usuarios['PRECIO'];
            // $salida[$ind]['imagen'] = $usuarios['IMAGEN'];
            $salida[$ind]['estado'] = $usuarios['ESTADO'];

            $salida[$ind]['imagenes']= $this->listarImagenesVehiculo($salida[$ind]["id"]);

            

        }

        return ($salida);
    }

    public function obtenerAutomovil($id)
    {
        $query = "SELECT IDAUTOMOVIL,MARCAAUTOMOVIL,TIPOAUTOMOVIL,GAMAAUTOMOVIL,COLORAUTOMOVIL,PRECIO,ESTADO FROM " . $this->table . " WHERE IDAUTOMOVIL = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDAUTOMOVIL'];
            $salida["marca"] = $usuario['MARCAAUTOMOVIL'];
            $salida['tipo'] = $usuario['TIPOAUTOMOVIL'];
            $salida['gama'] = $usuario['GAMAAUTOMOVIL'];
            $salida['color'] = $usuario['COLORAUTOMOVIL'];
            $salida['precio'] = $usuario['PRECIO'];
            // $salida['imagen'] = $usuario['IMAGEN'];
            $salida['estado'] = $usuario['ESTADO'];
        }
        $this->id = $id;

        $salida['imagenes'] = $this->listarImagenesVehiculo($salida["id"]);

        return $salida;
    }

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['marca']) || !isset($datos['tipo']) || !isset($datos['gama']) || !isset($datos['color']) || !isset($datos['precio']) || !isset($datos['imagenes']))
            return $_respuestas->error_400();
        $this->id = $datos['id'];
        $this->marca = $datos['marca'];
        $this->tipo = $datos['tipo'];
        $this->gama = $datos['gama'];
        $this->color = $datos['color'];
        $this->imagenes = $datos['imagenes'];
        $this->precio = $datos['precio'];
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

        $query = " BEGIN 
        INSERT INTO " . $this->table . " (IDAUTOMOVIL,MARCAAUTOMOVIL,TIPOAUTOMOVIL,GAMAAUTOMOVIL,COLORAUTOMOVIL,PRECIO, ESTADO)
        values
        ('" . $this->id . "','" . $this->marca . "','" . $this->tipo . "','" . $this->gama . "','"  . $this->color . "','" . $this->precio  . "', '" .  $this->estado  . "'); \n";


        foreach ($this->imagenes as $imagen) {
            $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDVEHICULO)
            values
            ('" . $imagen['id'] . "', '" . $this->id  . "');\n";
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
            $query = "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDVEHICULO)
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
            if (isset($datos['marca'])) {
                $this->marca = $datos['marca'];
            }
            if (isset($datos['tipo'])) {
                $this->tipo = $datos['tipo'];
            }
            if (isset($datos['gama'])) {
                $this->gama = $datos['gama'];
            }
            if (isset($datos['color'])) {
                $this->color = $datos['color'];
            }
            if (isset($datos['precio'])) {
                $this->precio = $datos['precio'];
            }
            if (isset($datos['imagenes'])) {
                $this->imagenes = $datos['imagenes'];
            }

            $resp = $this->modificarAutmovil();
            if ($resp) {
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "clienteID" => $this->id
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }
    private function modificarAutmovil()
    {

        $query = "BEGIN 
         UPDATE " . $this->table . " SET MARCAAUTOMOVIL = '" . $this->marca . "', TIPOAUTOMOVIL = '" . $this->tipo . "', GAMAAUTOMOVIL = '" . $this->gama .  "', COLORAUTOMOVIL = '" . $this->color . "', PRECIO = '" . $this->precio .
            "' WHERE IDAUTOMOVIL = '" . $this->id . "'; \n";

        if($this->imagenes){
            $query .= "DELETE FROM {$this->tableImagen} WHERE IDVEHICULO = '{$this->id}';\n";

            foreach ($this->imagenes as $imagen) {

                $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDVEHICULO)
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
        $resp = $this->eliminarAutomovil();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "AutomovilID" => $this->id
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }

    public function listarImagenesVehiculo($id){

        $query = "SELECT I.RUTA, I.ID
            FROM {$this->tableImagen} IA
            INNER JOIN IMAGEN I
            ON IA.IDIMAGEN = I.ID 
            WHERE IA.IDVEHICULO = '{$id}'";

        // print $query . "\n";

       $datos = parent::obtenerDatos($query);

    //    print_r($query);

       return $datos;
    } 

    private function eliminarAutomovil()
    {
        $query = "DELETE FROM " . $this->table . " WHERE IDAUTOMOVIL= '" . $this->id . "'";
        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }
    

    // private function buscarToken(){
    //     $query = "SELECT  TokenId,UsuarioId,Estado from usuarios_token WHERE Token = '" . $this->token . "' AND Estado = 'Activo'";
    //     $resp = parent::obtenerDatos($query);
    //     if($resp){
    //         return $resp;
    //     }else{
    //         return 0;
    //     }
    // }


    // private function actualizarToken($tokenid){
    //     $date = date("Y-m-d H:i");
    //     $query = "UPDATE usuarios_token SET Fecha = '$date' WHERE TokenId = '$tokenid' ";
    //     $resp = parent::nonQuery($query);
    //     if($resp >= 1){
    //         return $resp;
    //     }else{
    //         return 0;
    //     }
    // }



}
