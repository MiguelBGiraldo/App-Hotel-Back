<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Articulo extends conexion
{

    private $table = "ARTICULOS";
    private $tableImagen = "IMAGEN_ARTICULO";
    private $id = "";
    private $nombre = "";
    private $precio = 0;
    private $detalle = "";
    private $imagenes = array();
    private $estado = 0;


    public function listaArticulos($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDARTICULO,NOMBRE,PRECIO,DETALLE,ESTADO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDARTICULO'];
            $salida[$ind]["nombre"] = $usuarios['NOMBRE'];
            $salida[$ind]['precio'] = $usuarios['PRECIO'];
            $salida[$ind]['detalle'] = $usuarios['DETALLE'];
            $salida[$ind]['estado'] = $usuarios['ESTADO'];
            $salida[$ind]['precio'] = $usuarios['PRECIO'];

            $salida[$ind]['imagenes']= $this->listarImagenesArticulo($salida[$ind]["id"]);

            

        }

        return ($salida);
    }

    public function obtenerArticulo($id)
    {
        $query = "SELECT IDARTICULO,NOMBRE,PRECIO,DETALLE,ESTADO FROM " . $this->table . " WHERE IDARTICULO = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDARTICULO'];
            $salida["nombre"] = $usuario['NOMBRE'];
            $salida['precio'] = $usuario['PRECIO'];
            $salida['detalle'] = $usuario['DETALLE'];
            $salida['estado'] = $usuario['ESTADO'];
        }
        $this->id = $id;

        $salida['imagenes'] = $this->listarImagenesArticulo($salida["id"]);

        return $salida;
    }

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['nombre']) || !isset($datos['precio']) || !isset($datos['detalle']))
            return $_respuestas->error_400();
        $this->id = $datos['id'];
        $this->nombre = $datos['nombre'];
        $this->detalle = $datos['detalle'];
        $this->imagenes = $datos['imagenes'];
        $this->precio = $datos['precio'];
        $resp = $this->insertarArticulo();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "articuloId" => $resp,
                "Mensaje" => "Se creó el articulo correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarArticulo()
    {

        $query = " BEGIN 
        INSERT INTO " . $this->table . " (IDARTICULO,NOMBRE,PRECIO,DETALLE,ESTADO)
        values
        ('" . $this->id . "','" . $this->nombre . "','" . $this->precio . "','" . $this->detalle . "','"  .  '0'  . "'); \n";


        foreach ($this->imagenes as $imagen) {
            $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDARTICULO)
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
            $query = "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDARTICULO)
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
                $this->detalle = $datos['detalle'];
            }
            if (isset($datos['precio'])) {
                $this->precio = $datos['precio'];
            }

            if (isset($datos['imagenes'])) {
                $this->imagenes = $datos['imagenes'];
            }

            $resp = $this->modificarArticulo();
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
    private function modificarArticulo()
    {

        $query = "BEGIN 
         UPDATE " . $this->table . " SET NOMBRE = '" . $this->nombre . "', DETALLE = '" . $this->detalle . "', PRECIO = '" . $this->precio .
            "' WHERE IDARTICULO = '" . $this->id . "'; \n";

        if($this->imagenes){
            $query .= "DELETE FROM {$this->tableImagen} WHERE IDARTICULO = '{$this->id}';\n";

            foreach ($this->imagenes as $imagen) {

                $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDARTICULO)
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
        $resp = $this->eliminarArticulo();
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

    public function listarImagenesArticulo($id){

        $query = "SELECT I.RUTA, I.ID
            FROM {$this->tableImagen} IA
            INNER JOIN IMAGEN I
            ON IA.IDIMAGEN = I.ID 
            WHERE IA.IDARTICULO = '{$id}'";

        // print $query . "\n";

       $datos = parent::obtenerDatos($query);

    //    print_r($query);

       return $datos;
    } 

    private function eliminarArticulo()
    {
        $query = "DELETE FROM " . $this->table . " WHERE IDARTICULO = '" . $this->id . "'";
        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }
}