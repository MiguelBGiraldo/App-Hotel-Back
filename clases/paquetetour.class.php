<?php
require_once "conexion/conexion.php";
require_once "respuestas.class.php";


class Paquete_Tour extends conexion
{

    private $table = "PAQUETETUR";


    public function listaPaquetes($pagina = 1)
    {
        $inicio  = 0;
        $cantidad = 100;
        if ($pagina > 1) {
            $inicio = ($cantidad * ($pagina - 1)) + 1;
            $cantidad = $cantidad * $pagina;
        }
        $query = "SELECT IDPAQUETETUR,NOMBRE,DESCRIPCION,PRECIO FROM " . $this->table . "  WHERE ROWNUM BETWEEN $inicio AND $cantidad";
        $datos = parent::obtenerDatos($query);
        
        $salida = array();
        foreach ($datos as $ind => $usuarios) {

            $salida[$ind]["id"] = $usuarios['IDPAQUETETUR'];
            $salida[$ind]["nombre"] = $usuarios['NOMBRE'];
            $salida[$ind]['descripcion'] = $usuarios['DESCRIPCION'];
            $salida[$ind]['precio'] = $usuarios['PRECIO'];
        }

        return ($salida);
    }

    public function obtenerPaquete($id)
    {
        $query = "SELECT IDPAQUETETUR,NOMBRE,DESCRIPCION,PRECIO FROM " . $this->table . " WHERE IDPAQUETETUR = '$id'";
        $datos = parent::obtenerDatos($query);
        foreach ($datos as $ind => $usuario) {
            $salida["id"] = $usuario['IDPAQUETETUR'];
            $salida["nombre"] = $usuario['NOMBRE'];
            $salida['descripcion'] = $usuario['DESCRIPCION'];
            $salida['precio'] = $usuario['PRECIO'];
        }
        
        return $salida;
    }
}