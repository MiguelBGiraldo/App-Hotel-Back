<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Instalacion extends conexion
{

    private $table = "INSTALACION";
    private $tableImagen = "IMAGEN_INSTALACION";
    private $id = "";
    private $nombre = "";
    private $hotel = "";
    private $imagenes = array();
    private $estado = 0;


    public function listaInstalaciones($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDINSTALACION,NOMBRE,ESTADO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);

        $salida = array();
        foreach ($datos as $ind => $usuarios) {
            $salida[$ind]["id"] = $usuarios['IDINSTALACION'];
            $salida[$ind]["nombre"] = $usuarios['NOMBRE'];


            $salida[$ind]['imagenes']= $this->listarImagenesArticulo($salida[$ind]["id"]);
            $salida[$ind]['hotel'] = $this->obtenerHotel($salida[$ind]['id']);

        }

        return ($salida);
    }

    public function obtenerInstalacion($id)
    {
        $query = "SELECT IDINSTALACION,NOMBRE,ESTADO FROM " . $this->table . " WHERE IDINSTALACION = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDINSTALACION'];
            $salida["nombre"] = $usuario['NOMBRE'];
            $salida['estado'] = $usuario['ESTADO'];
        }
        $this->id = $id;

        $salida['imagenes'] = $this->listarImagenesArticulo($salida["id"]);
        $salida['hotel'] = $this->obtenerIdHotel($salida['id']);

        return $salida;
    }

    public function post($json)
    {


        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
        if (!isset($datos['id']) || !isset($datos['nombre']) || !isset($datos['hotel']) || !isset($datos['imagenes']))
            return $_respuestas->error_400();
        $this->id = $datos['id'];
        $this->nombre = $datos['nombre'];
        $this->imagenes = $datos['imagenes'];
        $this->hotel = $datos['hotel'];
        $resp = $this->insertarHotel();
        if ($resp) {
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "instalacionId" => $resp,
                "Mensaje" => "Se creó el articulo correctamente"
            );
            return $respuesta;
        } else {
            return $_respuestas->error_500();
        }
    }


    private function insertarHotel()
    {

        $query = " BEGIN 
        INSERT INTO " . $this->table . " (IDINSTALACION,NOMBRE,ESTADO)
        values
        ('" . $this->id . "','" . $this->nombre . "','"  .  '0'  . "'); \n";


        foreach ($this->imagenes as $imagen) {
            $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDINSTALACION)
            values
            ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
        }

        $query.= "INSERT INTO INSTA_HOTEL (HOTEL_IDHOTEL, INSTALACION_IDINSTALACION)
            values( '{$this->hotel}', '{$this->id}');\n";

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
            if (isset($datos['hotel'])) {
                $this->hotel = $datos['hotel'];
            }

            if (isset($datos['imagenes'])) {
                $this->imagenes = $datos['imagenes'];
            }

            $resp = $this->modificarInstalacion();
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
    private function modificarInstalacion()
    {

        $query = "BEGIN 
         UPDATE " . $this->table . " SET NOMBRE = '" . $this->nombre  .
            "' WHERE IDINSTALACION = '" . $this->id . "'; \n";

        if($this->imagenes){
            $query .= "DELETE FROM {$this->tableImagen} WHERE IDINSTALACION = '{$this->id}';\n";

            foreach ($this->imagenes as $imagen) {

                $query .= "INSERT INTO " . $this->tableImagen . " (IDIMAGEN,IDINSTALACION) values ('" . $imagen['ID'] . "', '" . $this->id  . "');\n";
            }
        }

        $query .= "DELETE FROM INSTA_HOTEL WHERE INSTALACION_IDINSTALACION = '{$this->id}';\n";
        
        $query.= "INSERT INTO INSTA_HOTEL (HOTEL_IDHOTEL, INSTALACION_IDINSTALACION) values( '{$this->hotel}', '{$this->id}');\n";


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
        $resp = $this->eliminarInstalacion();
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

    public function listarImagenesArticulo($id){

        $query = "SELECT I.RUTA, I.ID
            FROM {$this->tableImagen} IA
            INNER JOIN IMAGEN I
            ON IA.IDIMAGEN = I.ID 
            WHERE IA.IDINSTALACION = '{$id}'";

        // print $query . "\n";

       $datos = parent::obtenerDatos($query);

    //    print_r($query);

       return $datos;
    } 

    private function eliminarInstalacion()
    {
        $query = "BEGIN";

        $query .= "\nDELETE FROM  {$this->tableImagen} WHERE IDINSTALACION = '{$this->id}';\nDELETE FROM INSTA_HOTEL WHERE INSTALACION_IDINSTALACION = '{$this->id}';\nDELETE FROM " . $this->table . " WHERE IDINSTALACION = '{$this->id}';";

        $query .= "COMMIT;
        EXCEPTION 
        WHEN OTHERS THEN
        ROLLBACK;
        END;
        ";

        print $query . "\n";


        $resp = parent::nonQuery($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }

    public function obtenerHotel($id){

      
        $query = "SELECT H.NOMBRE NOMBRE FROM HOTEL H INNER JOIN INSTA_HOTEL IH ON H.IDHOTEL = IH.HOTEL_IDHOTEL WHERE IH.INSTALACION_IDINSTALACION = '$id'";
         $query = str_replace('&', '', $query);

        $datos = parent::obtenerDatos($query);

       
        foreach ($datos as $ind => $usuario) {
            $salida["nombre"] = $usuario['NOMBRE'];
        }

        return $salida['nombre'];
    }

    public function obtenerIdHotel($id){

        $query = "SELECT HOTEL_IDHOTEL FROM INSTA_HOTEL WHERE INSTALACION_IDINSTALACION = '$id'";
         

        $datos = parent::obtenerDatos($query);

       
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['HOTEL_IDHOTEL'];
        }

        return $salida['id'];

    }

}