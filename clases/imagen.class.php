<?php

require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class imagen extends conexion
{

    private $table = 'IMAGEN';

    public function post($ruta)
    {
        $id  = uniqid();
        $query = "INSERT INTO " . $this->table . " (ID,RUTA)
        values
        ('" . $id . "','" . $ruta . "')"; 
        $resp = parent::nonQueryId($query);
        if($resp){
             return array (
                "RUTA" => $ruta,
                "ID" => $id
             );
        }else{
            return 0;
        }
    }

    public function obtenerIdByRuta($ruta){

        $query = "SELECT ID FROM {$this->table} WHERE RUTA = '{$ruta}'";
        
        $resp = parent::obtenerDatos($query);
        $salida = 0;
        if($resp){
            foreach($resp as $val){
                $salida = $val;
            }
        }

        return $salida['ID'];

    }
}
