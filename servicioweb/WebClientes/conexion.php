<?php

require '../vendor/autoload.php';

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

$adapter = new InMemory();
$registry = new CollectorRegistry($adapter);

$counter = $registry->getOrRegisterCounter('app', 'requests_total', 'Total number of requests', ['method']);
$counter->inc(['post']);

// Conectar a Redis
$redis = new Redis();
$redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));

// Datos del formulario
$nombre = $_POST['nombre'];
$pass = $_POST['password'];

// Verificar si las credenciales están en caché
if ($redis->get($nombre . ':' . $pass)) {
    $info = explode(':', $redis->get($nombre . ':' . $pass));
    $nombre = $info[0];
    $correo = $info[1];
    $username = $info[2];
    $password = $info[3];

    // Mostrar la página con la información del usuario
    echo '<!DOCTYPE html>
    <html lang="es">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=1, initial-scale=1.0">
        <title>JaFer Inc. | Sesión</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap">
        <style>
            /* General Styles */
            body {
                font-family: "Arial", sans-serif;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                background: linear-gradient(135deg, var(--colorGradiente1), var(--colorGradiente2));
                --colorGradiente1: #ffffff;
                --colorGradiente2: #6d6d63;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "Ubuntu", sans-serif;
            }

            header {
                background-color: #333;
                color: white;
                padding: 10px 0;
            }

            .contentheader {
                display: flex;
                align-items: center;
                justify-content: space-around;
            }

            .imagenh {
                height: 70px;
            }

            .menu {
                display: flex;
                list-style: none;
                justify-content: space-between;
                flex-wrap: wrap;
                width: 500px;
            }

            .menu a {
                transition: 0.5s;
                color: white;
                text-decoration: none;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 18px;
            }

            .menu a:hover {
                transition: 0.5s;
                background: #555;
                color: rgb(255, 255, 255);
                border: solid;
                border-width: 1px;
            }

            nav ul {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
            }

            nav ul li {
                margin-left: 20px;
            }

            nav ul li a {
                color: white;
                text-decoration: none;
                font-weight: bold;
            }

            nav ul li a:hover {
                text-decoration: underline;
            }

            /* Form Styles */
            .formulario {
                display: flex;
                position: relative;
                max-width: 800px;
                z-index: 10;
                justify-content: space-around;
                margin: 40px auto;
                background: whitesmoke;
                border-radius: 4px;
                padding: 40px 0;
                -webkit-box-shadow: 3px 17px 21px -6px rgba(0, 0, 0, 0.75);
                -moz-box-shadow: 3px 17px 21px -6px rgba(0, 0, 0, 0.75);
                box-shadow: 3px 17px 21px -6px rgba(0, 0, 0, 0.75);
            }

            .formulario h1 {
                text-align: center;
                color: #333;
            }

            .formulario label {
                display: block;
                margin: 15px 0 5px;
                color: #555;
            }

            .formulario input[type="text"],
            .formulario input[type="email"],
            .formulario input[type="password"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .botones {
                text-align: center;
            }

            .btn.enviar {
                background-color: #333;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            }

            .btn.enviar:hover {
                background-color: #555;
            }

            .formulario img {
                width: 250px;
                height: 250px;
                display: block;
                border-radius: 800px;
                margin: 20px 0 20px;
            }

            /* Footer Styles */
            footer {
                background-color: #333;
                color: white;
                text-align: center;
                padding: 20px 0;
                margin-top: 40px;
            }

            footer h1 {
                margin: 0;
            }

            footer p {
                margin: 5px 0 0;
                font-size: 14px;
            }

            input {
                border: none;
                border-bottom: solid;
                border-width: 1px;
                background: transparent;
                width: 100%;
                margin: 0 auto 20px auto;
                font-size: 18px;
            }
        </style>
    </head>
    
    <body>
        <header>
            <div class="contentheader">
                <img class="imagenh" src="LOGO-IES-JOVELLANOS.png">
                <dir></dir>
                <nav>
                    <ul class="menu">
                        <li><a href="index.html">Inicio</a></li>
                        <li><a href="index.html">Portafolio</a></li>
                        <li><a href="index.html">Servicios</a></li>
                        <li><a href="index.html">Contáctanos</a></li>
                    </ul>
                </nav>
            </div>
        </header>
    
        <div class="formulario">
            <form action="">
                <div class="user">
                    <img src="_efb90713-bfc3-4cfc-a9ca-e2f7d244a79c.jpeg">
                    <h2>Nombre: ' . $nombre . '</h2><br>
                </div>
                <div class="infouser">
                    <h2>Contraseña: ' . $password . '</h2><br>
                    <h2>Correo: ' . $correo . '</h2><br>
                    <h2>Usuario: ' . $username . '</h2><br>
                </div>
            </form>
        </div>
    
        <footer>
            <div class="conteninfo">
                <h1>JaFer Inc.</h1>
                <p>Página diseñada por Fernando y Javier. | Todos los derechos reservados</p>
            </div>
        </footer>
    </body>
    
    </html>';
    exit;
}

// Conectar a MySQL
$con = mysqli_connect("basedatos", "root", "root", "usuarios");

if ($con) {
    $query = "SELECT * FROM clientes WHERE username='" . $nombre . "' AND contra='" . $pass . "'";
    $result = mysqli_query($con, $query);

    if ($result->num_rows > 0) {
        // Guardar las credenciales en caché
        while ($row = $result->fetch_assoc()) {
            $redis->set($nombre . ':' . $pass, $row['nombre'] . ':' . $row['correo'] . ':' . $row['username'] . ':' . $row['contra']);
            $redis->expire($nombre . ':' . $pass, 3600); // Expira en 1 hora
        }

        // Redireccionar al usuario a la página con la información
        header("Location: conexion.php");
        exit;
    } else {
        echo '<script type="text/javascript">
        alert("Usuario o contraseña incorrectos");
        window.location.href="index.html";
        </script>';
    }

    mysqli_close($con);
} else {
    echo "No conectado";
}

?>
