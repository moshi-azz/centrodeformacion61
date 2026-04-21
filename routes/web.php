<?php

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once './db.php'; // Asegúrate de que la ruta sea correcta
require_once './validar.php'; // Asegúrate de que la ruta sea correcta

// Crear una instancia de la clase Database
$db = new Database();
$conn = $db->getConnection(); // Obtener la conexión a la base de datos

// Definir rutas
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Mapeo de rutas
switch ($requestUri) {
    case '/':
    case '/index.php':
        require './public/index.php';
        break;

    case '/inscripcion.php':
        require './public/inscripcion.php';
        break;

    case '/procesar_inscripcion.php':
        require './public/procesar_inscripcion.php';
        break;

    case '/procesar_inscripcion.php':
            require './public/procesar_inscripcion.php';
            break;
    
    case '/procesar_inscripcion.php':
                require './public/procesar_inscripcion.php';
                break;
        
    default:
       include __DIR__ . '/../public/index.php';
        break;
}

// Cerrar la conexión
$conn->close();
?>s