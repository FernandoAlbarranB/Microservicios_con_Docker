<?php 

require '../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

$adapter = new InMemory();
$registry = new CollectorRegistry($adapter);

$counter = $registry->getOrRegisterCounter('app', 'requests_total', 'Total number of requests', ['method']);
$counter->inc(['post']);

// Conexión a Redis
$redis = new Redis();
$redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));


$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$username = $_POST['username'];
$contra = $_POST['contra'];


// Insertar datos en Redis
$redis->hMSet('usuarios:' . $username, [
    'nombre' => $nombre,
    'correo' => $correo,
    'contra' => $contra
]);



// Conectar a MySQL

$con = mysqli_connect("basedatos","root","root","usuarios");
$query = "insert into clientes values('$username', '$nombre','$correo','$contra')";


// Verificar si el usuario ya está en caché
if ($redis->get($username)) {
    echo '<script type="text/javascript">
    alert("Este usuario ya está registrado.");
    window.location.href="registro.html";
    </script>';
    exit;
}



if ($con) {
    $query = "INSERT INTO clientes (username, nombre, correo, contra) VALUES ('$username', '$nombre', '$correo', '$contra')";
    $result = mysqli_query($con, $query);

    if ($result) {
        // Guardar el usuario en caché
        $redis->set($username, true);
        // Configurar el tiempo de expiración del caché
        $redis->expire($username, 3600); // 1 hora

        echo '<script type="text/javascript">
        alert("Registro Correcto. Se ha guardado en caché.");
        window.location.href="index.html";
        </script>';
    } else {
        echo '<script type="text/javascript">
        alert("Algo salio mal :/");
        window.location.href="registro.html";
        </script>';
    }
} else {
    echo "No conectado";
}


// Endpoint para las métricas
if ($_SERVER['REQUEST_URI'] === '/metrics') {
    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());
    header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
    echo $result;
    exit;
}

?>
