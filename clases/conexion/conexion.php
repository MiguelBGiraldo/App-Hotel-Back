<?php
   
   class conexion {

    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;


    function __construct(){
        $listadatos = $this->datosConexion();
        foreach ($listadatos as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->port = $value['port'];
        }
        $this->conexion = oci_connect($this->user, $this->password, $this->server."/"."xe");
        // $this->conexion = new mysqli($this->server,$this->user,$this->password,$this->database,$this->port);
        // if($this->conexion->connect_errno){
        //     echo "algo va mal con la conexion";
        //     die();
        // }
        
        if (!$this->conexion) {
           $e = oci_error();
           echo "Error de conexión a Oracle: " . htmlentities($e['message']);
      }
}

    private function datosConexion(){
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }


    private function convertirUTF8($array){
        array_walk_recursive($array,function(&$item,$key){
            if(!mb_detect_encoding($item,'utf-8',true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }


    public function obtenerDatos($sqlstr){
        // $results = $this->conexion->query($sqlstr);
        // $resultArray = array();

        // print $sqlstr . "\n";

        $stid = oci_parse($this->conexion, $sqlstr);


        oci_execute($stid);

        $results = array();
        $indice =0;
        while ($fila = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            foreach ($fila as $nombre => $valor) {
                if ($valor === null) {
                    echo "$nombre: NULL<br>";
                } else {
                    $results[$indice][$nombre] = $valor;
                }
            }
            $indice++;
        }

        // $results = oci_fetch_assoc($stid);
        if(!$results)
        return array();
        // foreach ($results as $key) {
        //     $resultArray[] = $key;
        // }
        return $this->convertirUTF8($results);

    }

    public function nonQuery($sqlstr){
        // $results = $this->conexion->query($sqlstr);
        $stid = oci_parse($this->conexion, $sqlstr);

       // Ejecutar la consulta
        oci_execute($stid);

        return oci_num_rows($stid);
        // return $this->conexion->affected_rows;
    }


    //INSERT 
    public function nonQueryId($sqlstr){


        $stid = oci_parse($this->conexion, $sqlstr);
        oci_execute($stid);
        $filas = oci_num_rows($stid);


        // $results = $this->conexion->query($sqlstr);
        //  $filas = $this->conexion->affected_rows;
          if($filas >= 1){
            return $filas;
         }else{
             return 0;
         }
    }
     
    //encriptar

    protected function encriptar($string){
        return md5($string);
    }


   }
   ?>