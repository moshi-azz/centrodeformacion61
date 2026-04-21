<?php
/**
 * auth_check.php — Incluir SIEMPRE como primer require en páginas admin.
 * Centraliza: session_start, guard de admin, generación CSRF, conexión DB.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mostrar errores solo en localhost (ayuda a depurar en desarrollo)
if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Guard de administrador
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: ../public/index');
    exit();
}

// Generar token CSRF una sola vez por sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Conexión a la base de datos
require_once __DIR__ . '/../../db.php';
$db   = new Database();
$conn = $db->getConnection();

/**
 * Valida el token CSRF del POST actual.
 * Termina con HTTP 403 si el token falta o no coincide.
 */
function verify_csrf(): void
{
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('Token CSRF inválido. Por favor, recargá la página e intentá nuevamente.');
    }
}

/**
 * Devuelve un campo hidden con el token CSRF listo para insertar en formularios.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}
