<?php
session_start();
require_once '../db.php';

// Crear instancia de Database
$db = new Database();
$conn = $db->getConnection();

// Inicializar variables de mensaje
$mensaje = '';
$tipo_mensaje = '';

// --- LÍMITE DE PETICIONES (RATE LIMITING) & CSRF ---
// Generar token CSRF para evitar falsificacioens de petición (Anti-Spam/Bots)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario enviado (POST) en el propio servidor para aplicar seguridad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['csrf_token'] ?? '';

    // Validar Token CSRF
    if (hash_equals($_SESSION['csrf_token'], $token_post)) {

        // Validar Rate Limit: Bloquear temporalmente (1 petición por minuto como mínimo)
        $tiempo_actual = time();
        $tiempo_limite = 60; // segundos

        if (isset($_SESSION['ultimo_envio']) && ($tiempo_actual - $_SESSION['ultimo_envio']) < $tiempo_limite) {
            $mensaje = 'Por favor, espera un momento antes de enviar otra solicitud. Protegemos el servidor contra Spam.';
            $tipo_mensaje = 'danger';
        } else {
            // Sanitizar de manera estricta los datos contra XSS / Inyección de Etiquetas
            $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $curso_id = filter_var($_POST['curso'] ?? '', FILTER_SANITIZE_NUMBER_INT);

            // Validar campos vacíos
            if ($nombre && $telefono && filter_var($email, FILTER_VALIDATE_EMAIL) && $curso_id) {

                // Obtener el nombre del curso de forma SEGURA usando Prepared Statements (Anti Inyección SQL)
                $stmt = $conn->prepare("SELECT titulo FROM cursos WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $curso_id);
                    $stmt->execute();
                    $resultado_curso = $stmt->get_result();
                    $curso_titulo = 'Curso No Encontrado';
                    if ($row = $resultado_curso->fetch_assoc()) {
                        $curso_titulo = $row['titulo'];
                    }
                    $stmt->close();
                }

                // Enviar datos mediante Servidor (cURL) a Formsubmit.co utilizando su sistema AJAX silencioso
                $url = "https://formsubmit.co/ajax/cformprof61.lacriolla@gmail.com";
                $postData = [
                    'nombre' => $nombre,
                    'telefono' => $telefono,
                    'email' => $email,
                    'curso' => $curso_titulo,
                    '_subject' => "Nueva Inscripción Oficial: $curso_titulo"
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Opcional: Puedes descomentar estas lineas si falla por certificado local en servidor Windows (XAMPP).
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $_SESSION['ultimo_envio'] = $tiempo_actual; // Registrar éxito para el rate limit
                    $mensaje = '¡Inscripción recibida de forma exitosa! Nos pondremos en contacto pronto.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'La inscripción es válida, pero hubo un error de conexión con la central de correo. Intente más tarde.';
                    $tipo_mensaje = 'warning';
                }

            } else {
                $mensaje = 'Incompleto o incorrecto. Verifica los campos ingresados.';
                $tipo_mensaje = 'warning';
            }
        }
    } else {
        $mensaje = 'La sesión caducó o el token de seguridad es inválido. Por favor recarga la página.';
        $tipo_mensaje = 'danger';
    }
}

// Obtener los cursos para llenar el desplegable de manera SEGURA
$cursos = [];
$stmt_cursos = $conn->prepare("SELECT id, titulo FROM cursos WHERE inscripciones_cerradas = 0");
if ($stmt_cursos) {
    $stmt_cursos->execute();
    $result_cursos = $stmt_cursos->get_result();
    while ($curso = $result_cursos->fetch_assoc()) {
        $cursos[] = $curso;
    }
    $stmt_cursos->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles/style.css"> <!-- Hereda paleta #152372 y tipografía Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="shortcut icon" href="./IMG/favicon.png" type="image/x-icon">
    <title>Inscripción a Curso | CFP 61 La Criolla</title>
    <meta name="description" content="Inscribite a los cursos del Centro de Formación Profesional N° 61 en La Criolla, Entre Ríos. Completá el formulario para comenzar tu formación.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="http://cfp61.edu.ar/inscripcion">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://cfp61.edu.ar/public/inscripcion.php">
    <meta property="og:title" content="Inscripción a Curso | CFP 61 La Criolla">
    <meta property="og:description" content="Inscribite a los cursos del Centro de Formación Profesional N° 61 en La Criolla, Entre Ríos.">
    <meta property="og:image" content="http://cfp61.edu.ar/public/IMG/lococfp61.png">
    <meta property="og:locale" content="es_AR">
    <meta property="og:site_name" content="CFP 61 La Criolla">
</head>

<body style="background-color: #cde3ef;"> <!-- Fondo uniforme con el sitio original -->

    <div class="container mt-5 mb-5 d-flex justify-content-center">
        <!-- Tarjeta del formulario estilo 'Moderno/Corporativo' -->
        <div class="card shadow-lg" style="width: 100%; max-width: 600px; border: none; border-radius: 10px;">

            <div class="card-header text-center"
                style="background-color: #152372; color: #cde3ef; border-radius: 10px 10px 0 0;">
                <h3 class="mb-0 py-2 fw-bold">Inscripción a Curso</h3>
            </div>

            <div class="card-body p-4" style="background-color: #ffffff; border-radius: 0 0 10px 10px;">

                <div class="text-end mb-4 mt-2">
                    <img src="./IMG/lococfp61.png" alt="Logo CFP61" width="520" class="img-fluid"
                        style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); position: relative; right: -30px;">
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> text-center fw-bold shadow-sm" role="alert">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <!-- Envía al MISMO SCRIPT (vacío en action=) en lugar de ir a formsubmit -->
                <form action="" method="POST">
                    <!-- Token Antiespasmo oculto en la vista -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold" style="color: #152372;">Nombre Completo</label>
                        <input type="text" class="form-control bg-light" id="nombre" name="nombre"
                            placeholder="Ingresa tu nombre y apellido" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label fw-bold" style="color: #152372;">Número de
                            Teléfono</label>
                        <input type="tel" class="form-control bg-light" id="telefono" name="telefono"
                            placeholder="Ej: 345 123 4567" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold" style="color: #152372;">Correo Electrónico</label>
                        <input type="email" class="form-control bg-light" id="email" name="email"
                            placeholder="tu@correo.com" required>
                    </div>
                    <div class="mb-4">
                        <label for="curso" class="form-label fw-bold" style="color: #152372;">Seleccione el Trayecto o
                            Curso</label>
                        <select class="form-select bg-light" id="curso" name="curso" required>
                            <option value="">-- Elige una opción disponible --</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?php echo htmlspecialchars($curso['id']); ?>">
                                    <?php echo htmlspecialchars($curso['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <!-- El botón utiliza la clase redefinida tuya de btn-primary -->
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">Confirmar e Inscribirme</button>
                        <a href="index" class="btn btn-outline-secondary mt-2">Volver al Inicio</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>