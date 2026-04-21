<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mostrar errores solo en localhost
if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Si ya es admin, redirigir directo al panel
if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true) {
    header('Location: admin');
    exit();
}

require_once __DIR__ . '/../db.php';

// Generar token CSRF para el login
if (empty($_SESSION['csrf_token_login'])) {
    $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
}

$error_msg = '';

// Parámetros de seguridad contra fuerza bruta
$max_intentos = 3;
$tiempo_bloqueo = 60 * 5; // 5 minutos de bloqueo

// Limpiar bloqueo si ya pasó el tiempo
if (isset($_SESSION['bloqueo_hasta']) && time() > $_SESSION['bloqueo_hasta']) {
    unset($_SESSION['intentos_fallidos']);
    unset($_SESSION['bloqueo_hasta']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Verificación de Fuerza Bruta (Rate Limiting)
    if (isset($_SESSION['bloqueo_hasta']) && time() <= $_SESSION['bloqueo_hasta']) {
        $minutos_restantes = ceil(($_SESSION['bloqueo_hasta'] - time()) / 60);
        $error_msg = "Demasiados intentos fallidos. Intente nuevamente en $minutos_restantes minutos.";
    } 
    // 2. Verificación de CSRF
    else if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token_login'], $_POST['csrf_token'])) {
        $error_msg = "Error de seguridad (CSRF). Por favor, recargue la página y vuelva a intentarlo.";
    } 
    // 3. Proceso de Login
    else {
        $usuario = trim($_POST['usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (!empty($usuario) && !empty($contrasena)) {
            try {
                $db = new Database();
                $conexion = $db->getConnection();

                $query = "SELECT id_cargo, contrasena FROM usuarios WHERE usuario = ? LIMIT 1";

                if ($stmt = $conexion->prepare($query)) {
                    $stmt->bind_param("s", $usuario);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    if ($resultado->num_rows === 1) {
                        $filas = $resultado->fetch_assoc();

                        if (password_verify($contrasena, $filas['contrasena'])) {
                            session_regenerate_id(true);
                            unset($_SESSION['intentos_fallidos']);
                            unset($_SESSION['bloqueo_hasta']);
                            $_SESSION['id_cargo'] = $filas['id_cargo'];
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                            if ($filas['id_cargo'] == 1) {
                                $_SESSION['isAdmin'] = true;
                                $stmt->close();
                                $db->closeConnection();
                                header("Location: admin");
                                exit();
                            } elseif ($filas['id_cargo'] == 2) {
                                $stmt->close();
                                $db->closeConnection();
                                header("Location: ../public/cliente");
                                exit();
                            }
                        } else {
                            registrar_intento_fallido();
                        }
                    } else {
                        registrar_intento_fallido();
                    }
                    $stmt->close();
                } else {
                    $error_msg = "Error interno del sistema.";
                }
                $db->closeConnection();
            } catch (RuntimeException $e) {
                $error_msg = $e->getMessage();
            }
        } else {
            $error_msg = "Por favor, complete todos los campos.";
        }
    }
}

function registrar_intento_fallido() {
    global $error_msg, $max_intentos, $tiempo_bloqueo;
    $error_msg = "Usuario o contraseña incorrectos.";
    
    if (!isset($_SESSION['intentos_fallidos'])) {
        $_SESSION['intentos_fallidos'] = 1;
    } else {
        $_SESSION['intentos_fallidos']++;
    }

    if ($_SESSION['intentos_fallidos'] >= $max_intentos) {
        $_SESSION['bloqueo_hasta'] = time() + $tiempo_bloqueo;
        $error_msg = "Demasiados intentos fallidos. Ha sido bloqueado por 5 minutos por seguridad.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro - CFP 61</title>
    <!-- Incluimos Bootstrap y un estilo rápido para que no dependa de estilos que se pueden romper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #152372, #2136a8);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        .login-card .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .logo-container img {
            max-width: 180px;
        }
        .btn-custom {
            background-color: #152372;
            color: #ffffff;
            font-weight: 600;
            border-radius: 50px;
            padding: 10px;
        }
        .btn-custom:hover {
            background-color: #0f1954;
            color: #ffffff;
        }
        .form-control {
            border-radius: 50px;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-container">
        <!-- Logo similar a index.php -->
        <h3 class="fw-bold" style="color: #152372;">Panel de Control</h3>
        <p class="text-muted">Centro de Formación Profesional N°61</p>
    </div>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?= htmlspecialchars($error_msg) ?></div>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token_login'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="mb-3">
            <label class="form-label text-muted ms-2 fw-semibold">Usuario</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="border-radius: 50px 0 0 50px;">
                    <i class="bi bi-person-fill" style="color: #152372;"></i>
                </span>
                <input type="text" name="usuario" class="form-control border-start-0" required autofocus placeholder="Ingrese usuario administrador">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-muted ms-2 fw-semibold">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="border-radius: 50px 0 0 50px;">
                    <i class="bi bi-shield-lock-fill" style="color: #152372;"></i>
                </span>
                <input type="password" name="contrasena" class="form-control border-start-0" required placeholder="Ingrese contraseña">
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-custom btn-lg">Acceder de Forma Segura</button>
        </div>
        <div class="text-center">
            <a href="../public/index" class="text-decoration-none text-muted" style="font-size: 0.9rem;">
                <i class="bi bi-arrow-left me-1"></i>Volver a la página principal
            </a>
        </div>
    </form>
</div>

</body>
</html>
