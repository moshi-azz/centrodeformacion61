<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php'; // Asegúrate de que la ruta sea correcta
require_once './validar.php'; // Asegúrate de que la ruta sea correcta

// Crear una instancia de la clase Database
$db = new Database();
$conn = $db->getConnection(); // Obtener la conexión a la base de datos

// Obtener cursos destacados
$sql = "SELECT * FROM cursos LIMIT 15"; // Cambia el límite según sea necesario
$result = $conn->query($sql);
$sql_evento = "SELECT * FROM eventos ORDER BY fecha_hora DESC LIMIT 1";
$result_evento = $conn->query($sql_evento);
$evento = $result_evento->fetch_assoc();
// Verificar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// ── PROCESAMIENTO DEL FORMULARIO DE INSCRIPCIÓN (Offcanvas) ──────────────────
$mensaje = '';
$tipo_mensaje = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    $token_post = $_POST['csrf_token'] ?? '';

    if (hash_equals($_SESSION['csrf_token'], $token_post)) {
        $tiempo_actual = time();
        $tiempo_limite = 60;

        if (isset($_SESSION['ultimo_envio']) && ($tiempo_actual - $_SESSION['ultimo_envio']) < $tiempo_limite) {
            $mensaje = 'Por favor, espera un momento antes de enviar otra solicitud. Protegemos el servidor contra Spam.';
            $tipo_mensaje = 'danger';
        } else {
            $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $curso_id = filter_var($_POST['curso'] ?? '', FILTER_SANITIZE_NUMBER_INT);

            if ($nombre && $telefono && filter_var($email, FILTER_VALIDATE_EMAIL) && $curso_id) {
                $stmt_titulo = $conn->prepare("SELECT titulo FROM cursos WHERE id = ?");
                if ($stmt_titulo) {
                    $stmt_titulo->bind_param("i", $curso_id);
                    $stmt_titulo->execute();
                    $res_titulo = $stmt_titulo->get_result();
                    $curso_titulo = 'Curso No Encontrado';
                    if ($row_titulo = $res_titulo->fetch_assoc()) {
                        $curso_titulo = $row_titulo['titulo'];
                    }
                    $stmt_titulo->close();
                }

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
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $_SESSION['ultimo_envio'] = $tiempo_actual;
                    $mensaje = '¡Solicitud enviada con éxito! Pronto nos comunicaremos con vos para informarte los requisitos del curso.';
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

// Query de cursos con inscripciones abiertas (para el dropdown del offcanvas)
$cursos_abiertos = [];
$stmt_abiertos = $conn->prepare("SELECT id, titulo FROM cursos WHERE inscripciones_cerradas = 0");
if ($stmt_abiertos) {
    $stmt_abiertos->execute();
    $res_abiertos = $stmt_abiertos->get_result();
    while ($row_abierto = $res_abiertos->fetch_assoc()) {
        $cursos_abiertos[] = $row_abierto;
    }
    $stmt_abiertos->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles/style.css?v=2026041">
    <link rel="shortcut icon" href="./IMG/favicon.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- AOS – Animate On Scroll -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <title>Centro de Formación Profesional N° 61 | La Criolla, Entre Ríos</title>
    <meta name="description"
        content="Centro de Formación Profesional N° 61 en La Criolla, Concordia, Entre Ríos. Ofrecemos trayectos en informática, gastronomía, soldadura, electricidad, cosmetología y más. Inscripciones abiertas.">
    <meta name="keywords"
        content="centro de formación profesional, CFP 61, La Criolla, Concordia, Entre Ríos, cursos técnicos, trayectos formativos, inscripciones, educación técnica">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="http://cfp61.edu.ar/">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://cfp61.edu.ar/">
    <meta property="og:title" content="Centro de Formación Profesional N° 61 | La Criolla">
    <meta property="og:description"
        content="Formación profesional y capacitación laboral en La Criolla, Entre Ríos. Cursos en informática, gastronomía, soldadura, electricidad y más.">
    <meta property="og:image" content="http://cfp61.edu.ar/public/IMG/lococfp61.png">
    <meta property="og:locale" content="es_AR">
    <meta property="og:site_name" content="CFP 61 La Criolla">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Centro de Formación Profesional N° 61 | La Criolla">
    <meta name="twitter:description" content="Formación profesional y capacitación laboral en La Criolla, Entre Ríos.">
    <meta name="twitter:image" content="http://cfp61.edu.ar/public/IMG/lococfp61.png">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "EducationalOrganization",
      "name": "Centro de Formación Profesional N° 61",
      "alternateName": "CFP 61",
      "url": "http://cfp61.edu.ar/",
      "logo": "http://cfp61.edu.ar/public/IMG/lococfp61.png",
      "description": "Institución educativa que brinda trayectos de formación profesional y capacitación laboral para una rápida inserción en el mercado socioproductivo local y regional.",
      "foundingDate": "2014",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Río Bermejo N° 278",
        "addressLocality": "La Criolla",
        "addressRegion": "Entre Ríos",
        "addressCountry": "AR"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+54-345-410-9085",
        "email": "cfplacriolla@gmail.com",
        "contactType": "admissions"
      },
      "sameAs": [
        "https://www.instagram.com/cfplacriolla2",
        "https://www.facebook.com/share/15S7oEsPhe/"
      ],
      "parentOrganization": {
        "@type": "Organization",
        "name": "Dirección de Educación Técnico Profesional del Consejo General de Educación de Entre Ríos",
        "url": "https://cge.entrerios.gov.ar/tecnico-profesional/"
      }
    }
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light glass-nav">
        <div id="navPill"></div>
        <div class="container-fluid">
            <a class="navbar-brand p-0" href="#">
                <img src="./IMG/lococfp61.png" alt="Logo CFP 61"
                    style="width:280px; margin-bottom:-30px; margin-top:-30px; max-width:100%; height:auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#" id="nav-inicio">
                            <i class="bi bi-house-fill me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#trayectos" id="nav-trayectos">
                            <i class="bi bi-journal-bookmark-fill me-1"></i>Trayectos Formativos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#nosotros" id="nav-nosotros">
                            <i class="bi bi-people-fill me-1"></i>Sobre Nosotros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#footer" id="nav-contacto">
                            <i class="bi bi-envelope-fill me-1"></i>Contacto
                        </a>
                    </li>
                    <?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-bold" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock-fill me-1"></i>Administración
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../admin/admin">
                                        <i class="bi bi-grid-fill me-2"></i>Ver Cursos
                                    </a></li>
                                <li><a class="dropdown-item" href="../admin/agregar_curso">
                                        <i class="bi bi-plus-circle-fill me-2"></i>Agregar Curso
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../admin/cerrar_sesion">
                                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="social-icons d-flex align-items-center justify-content-center mt-3 mt-lg-0 gap-2">
                    <a href="https://www.instagram.com/cfplacriolla2?igsh=MW5hemlyNmpiNzRzcA==" target="_blank"
                        class="social-link" style="color: #152372;">
                        <i class="bi bi-instagram fs-2 px-1"></i>
                    </a>
                    <a href="https://www.facebook.com/share/15S7oEsPhe/?mibextid=LQQJ4d" target="_blank"
                        class="social-link" style="color: #152372;">
                        <i class="bi bi-facebook fs-2 px-1"></i>
                    </a>
                    <button type="button" class="btn btn-sm fw-bold rounded-pill ms-2" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasInscripcion" aria-controls="offcanvasInscripcion"
                        style="background-color:#152372; color:#cde3ef; border:none; white-space:nowrap;">
                        <i class="bi bi-telephone-fill me-1"></i>Quiero anotarme
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <header>
        <?php if ($evento): ?>
            <div class="alert alert-info text-center news-section" id="evento"
                data-fecha="<?php echo htmlspecialchars($evento['fecha_hora']); ?>">
                <h2 class="news-title"><strong><?php echo htmlspecialchars($evento['titulo']); ?></strong></h2>
                <p class="news-description"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                <p class="news-date">Fecha y Hora: <span
                        class="date-time"><?php echo htmlspecialchars($evento['fecha_hora']); ?></span></p>
                <div id="contador" class="countdown-timer"></div>
            </div>
        <?php endif; ?>
    </header>

    <main class="container">
        <div class="container-hero" id="heroContainer">
            <div class="bg-contrast"></div>
            <h1 class="hero">Bienvenido al Centro de Formación Profesional</h1>
            <a href="#trayectos" class="scroll-indicator" aria-label="Ver trayectos">
                <span class="scroll-indicator-text">Explorá</span>
                <i class="bi bi-chevron-double-down"></i>
            </a>
        </div>
        <p class="slogan" data-aos="fade-up" data-aos-delay="100">
            Impulsá tus habilidades,<br> <strong>el momento es ahora.</strong>
        </p>




        <div id="trayectos"> </div>
        <h2 data-aos="zoom-in"
            style="text-align: center; color: #cde3ef;font-weight:bold; border:#152372 solid 4px; border-radius:20px; background-color: #152372; margin-bottom:20px;">
            Trayectos:</h2>

        <div class="row justify-content-center mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="col-md-6">
                <input type="text" id="buscadorCursos" class="form-control form-control-lg rounded-pill shadow-sm"
                    placeholder="Buscar un trayecto o curso...">
            </div>
        </div>

        <div class="row" id="listaCursos">
            <?php
            if ($result->num_rows > 0) {
                $cardIndex = 0;
                while ($curso = $result->fetch_assoc()) {
                    $foto = isset($curso['imagen']) ? '../public/IMG/' . htmlspecialchars($curso['imagen']) : 'default.jpg';
                    $inscripciones_cerradas = $curso['inscripciones_cerradas'] ? 'inscripciones-cerradas' : '';
                    // Delay en cascada según columna (0 → 0ms, 1 → 150ms, 2 → 300ms)
                    $aosDelay = ($cardIndex % 3) * 150;

                    $cerradas_bool = $curso['inscripciones_cerradas'] ? 'true' : 'false';
                    $fecha_ap = htmlspecialchars($curso['fecha_apertura'] ?? '');
                    echo '<div class="col-md-4 mb-4 curso-card-container" data-aos="fade-up" data-aos-delay="' . $aosDelay . '">';
                    echo '<div class="card h-100 ' . $inscripciones_cerradas . '">';
                    echo '<img src="' . $foto . '" alt="' . htmlspecialchars($curso['titulo']) . '" class="card-img-top">';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h3 class="card-title h5">' . htmlspecialchars($curso['titulo']) . '</h3>';
                    echo '<button class="btn btn-outline-primary btn-sm mt-auto btn-ver-curso" type="button"
                        data-titulo="' . htmlspecialchars($curso['titulo']) . '"
                        data-descripcion="' . htmlspecialchars($curso['descripcion']) . '"
                        data-imagen="' . $foto . '"
                        data-cursoid="' . (int)$curso['id'] . '"
                        data-inscripciones-cerradas="' . $cerradas_bool . '"
                        data-fecha-apertura="' . $fecha_ap . '">
                        <i class="bi bi-eye me-1"></i>Ver trayecto
                    </button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    $cardIndex++;
                }
            } else {
                echo '<p>No hay cursos destacados en este momento.</p>';
            }
            ?>
        </div>
        <div id="nosotros"></div>
        <div class="row">
            <div class="col-sm" data-aos="fade-right">
                <h2 style="text-align: center; font-weight:bold; margin-top:80px;">¿Quiénes somos?</h2>
            </div>
        </div>


        <div class="row">
            <div class="col-sm" data-aos="fade-left" data-aos-delay="100">
                <p class="texto-institucional" style="text-align: center; font-size:x-large; ">El CENTRO FORMACIÓN PROFESIONAL N° 61
                    dependiente de la Dirección de Educación Técnico
                    Profesional del Consejo General de Educación funciona
                    en la Localidad de La Criolla desde el año 2014 y
                    actualmente cuenta con un anexo en la ciudad vecina
                    de Colonia Ayuí.

                    Somos una institución educativa que brinda trayectos
                    de formación profesional y capacitación laboral para
                    una rápida inserción en el mercado socioproductivo
                    local y regional.
                    <br>
                    La Formación Profesional permite compatibilizar la
                    promoción social, profesional y personal con la
                    productividad de la economía nacional, regional y local.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-sm" data-aos="fade-right" data-aos-delay="50">
                <h2 style="text-align: center; font-weight:bold; margin-top:80px;">Propósitos y Objetivos</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-sm" data-aos="fade-left" data-aos-delay="150">
                <p class="texto-institucional" style="text-align: center; font-size:x-large; ">Nuestra oferta busca preparar, actualizar y
                    desarrollar
                    las capacidades de las personas para el mundo del
                    trabajo. Capacitamos en conocimientos específicos,
                    competencias básicas, profesionales y sociales para que
                    jóvenes y adultos/as puedan mejorar sus oportunidades
                    de empleabilidad.
                    <br>
                    La oferta de cursos y trayectos se orienta a temáticas
                    como: Informática, gastronomía, herrería, electricidad,
                    belleza y cosmética, marroquinería, entre otras.
                </p>
            </div>
        </div>
    </main>

    <!-- ═══ OFFCANVAS: Panel de Contacto ═══ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasInscripcion"
        aria-labelledby="offcanvasInscripcionLabel" style="width: min(480px, 100vw); background-color: #ffffff;">

        <div class="offcanvas-header" style="background-color:#152372; color:#cde3ef;">
            <h5 class="offcanvas-title fw-bold" id="offcanvasInscripcionLabel">
                <i class="bi bi-telephone-fill me-2"></i>¡Me interesa anotarme!
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Cerrar"></button>
        </div>

        <div class="offcanvas-body" style="background-color:#f8fbfd;">

            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Completá el formulario y nos pondremos en contacto con vos para informarte los requisitos, horarios y
                fechas de inicio del curso.
            </p>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo htmlspecialchars($tipo_mensaje); ?> fw-bold shadow-sm text-center"
                    role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="formInscripcionOffcanvas">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="mb-3">
                    <label for="oc_nombre" class="form-label fw-bold" style="color:#152372;">Nombre Completo</label>
                    <input type="text" class="form-control bg-light" id="oc_nombre" name="nombre"
                        placeholder="Ingresa tu nombre y apellido" required>
                </div>
                <div class="mb-3">
                    <label for="oc_telefono" class="form-label fw-bold" style="color:#152372;">Número de
                        Teléfono</label>
                    <input type="tel" class="form-control bg-light" id="oc_telefono" name="telefono"
                        placeholder="Ej: 345 123 4567" required>
                </div>
                <div class="mb-3">
                    <label for="oc_email" class="form-label fw-bold" style="color:#152372;">Correo Electrónico</label>
                    <input type="email" class="form-control bg-light" id="oc_email" name="email"
                        placeholder="tu@correo.com" required>
                </div>
                <div class="mb-4">
                    <label for="oc_curso" class="form-label fw-bold" style="color:#152372;">¿Qué trayecto te
                        interesa?</label>
                    <select class="form-select bg-light" id="oc_curso" name="curso" required>
                        <option value="">-- Elegí una opción disponible --</option>
                        <?php foreach ($cursos_abiertos as $c_abierto): ?>
                            <option value="<?php echo htmlspecialchars($c_abierto['id']); ?>">
                                <?php echo htmlspecialchars($c_abierto['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-lg fw-bold shadow-sm"
                        style="background-color:#152372; color:#cde3ef;">
                        <i class="bi bi-send-fill me-1"></i>Quiero que me contacten
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <img src="./IMG/lococfp61.png" alt="Logo CFP 61" class="img-fluid"
                    style="max-width:180px; opacity:0.65;">
            </div>
        </div>
    </div>


    <footer class=" text-light pt-4" id="footer">
        <div class="container-fluid">
            <div class="row">
                <!-- Columna 1: Ubicación -->
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <h3 class="h5"><i class="bi bi-geo-alt-fill fs-5"></i> Dirección</h3>
                    <p>
                        Rio Bermejo N°278, La Criolla, Dpto Concordia.<br>
                        Instalaciones del Club Juan B. Alberdi.
                    </p>
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3410.1767368566343!2d-58.10936102371696!3d-31.271206174329066!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95adecb04d7457bd%3A0x98aa53d46aa8cd3f!2sR%C3%ADo%20Bermejo%2C%20La%20Criolla%2C%20Entre%20R%C3%ADos!5e0!3m2!1ses-419!2sar!4v1731937964342!5m2!1ses-419!2sar"
                        width="50%" height="50%" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <!-- Columna 2: Contacto -->
                <div class="col-lg-4 col-md-6 col-12 mb-3">
                    <h3 class="h5"><i class="bi bi-telephone-fill fs-5"></i> Teléfono y correos electrónicos</h3>
                    <p>
                        Celular: <a href="tel:3454109085" class="text-light">345 410-9085</a><br>
                        Correo electrónico:<br>
                        Administración: <a href="mailto:cfplacriolla@gmail.com"
                            class="text-light">cfplacriolla@gmail.com</a><br>

                    </p>
                </div>

                <!-- Columna 3: Redes sociales o enlace institucional -->
                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <h3 class="h5"><i class="bi bi-link fs-5"></i> Dirección de Educación Técnico Profesional del
                        Consejo General de Educación</h3>
                    <p>
                        <a href="https://cge.entrerios.gov.ar/tecnico-profesional/" class="text-light"
                            target="_blank">Consejo General de Educación</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="border-top border-light mt-3"></div> <!-- Separación visual -->
        <div class="container-fluid d-flex flex-column align-items-center mt-3 pb-3">
            <p class="mb-2 fs-6">
                Diseñado y desarrollado con <i class="bi bi-heart-fill text-danger"></i> por <a
                    href="https://github.com/moshi-azz" target="_blank"
                    class="text-light fw-bold text-decoration-none">moshi-azz</a>
            </p>
            <a href="../admin/" class="text-light text-decoration-none text-muted transition-all"
                style="font-size: 0.8rem; opacity: 0.5;" title="Panel de Administración">
                <i class="bi bi-lock-fill"></i> Ingreso Admin
            </a>
        </div>
    </footer>
    <!-- ═══ MODAL CUSTOM: Detalle de Trayecto ═══ -->
    <div class="curso-modal-overlay" id="cursoModalOverlay" role="dialog" aria-modal="true" aria-label="Detalle del trayecto">
        <div class="curso-modal-card" id="cursoModalCard">
            <button class="curso-modal-close" id="cursoModalClose" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
            <img id="cursoModalImg" src="" alt="" class="curso-modal-img">
            <div class="curso-modal-body">
                <h2 class="curso-modal-titulo" id="cursoModalTitulo"></h2>
                <p class="curso-modal-descripcion" id="cursoModalDesc"></p>
                <div id="cursoModalInscripcionArea"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS – Animate On Scroll -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 750,      // duración de cada animación (ms)
            offset: 80,         // px desde el borde inferior del viewport para activar
            once: true,         // animar solo la primera vez que aparece
            easing: 'ease-out-cubic'
        });
    </script>
    <script src="./js/main.js"></script>
</body>

</html>