<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class clientes extends conexion
{

    private $table = "CLIENTE";
    private $clienteID = "";
    private $nombreCliente = "";
    private $apellidoCliente = "";
    private $correoCliente = "";
    private $telefonoCliente = "";
    private $direccionCliente = "";
    private $password = "";
    // private $fechaNacimiento = "0000-00-00";
    // private $correo = "";
    // private $token = "";
    //912bc00f049ac8464472020c5cd06759

    public function listaPacientes($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDCLIENTE,NOMBRECLIENTE,APELLIDOCLIENTE,CORREOCLIENTE,TELEFONOCLIENTE,DIRECCIONCLIENTE FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["nombre"] = $usuarios['NOMBRECLIENTE'];
            $salida[$ind]["cedula"] = $usuarios['IDCLIENTE'];
            $salida[$ind]['apellido'] = $usuarios['APELLIDOCLIENTE'];
            $salida[$ind]['correo'] = $usuarios['CORREOCLIENTE'];
            $salida[$ind]['telefono'] = $usuarios['TELEFONOCLIENTE'];
            $salida[$ind]['direccion'] = $usuarios['DIRECCIONCLIENTE'];
        }

        return ($salida);
    }

    public function obtenerPaciente($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE IDCLIENTE = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["nombre"] = $usuario['NOMBRECLIENTE'];
            $salida["cedula"] = $usuario['IDCLIENTE'];
            $salida['apellido'] = $usuario['APELLIDOCLIENTE'];
            $salida['correo'] = $usuario['CORREOCLIENTE'];
            $salida['telefono'] = $usuario['TELEFONOCLIENTE'];
            $salida['direccion'] = $usuario['DIRECCIONCLIENTE'];
        }
        return $salida;
    }

    public function post($json)
    {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);


        if (!isset($datos['nombre']) || !isset($datos['apellido']) || !isset($datos['cedula']) || !isset($datos['correo']) || !isset($datos['password']))
            return $_respuestas->error_400();



        $this->nombreCliente = $datos['nombre'];
        $this->clienteID = $datos['cedula'];
        $this->correoCliente = $datos['correo'];
        $this->apellidoCliente = $datos['apellido'];
        $this->password = $datos['password'];
        if (isset($datos['telefono'])) {
            $this->telefonoCliente = $datos['telefono'];
        }
        if (isset($datos['direccion'])) {
            $this->direccionCliente = $datos['direccion'];
        }


        $resp = $this->insertarPaciente();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "pacienteId" => $resp,
                "Mensaje" => "Se creó el cliente correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarPaciente()
    {


        $query = "INSERT INTO " . $this->table . " (IDCLIENTE,NOMBRECLIENTE,APELLIDOCLIENTE,CORREOCLIENTE,TELEFONOCLIENTE,DIRECCIONCLIENTE, CLAVE)
        values
        ('" . $this->clienteID . "','" . $this->nombreCliente . "','" . $this->apellidoCliente . "','" . $this->correoCliente . "','"  . $this->telefonoCliente . "','" . $this->direccionCliente . "','" . $this->password . "')";
        $resp = parent::nonQueryId($query);
        if ($resp) {
            return $this->clienteID;
        } else {
            return 0;
        }
    }

    public function put($json)
    {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);



        if (!isset($datos['cedula'])) {
            return $_respuestas->error_400();
        } else {


            $this->clienteID = $datos['cedula'];
            if (isset($datos['nombre'])) {
                $this->nombreCliente = $datos['nombre'];
            }
            if (isset($datos['correo'])) {
                $this->correoCliente = $datos['correo'];
            }
            if (isset($datos['telefono'])) {
                $this->telefonoCliente = $datos['telefono'];
            }
            if (isset($datos['direccion'])) {
                $this->direccionCliente = $datos['direccion'];
            }
            if (isset($datos['apellido'])) {
                $this->apellidoCliente = $datos['apellido'];
            }
            if (isset($datos['password'])) {
                $this->password = $datos['password'];
            }

            $resp = $this->modificarPaciente();
            if ($resp) {
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "clienteID" => $this->clienteID
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }


    private function modificarPaciente()
    {


        $query = "UPDATE " . $this->table . " SET NOMBRECLIENTE ='" . $this->nombreCliente . "',DIRECCIONCLIENTE = '" . $this->direccionCliente . "', APELLIDOCLIENTE = '" . $this->apellidoCliente . "', TELEFONOCLIENTE = '" . $this->telefonoCliente .  "', CORREOCLIENTE = '" . $this->correoCliente . "', CLAVE = '" . $this->password .
            "' WHERE IDCLIENTE = '" . $this->clienteID . "'";
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

        if (!isset($datos['cedula']))
            return $_respuestas->error_400();

        $this->clienteID = $datos['cedula'];
        $resp = $this->eliminarPaciente();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "clienteID" => $this->clienteID
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function eliminarPaciente()
    {
        $query = "DELETE FROM " . $this->table . " WHERE IDCLIENTE= '" . $this->clienteID . "'";
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
