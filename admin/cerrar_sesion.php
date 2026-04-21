<?php
session_start(); // Iniciar la sesión

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, se debe borrar también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir a la página de inicio o a la página de login
header("Location: ../public/index");
exit();
?>