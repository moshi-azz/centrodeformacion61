<?php
// $pageTitle debe definirse antes de hacer include de este archivo.
$pageTitle = $pageTitle ?? 'Administración — CFP 61';
// $activePage puede definirse para resaltar el link activo del navbar.
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../public/IMG/favicon.png" type="image/x-icon">
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Bootstrap 5.3.3 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
          rel="stylesheet">
    <!-- Estilos del panel admin -->
    <link rel="stylesheet" href="../admin/css/admin.css">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>

<!-- Navbar unificado con el sitio público -->
<nav class="navbar navbar-expand-lg navbar-light glass-nav">
    <div class="container-fluid">
        <a class="navbar-brand p-0" href="../public/index">
            <img src="../public/IMG/lococfp61.png" alt="Logo CFP 61"
                 style="width:220px; margin-top:-20px; margin-bottom:-20px; max-width:100%; height:auto;">
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#adminNavbar"
                aria-controls="adminNavbar" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= $activePage === 'dashboard' ? 'active' : '' ?>"
                       href="../admin/admin">
                        <i class="bi bi-grid-fill me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= $activePage === 'agregar' ? 'active' : '' ?>"
                       href="../admin/agregar_curso">
                        <i class="bi bi-plus-circle-fill me-1"></i>Agregar Trayecto
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= $activePage === 'eventos' ? 'active' : '' ?>"
                       href="../admin/eventos">
                        <i class="bi bi-calendar-event-fill me-1"></i>Eventos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= $activePage === 'usuarios' ? 'active' : '' ?>"
                       href="../admin/usuarios">
                        <i class="bi bi-people-fill me-1"></i>Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= $activePage === 'manual' ? 'active' : '' ?>"
                       href="../admin/manual">
                        <i class="bi bi-journal-text me-1"></i>Manual
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="../public/index" target="_blank">
                        <i class="bi bi-house-fill me-1"></i>Ver Sitio
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                <span class="fw-bold" style="color:var(--brand-dark);">
                    <i class="bi bi-person-fill me-1"></i>Administrador
                </span>
                <a href="../admin/cerrar_sesion" class="btn-admin-logout">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
