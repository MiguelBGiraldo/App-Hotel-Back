<?php
$extensions = get_loaded_extensions();
foreach ($extensions as $extension) {
    echo $extension . "<br>";
}
if (extension_loaded('oci8')) {
    echo 'OCI8 is enabled';
} else {
    echo 'OCI8 is not enabled';
}

echo "Holaa";
$usuario = "ANGEL";
$password = "1234";
// $cadena = "(DESCRIPTION=(ADDRESS=(PROTOCOL=tcp)(HOST=Miguel990202)(PORT=1521))
// (CONNECT_DATA =
//   (SERVICE_NAME = Parcial2)
// )
// )";
$cadena = 'localhost/xe';


$conn = oci_connect($usuario, $password, $cadena);

if (!$conn) {
    $e = oci_error();
    echo "Error de conexión a Oracle: " . htmlentities($e['message']);
} else {
    echo "Conexión exitosa a Oracle";


    $sql = "SELECT table_name FROM user_tables";

// Preparar la consulta
$stid = oci_parse($conn, $sql);

// Ejecutar la consulta
oci_execute($stid);

while ($row = oci_fetch_array($stid, OCI_ASSOC)) {
    echo "Tabla: " . $row['TABLE_NAME'] . "<br>";
}

// Cerrar la conexión
oci_free_statement($stid);
oci_close($conn);
}

?>