<?php
$pageTitle = 'Manual de Usuario — CFP 61';
$activePage = 'manual';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid pt-4 px-4 pb-5">
    <div class="glass-container p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2><i class="bi bi-journal-text me-2" style="color:var(--brand-primary);"></i>Manual de Uso - Panel de Administración</h2>
        </div>

        <p class="text-muted">Bienvenido al manual interactivo del sistema administrativo CFP 61. Aquí encontrarás cómo operar cada sección.</p>

        <div class="accordion" id="manualAccordion">
            <!-- Sección Cursos -->
            <div class="accordion-item" style="border:none; border-radius: 12px; margin-bottom:10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h2 class="accordion-header" id="headingCursos">
                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCursos" aria-expanded="true" aria-controls="collapseCursos">
                        <i class="bi bi-journal-bookmark-fill me-2" style="color:var(--brand-primary);"></i> Gestión de Trayectos / Cursos
                    </button>
                </h2>
                <div id="collapseCursos" class="accordion-collapse collapse show" aria-labelledby="headingCursos" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        <strong>Agregar un nuevo trayecto:</strong> Dirígete a la pestaña "Agregar Trayecto". Deberás completar información como nombre, duración, modalidad e ingresar una imagen desde tu dispositivo local.<br><br>
                        <strong>Editar y Eliminar:</strong> Desde el "Dashboard" principal visualizarás el listado en formato tabla. Cada fila cuenta con un botón amarillo (Lápiz) para editar o actualizar datos de un curso, y un botón rojo (Basurero) para eliminarlo permanentemente.<br><br>
                        <strong>Abrir/Cerrar Inscripciones:</strong> En ese mismo Dashboard, el primer botón de acción alternará el estado de las inscripciones del curso entre verde (Abierto) y gris (Cerrado), lo que deshabilitará el botón a los estudiantes en la web pública.
                    </div>
                </div>
            </div>

            <!-- Sección Eventos -->
            <div class="accordion-item" style="border:none; border-radius: 12px; margin-bottom:10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h2 class="accordion-header" id="headingEventos">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEventos" aria-expanded="false" aria-controls="collapseEventos">
                        <i class="bi bi-calendar-event-fill me-2" style="color:#d94350;"></i> Gestión de Eventos
                    </button>
                </h2>
                <div id="collapseEventos" class="accordion-collapse collapse" aria-labelledby="headingEventos" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        Al entrar en la pestaña <strong>Eventos</strong>, se permitirá gestionar anuncios institucionales (ej: Muestras Anuales, Feriados, Comienzo de Clases). Similar a los cursos, puedes incorporar una fecha programada, título descriptivo y adjuntar una imagen ilustrativa que se proyectará instantáneamente en el rotador principal o la página pública vinculada a la agenda.
                    </div>
                </div>
            </div>

            <!-- Sección Usuarios -->
            <div class="accordion-item" style="border:none; border-radius: 12px; margin-bottom:10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h2 class="accordion-header" id="headingUsuarios">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                        <i class="bi bi-people-fill me-2" style="color:#1d2939;"></i> Gestión de Usuarios
                    </button>
                </h2>
                <div id="collapseUsuarios" class="accordion-collapse collapse" aria-labelledby="headingUsuarios" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        En la sección de <strong>Usuarios</strong> podrás registrar a otros administradores o directivos que necesiten operar en el panel de control. Es sumamente importante mantener actualizadas y en reserva estas credenciales. Todo usuario aquí creado contará con permisos plenos sobre la manipulación de cursos y eventos, por lo cual se aconseja generar cuentas estrictamente para la gestión y evitar cuentas irrelevantes.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
